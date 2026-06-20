<?php
// reports/reporte_mora_pdf.php

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/security.php';

date_default_timezone_set('America/Managua');
setlocale(LC_TIME, 'es_NI.UTF-8', 'es_ES.UTF-8', 'es');

// Ajusta la ruta a tu TCPDF
require_once __DIR__ . '/../tcpdf/tcpdf.php';
require_once __DIR__ . '/../includes/tcpdf_netsoluciones.php';
// Alternativas comunes:
// require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';
// require_once __DIR__ . '/../tcpdf_min/tcpdf.php';

error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();
if (empty($_SESSION['id_usuario']) || empty($_SESSION['activo'])) {
    header('HTTP/1.1 403 Forbidden');
    die('No autorizado.');
}

// Año y mes actuales (o usa ?anio=YYYY si lo pasas por GET)
$ano_actual    = isset($_GET['anio']) ? (int) $_GET['anio'] : (int) date('Y');
$mes_actual    = (int) date('n'); // 1..12

$nivel_escolar = isset($_GET['nivel_escolar']) ? trim($_GET['nivel_escolar']) : '';
$grado         = isset($_GET['grado']) ? trim($_GET['grado']) : '';
$seccion       = isset($_GET['seccion']) ? trim($_GET['seccion']) : '';
$q             = isset($_GET['q']) ? trim($_GET['q']) : '';

// Helper meses
$map = [
  1 => ['Ene','Enero'],
  2 => ['Feb','Febrero'],
  3 => ['Mar','Marzo'],
  4 => ['Abr','Abril'],
  5 => ['May','Mayo'],
  6 => ['Jun','Junio'],
  7 => ['Jul','Julio'],
  8 => ['Ago','Agosto'],
  9 => ['Sep','Septiembre'],
  10 => ['Oct','Octubre'],
  11 => ['Nov','Noviembre'],
  12 => ['Dic','Diciembre'],
];

// ========== 1) Trae inscripciones/alumnos del año lectivo ==========
$sqlBase = "
  SELECT b.ID AS IdInscripcion, c.NOMBREAPELLIDO, b.IDNIVEL AS IdNivel
  FROM tbl_matricula a
  INNER JOIN tbl_inscripcion b ON a.ID = b.IDMATRICULA
  INNER JOIN tbl_alumnos c     ON a.IDALUMNO = c.IDALUMNO
  WHERE a.ANIO = ?
";

// parámetros dinámicos
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
    // SECCION = letra -> string
    $types   .= 's';
    $params[] = $seccion;
}
if ($q !== '') {
    // buscador por nombre
    $sqlBase .= " AND c.NOMBREAPELLIDO LIKE ?";
    $types   .= 's';
    $params[] = '%'.$q.'%';
}

$stmt = $link->prepare($sqlBase);

// PHP 7.4+: spread operator para bind_param
$stmt->bind_param($types, ...$params);

$stmt->execute();
$res = $stmt->get_result();

$deudores = [];
$total_alumnos_mora = 0;
$deuda_total = 0.00;

// Prepara consultas reusables
$sqlPagos = "SELECT * FROM tbl_pagosmensualidades WHERE IdInscripcion = ? LIMIT 1";
$stmtPagos = $link->prepare($sqlPagos);

$sqlMonto = "
  SELECT a.Monto, a.TIPOMONEDA
  FROM tbl_aranceles a
  INNER JOIN tbl_categoriapago b ON a.IdCategoria = b.Id
  WHERE b.Concepto = 'MENSUALIDAD' AND a.IdNivel = ? AND a.Anio = ?
  LIMIT 1
";
$stmtMonto = $link->prepare($sqlMonto);

while ($row = $res->fetch_assoc()) {
    $idInscripcion = (int) $row['IdInscripcion'];
    $nombreRaw     = (string) $row['NOMBREAPELLIDO'];
    $idNivel       = (int) $row['IdNivel'];

    // 2) Trae fila de pagos mensualidades
    $stmtPagos->bind_param('i', $idInscripcion);
    $stmtPagos->execute();
    $resPagos = $stmtPagos->get_result();
    $filaPagos = $resPagos->fetch_assoc(); // puede ser null si no existe

    // 3) Recorre meses hasta el mes actual y cuenta mora
    $total_mes_mora = 0;
    $meses_mora = [];

    for ($i = 1; $i <= $mes_actual; $i++) {
        [$abbr, $full] = $map[$i];
        $valor = $filaPagos[$abbr] ?? null; // si no existe, lo tratamos como NO pagado
        if ($valor !== 'X') {
            $total_mes_mora++;
            $meses_mora[] = $full;
        }
    }

    if ($total_mes_mora > 0) {
        // 4) Monto mensual segun nivel/año
        $stmtMonto->bind_param('ii', $idNivel, $ano_actual);
        $stmtMonto->execute();
        $resMonto = $stmtMonto->get_result();
        $filaMonto = $resMonto->fetch_assoc();

        $montoMensual = $filaMonto ? (float)$filaMonto['Monto'] : 0.0;
        $tipomoneda   = $filaMonto ? (string)$filaMonto['TIPOMONEDA'] : '';
        $prefijo = 'C$';
        if ($tipomoneda) {
            $tipUpper = strtoupper(trim($tipomoneda));
            if ($tipUpper === 'USD' || $tipUpper === 'DOLAR' || $tipUpper === 'DÓLAR') {
                $prefijo = '$';
            }
        }

        // Total adeudado por el alumno
        $monto_total = $montoMensual * $total_mes_mora;
        $deuda_total += $monto_total;

        // Formato nombre con mayúscula inicial
        $nombreFmt = mb_convert_case(mb_strtolower($nombreRaw, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');

        $deudores[] = [
            'nombre'      => $nombreFmt,
            'meses_lista' => implode(', ', $meses_mora), // "Enero, Febrero, ..."
            'meses_cnt'   => $total_mes_mora,           // 3
            'monto_total' => $monto_total,              // numérico
            'prefijo'     => $prefijo,                  // "C$" | "$"
        ];

        $total_alumnos_mora++;
    }
}

$stmtMonto->close();
$stmtPagos->close();
$stmt->close();

// ========== 5) Genera PDF con TCPDF ==========
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
$pdf->logoPath = __DIR__ . '/../img/Logo-SYSCPE.png'; // ajusta si tu logo cambia
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
//$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('dejavusans', '', 10);

// Si no hay deudores
if ($total_alumnos_mora === 0) {
    $pdf->Write(6, "No se encontraron alumnos en mora para el año {$ano_actual}.");
    $pdf->Output('alumnos_en_mora.pdf', 'I');
    exit;
}

// CSS y tabla
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
$prefijoMonedaDominante = 'C$'; // usaremos el último visto si varía por alumno
foreach ($deudores as $d) {
    $mesLabel = ($d['meses_cnt'] > 1) ? 'meses' : 'mes';
    $montoFmt = number_format((float)$d['monto_total'], 2, '.', ','); // igual que tu vista
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

// Totales
$html .= '<br><table>
  <tr>
    <td><strong>Total alumnos en mora:</strong> '.$total_alumnos_mora.'</td>
    <td class="text-right"><strong>Total adeudado:</strong></td>
    <td class="text-right"><strong>'.$prefijoMonedaDominante.' '.number_format($deuda_total, 2, '.', ',').'</strong></td>
  </tr>
</table>';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('alumnos_en_mora_'.$ano_actual.'.pdf', 'I');
