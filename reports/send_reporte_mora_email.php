<?php
// reports/send_reporte_mora_email.php
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/security.php';

date_default_timezone_set('America/Managua');
setlocale(LC_TIME, 'es_NI.UTF-8', 'es_ES.UTF-8', 'es');

// TCPDF
require_once __DIR__ . '/../tcpdf/tcpdf.php';
require_once __DIR__ . '/../includes/tcpdf_netsoluciones.php';

// PHPMailer
include_once '../PHPMailer/src/PHPMailer.php';
include_once '../PHPMailer/src/SMTP.php';
include_once '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

session_start();
if (empty($_SESSION['id_usuario']) || empty($_SESSION['activo'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'No autorizado.']);
    exit;
}

// Parámetros
$ano_actual    = isset($_POST['anio']) && $_POST['anio'] !== '' ? (int) $_POST['anio'] : (int) date('Y');
$mes_actual    = (int) date('n');

$nivel_escolar = isset($_POST['nivel_escolar']) ? trim($_POST['nivel_escolar']) : '';
$grado         = isset($_POST['grado']) ? trim($_POST['grado']) : '';
$seccion       = isset($_POST['seccion']) ? trim($_POST['seccion']) : '';
$q             = isset($_POST['q']) ? trim($_POST['q']) : '';


// Mapeo meses
$map = [
  1 => ['Ene','Enero'],  2 => ['Feb','Febrero'], 3 => ['Mar','Marzo'],
  4 => ['Abr','Abril'],  5 => ['May','Mayo'],    6 => ['Jun','Junio'],
  7 => ['Jul','Julio'],  8 => ['Ago','Agosto'],  9 => ['Sep','Septiembre'],
  10 => ['Oct','Octubre'], 11 => ['Nov','Noviembre'], 12 => ['Dic','Diciembre'],
];

// ===== 1) Traer inscripciones del año lectivo =====
$sqlBase = "
  SELECT b.ID AS IdInscripcion, c.NOMBREAPELLIDO, b.IDNIVEL AS IdNivel
  FROM tbl_matricula a
  INNER JOIN tbl_inscripcion b ON a.ID = b.IDMATRICULA
  INNER JOIN tbl_alumnos c     ON a.IDALUMNO = c.IDALUMNO
  WHERE a.ANIO = ?
";
$types  = 'i';
$params = [$ano_actual];

if ($nivel_escolar !== '') {
    $sqlBase .= " AND b.IDNIVEL = ?";
    $types   .= 'i';
    $params[] = (int)$nivel_escolar;
}
if ($grado !== '') {
    $sqlBase .= " AND b.IDGRADO = ?";
    $types   .= 'i';
    $params[] = (int)$grado;
}
if ($seccion !== '') {
    $sqlBase .= " AND b.SECCION = ?";
    $types   .= 's';
    $params[] = $seccion; // letra
}
if ($q !== '') {
    $sqlBase .= " AND c.NOMBREAPELLIDO LIKE ?";
    $types   .= 's';
    $params[] = '%'.$q.'%';
}

$stmt = $link->prepare($sqlBase);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

// Preparar consultas reusables
$stmtPagos = $link->prepare("SELECT * FROM tbl_pagosmensualidades WHERE IdInscripcion = ? LIMIT 1");
$stmtMonto = $link->prepare("
  SELECT a.Monto, a.TIPOMONEDA
  FROM tbl_aranceles a
  INNER JOIN tbl_categoriapago b ON a.IdCategoria = b.Id
  WHERE b.Concepto = 'MENSUALIDAD' AND a.IdNivel = ? AND a.Anio = ?
  LIMIT 1
");

$cacheMontos = [];
$deudores = [];
$deuda_total = 0.0;

while ($row = $res->fetch_assoc()) {
    $idInscripcion = (int) $row['IdInscripcion'];
    $nombreRaw     = (string) $row['NOMBREAPELLIDO'];
    $idNivel       = (int) $row['IdNivel'];

    // Pagos (puede no existir fila)
    $stmtPagos->bind_param('i', $idInscripcion);
    $stmtPagos->execute();
    $resPagos = $stmtPagos->get_result();
    $rowPagos = $resPagos->fetch_assoc() ?: [];

    // Calcular mora hasta mes actual
    $total_mes_mora = 0;
    $meses_mora = [];
    for ($i = 1; $i <= $mes_actual; $i++) {
        [$abbr, $full] = $map[$i];
        $valor = $rowPagos[$abbr] ?? null;
        if ($valor !== 'X') {
            $total_mes_mora++;
            $meses_mora[] = $full;
        }
    }

    if ($total_mes_mora > 0) {
        // Monto mensual por nivel (con caché)
        if (!array_key_exists($idNivel, $cacheMontos)) {
            $stmtMonto->bind_param('ii', $idNivel, $ano_actual);
            $stmtMonto->execute();
            $resMonto = $stmtMonto->get_result();
            $filaMonto = $resMonto->fetch_assoc();
            $cacheMontos[$idNivel] = [
                'monto' => isset($filaMonto['Monto']) ? (float)$filaMonto['Monto'] : 0.0,
                'moneda'=> isset($filaMonto['TIPOMONEDA']) ? (string)$filaMonto['TIPOMONEDA'] : ''
            ];
        }
        $montoMensual = (float) $cacheMontos[$idNivel]['monto'];
        $tipomoneda   = (string) $cacheMontos[$idNivel]['moneda'];
        $prefijo = 'C$';
        if ($tipomoneda) {
            $tipUpper = strtoupper(trim($tipomoneda));
            if ($tipUpper === 'USD' || $tipUpper === 'DOLAR' || $tipUpper === 'DÓLAR') {
                $prefijo = '$';
            }
        }

        $monto_total = $montoMensual * $total_mes_mora;
        $deuda_total += $monto_total;

        $deudores[] = [
            'nombre'      => mb_convert_case(mb_strtolower($nombreRaw, 'UTF-8'), MB_CASE_TITLE, 'UTF-8'),
            'meses_lista' => implode(', ', $meses_mora),
            'meses_cnt'   => $total_mes_mora,
            'monto_total' => $monto_total,
            'prefijo'     => $prefijo,
        ];
    }
}

$stmtMonto->close();
$stmtPagos->close();
$stmt->close();

if (count($deudores) === 0) {
    echo json_encode(['ok' => false, 'error' => 'No hay alumnos en mora para el año indicado.']);
    exit;
}

// ===== 2) Generar PDF en memoria =====
class PDFConEncabezado extends TCPDFNetsoluciones {
    public $logoPath = '';
    public $titulo   = '';
    public $subtitulo= '';
    public $info     = '';
    public function Header() {
        if ($this->logoPath && file_exists($this->logoPath)) {
            $this->Image($this->logoPath, 15, 12, 20);
        }
        $this->SetY(12);
        $this->SetFont('dejavusans', 'B', 12);
        $this->Cell(0, 6, $this->titulo, 0, 1, 'C');
        $this->SetFont('dejavusans', '', 10);
        $this->Cell(0, 5, $this->subtitulo, 0, 1, 'C');
        $this->Ln(2);
        $this->SetFont('dejavusans', '', 9);
        $this->Cell(0, 5, $this->info, 0, 0, 'C');
        $this->Ln(8);
        $this->Line(15, 30, 200, 30);
        $this->Ln(2);
    }
    public function Footer() {
        parent::Footer();
    }
}

$pdf = new PDFConEncabezado('P', 'mm', 'LETTER', true, 'UTF-8', false);
$pdf->logoPath = __DIR__ . '/../img/Logo-SYSCPE.png';
$pdf->titulo   = 'Alumnos en mora';
$pdf->subtitulo= 'Año lectivo: ' . $ano_actual;
$pdf->info     = 'Generado: ' . date('d/m/Y H:i');

$pdf->SetCreator('Sistema');
$pdf->SetAuthor('Netsoluciones');
$pdf->SetTitle('Alumnos en mora - ' . $ano_actual);
$pdf->SetMargins(15, 36, 15);
$pdf->SetAutoPageBreak(true, 18);
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);
// $pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('dejavusans', '', 10);

$css = '
<style>
table { border-collapse: collapse; width: 100%; }
th, td { border: 1px solid #777; padding: 6px; font-size: 10pt; }
th { background-color: #f0f0f0; font-weight: bold; }
.text-right { text-align: right; }
</style>';

$html = $css . '<table>
<thead>
<tr>
  <th>#</th>
  <th>Nombre y apellido</th>
  <th>Meses que debe</th>
  <th class="text-right">Monto que debe</th>
  <th>Meses en mora</th>
</tr>
</thead><tbody>';

$i = 1;
$prefijoMonedaDominante = 'C$';
foreach ($deudores as $d) {
    $mesLabel = ($d['meses_cnt'] > 1) ? 'meses' : 'mes';
    $montoFmt = number_format((float)$d['monto_total'], 2, '.', ',');
    $prefijoMonedaDominante = $d['prefijo'] ?: $prefijoMonedaDominante;

    $html .= '<tr>
      <td>'.($i++).'</td>
      <td>'.htmlspecialchars($d['nombre'], ENT_QUOTES, 'UTF-8').'</td>
      <td>'.htmlspecialchars($d['meses_lista'], ENT_QUOTES, 'UTF-8').'</td>
      <td class="text-right">'.$d['prefijo'].' '.$montoFmt.'</td>
      <td>'.$d['meses_cnt'].' '.$mesLabel.'</td>
    </tr>';
}
$html .= '</tbody></table>';

$html .= '<br><table>
  <tr>
    <td><strong>Total alumnos en mora:</strong> '.count($deudores).'</td>
    <td class="text-right"><strong>Total adeudado:</strong></td>
    <td class="text-right"><strong>'.$prefijoMonedaDominante.' '.number_format($deuda_total, 2, '.', ',').'</strong></td>
  </tr>
</table>';

$pdf->writeHTML($html, true, false, true, false, '');
$filename = 'alumnos_en_mora_'.$ano_actual.'.pdf';
$pdfString = $pdf->Output($filename, 'S');

//obtener datos de mail
$queryEmail = mysqli_query($link, "SELECT * FROM tbla_email");
$rowEmail = mysqli_fetch_array($queryEmail);

//obtener informacion del colegio
$queryColegio = mysqli_query($link, "SELECT * FROM tbl_parametros");
$rowColegio = mysqli_fetch_array($queryColegio);
$nombre_colegio = $rowColegio['NOMBRECOLEGIO'];
if(empty($rowColegio['logo_colegio'])){
    $logoPath = __DIR__ . '/../img/Logo-SYSCPE.png';
} else {
    $logoPath = __DIR__ . '/../'.$rowColegio['logo_colegio'];
}

$mail = new PHPMailer(true);

// ===== 3) Enviar email con PHPMailer =====
try {
    $mail->isSMTP();
	$mail->Host = $rowEmail['host'];
	$mail->SMTPAuth = true;
	$mail->Username = $rowEmail['username'];
	$mail->Password = $rowEmail['password'];
	$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
	$mail->Port = $rowEmail['port'];

    // Incrustar logo (ajusta la ruta si es necesario)
    if (file_exists($logoPath)) {
        // "cid:logo_colegio" se usará en el HTML
        $mail->addEmbeddedImage($logoPath, 'logo_colegio', 'logo.png');
    }

    $mail->setFrom($rowEmail['username'], $nombre_colegio);
    // Destinatario fijo que pediste:
    $mail->addAddress($_SESSION['email_usuario'], ''); // puedes poner un nombre si quieres
    $mail->isHTML(true);

    $mail->Subject = 'Alumnos en mora - ' . $ano_actual;
    
    // Formatos
    $totalDeudores = count($deudores);
    $totalAdeudadoFmt = $prefijoMonedaDominante . ' ' . number_format($deuda_total, 2, '.', ',');
    $fechaAhora = date('d/m/Y \a\ \l\a\s H:i');
    $nombreColegioSafe = htmlspecialchars($nombre_colegio ?? 'Colegio', ENT_QUOTES, 'UTF-8');

    // HTML estilo “card”
    $mail->Body = '
    <div style="margin:0;padding:24px;background:#f5f7fb;font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif; color:#111;">
    <div style="max-width:560px;margin:0 auto;">

        <!-- Logo -->
        <div style="text-align:center;margin-bottom:16px;">
        <img src="cid:logo_colegio" alt="Logo" style="width:64px;height:64px;border-radius:12px;display:inline-block;vertical-align:middle;">
        </div>

        <!-- Tarjeta -->
        <div style="background:#ffffff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.06);padding:24px;">
        <h2 style="margin:0 0 4px;font-size:20px;line-height:1.3;font-weight:700;text-align:center;">Alumnos en mora</h2>
        <p style="margin:0 0 16px;font-size:13px;color:#667085;text-align:center;">
            Año lectivo <strong>' . (int)$ano_actual . '</strong> &middot; Generado el ' . $fechaAhora . '
        </p>

        <div style="height:1px;background:#eef0f4;margin:12px 0 16px;"></div>

        <div style="font-size:14px;color:#111;line-height:1.6;">
            <p style="margin:0 0 8px;">
            <strong>Resumen</strong>
            </p>
            <p style="margin:0 0 6px;">
            Total alumnos en mora: <strong>' . $totalDeudores . '</strong>
            </p>
            <p style="margin:0;">
            Total adeudado: <strong>' . $totalAdeudadoFmt . '</strong>
            </p>
        </div>
        </div>

        <!-- Pie -->
        <div style="text-align:center;margin-top:20px;">
        <div style="font-size:18px;font-weight:800;letter-spacing:.2px;color:#111;margin-bottom:4px;">' . $nombreColegioSafe . '</div>
        <div style="font-size:12px;color:#8a8f98;">Enviado automáticamente desde el sistema de reportes</div>
        </div>

    </div>
    </div>
    ';

    // Texto plano (fallback)
    $mail->AltBody = 'Alumnos en mora - ' . $ano_actual . PHP_EOL .
                    'Generado el ' . $fechaAhora . PHP_EOL .
                    'Total alumnos en mora: ' . $totalDeudores . PHP_EOL .
                    'Total adeudado: ' . $totalAdeudadoFmt;

    // Adjuntar el PDF desde memoria
    $mail->addStringAttachment($pdfString, $filename, 'base64', 'application/pdf');

    $mail->send();
    echo json_encode(['ok' => true, 'msg' => 'Correo enviado']);
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'error' => 'No se pudo enviar el correo: ' . $e->getMessage()]);
}
