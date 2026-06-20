<?php
// reports/send_reporte_alumno_email.php
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/security.php';
date_default_timezone_set('America/Managua');
setlocale(LC_TIME, 'es_NI.UTF-8', 'es_ES.UTF-8', 'es_ES', 'es');

// TCPDF (ajusta la ruta a tu librería)
require_once __DIR__ . '/../tcpdf/tcpdf.php';
require_once __DIR__ . '/../includes/tcpdf_netsoluciones.php';

// PHPMailer
include_once '../PHPMailer/src/PHPMailer.php';
include_once '../PHPMailer/src/SMTP.php';
include_once '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();
if (empty($_SESSION['id_usuario']) || empty($_SESSION['activo'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'No autorizado.']);
    exit;
}

// Validación básica
$id_alumno = isset($_POST['id_alumno']) ? (int) $_POST['id_alumno'] : 0;
$id_anio   = isset($_POST['id_anio'])   ? (int) $_POST['id_anio']   : 0;
$email_nuevo     = isset($_POST['email'])     ? trim($_POST['email'])     : '';

if ($id_alumno <= 0 || $id_anio <= 0) {
    echo json_encode(['ok' => false, 'error' => 'Parámetros inválidos.']);
    exit;
}

// 1) Obtén email y nombre del alumno
$sqlAlumno = "SELECT NOMBREAPELLIDO, EMAIL
              FROM tbl_alumnos
              WHERE IDALUMNO = ?
              LIMIT 1";
$stmtA = $link->prepare($sqlAlumno);
$stmtA->bind_param('i', $id_alumno);
$stmtA->execute();
$resA = $stmtA->get_result();
$alumno = $resA->fetch_assoc();
$stmtA->close();

$nombreAlumno = $alumno ? $alumno['NOMBREAPELLIDO'] : '';
// $nombrePapa = $alumno ? $alumno['NOMBREPADRE'] : '';
// $nombreMama = $alumno ? $alumno['NOMBREMADRE'] : '';

if ($email_nuevo !== '' && filter_var($email_nuevo, FILTER_VALIDATE_EMAIL)) {
    $correoAlumno = $email_nuevo;
} else {
    $correoAlumno = $alumno ? ($alumno['EMAIL'] ?? '') : '';
}

if (empty($correoAlumno) || !filter_var($correoAlumno, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok' => false, 'error' => 'El alumno no tiene un correo válido registrado.']);
    exit;
}

// 2) Misma consulta que tu reporte, para armar el PDF
$sql = "
    SELECT 
        R.IdReciboMaestro AS NoFACTURA,
        R.FechaPago       AS FECHAPAGO,
        A.NOMBREAPELLIDO  AS NombreApellido,
        CASE 
            WHEN CAP.Id = 3 THEN CONCAT(
                CAP.Concepto, ' ', 
                CASE MONTH(R.MesReferencia)
                    WHEN 1 THEN 'ENERO'
                    WHEN 2 THEN 'FEBRERO'
                    WHEN 3 THEN 'MARZO'
                    WHEN 4 THEN 'ABRIL'
                    WHEN 5 THEN 'MAYO'
                    WHEN 6 THEN 'JUNIO'
                    WHEN 7 THEN 'JULIO'
                    WHEN 8 THEN 'AGOSTO'
                    WHEN 9 THEN 'SEPTIEMBRE'
                    WHEN 10 THEN 'OCTUBRE'
                    WHEN 11 THEN 'NOVIEMBRE'
                    WHEN 12 THEN 'DICIEMBRE'
                    ELSE ''
                END
            )
            ELSE CONCAT(CAP.Concepto, ' ', COP.Concepto)
        END AS CONCEPTO,
        R.ValorTotal,
        (
            SELECT TIPOMONEDA
            FROM tbl_aranceles Ara
            WHERE Ara.IdNivel = R.Nivel 
              AND Ara.IdCategoria = R.IdCategoriaConcepto 
              AND Ara.IdConcepto = R.IdConcepto 
              AND Ara.Anio = R.Anio
            LIMIT 1
        ) AS TIPOMONEDA
    FROM tbl_recibo R
    INNER JOIN tbl_categoriapago CAP ON CAP.Id = R.IdCategoriaConcepto
    INNER JOIN tbl_conceptospago COP ON COP.IdConcepto = R.IdConcepto
    INNER JOIN tbl_alumnos A ON A.IDALUMNO = R.IDALUMNO
    WHERE R.Anulado = 0 
      AND R.ANIO = ? 
      AND R.IdAlumno = ?
    ORDER BY R.FechaPago ASC, R.IdReciboMaestro ASC
";
$stmt = $link->prepare($sql);
$stmt->bind_param('ii', $id_anio, $id_alumno);
$stmt->execute();
$res = $stmt->get_result();

$rows = [];
$moneda = 'C$';
$alumnoNombreFmt = '';
while ($r = $res->fetch_assoc()) {
    $rows[] = $r;
    if ($alumnoNombreFmt === '' && !empty($r['NombreApellido'])) {
        $alumnoNombreFmt = mb_convert_case(mb_strtolower($r['NombreApellido'],'UTF-8'), MB_CASE_TITLE,'UTF-8');
    }
    if (!empty($r['TIPOMONEDA'])) {
        $tip = strtoupper(trim($r['TIPOMONEDA']));
        $moneda = ($tip === 'USD' || $tip === 'DOLAR' || $tip === 'DÓLAR') ? '$' : 'C$';
    }
}
$stmt->close();

$total = 0.0;
foreach ($rows as $r) $total += (float)$r['ValorTotal'];

// 3) Genera el PDF en MEMORIA (string)
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
$pdf->titulo   = 'Reporte de pagos por alumno';
$pdf->subtitulo= 'Año lectivo: ' . $id_anio;
$pdf->info     = 'Alumno: ' . ($alumnoNombreFmt ?: $nombreAlumno ?: 'N/D') . '   |   Generado: ' . date('d/m/Y H:i');

$pdf->SetCreator('Sistema');
$pdf->SetAuthor('Netsoluciones');
$pdf->SetTitle('Reporte de pagos por alumno');
$pdf->SetMargins(15, 36, 15);
$pdf->SetAutoPageBreak(true, 18);
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);
//$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('dejavusans', '', 10);

// Tabla HTML
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
<th>No. Recibo</th>
<th>Fecha de pago</th>
<th>Concepto</th>
<th class="text-right">Monto pagado</th>
</tr>
</thead><tbody>';

if (count($rows) > 0) {
    foreach ($rows as $r) {
        $no = htmlspecialchars((string)$r['NoFACTURA'], ENT_QUOTES, 'UTF-8');
        $fecha = '';
        if (!empty($r['FECHAPAGO'])) {
            try { $fecha = (new DateTime($r['FECHAPAGO']))->format('d/m/Y'); }
            catch (Exception $e) { $fecha = htmlspecialchars((string)$r['FECHAPAGO'], ENT_QUOTES, 'UTF-8'); }
        }
        $concepto = htmlspecialchars((string)$r['CONCEPTO'], ENT_QUOTES, 'UTF-8');
        $monto = number_format((float)$r['ValorTotal'], 2, ',', '.');

        $html .= '<tr>
            <td>'.$no.'</td>
            <td>'.$fecha.'</td>
            <td>'.$concepto.'</td>
            <td class="text-right">'.$moneda.' '.$monto.'</td>
        </tr>';
    }
} else {
    $html .= '<tr><td colspan="4">No se encontraron pagos para el alumno en este año.</td></tr>';
}

$html .= '</tbody></table>';

$html .= '<br><table>
<tr>
  <td></td>
  <td></td>
  <td class="text-right"><strong>TOTAL:</strong></td>
  <td class="text-right"><strong>'.$moneda.' '.number_format($total, 2, ',', '.').'</strong></td>
</tr>
</table>';

$pdf->writeHTML($html, true, false, true, false, '');
$filename = 'reporte_pagos_alumno_'.$id_alumno.'_'.$id_anio.'.pdf';

// Salida en memoria (string)
$pdfString = $pdf->Output($filename, 'S');

//obtener datos de mail
$queryEmail = mysqli_query($link, "SELECT * FROM tbla_email");
$rowEmail = mysqli_fetch_array($queryEmail);

//obtener informacion del colegio
$queryColegio = mysqli_query($link, "SELECT * FROM tbl_parametros");
$rowColegio = mysqli_fetch_array($queryColegio);
$nombre_colegio = $rowColegio['NOMBRECOLEGIO'];
// Logo
if(empty($rowColegio['logo_colegio'])){
    $logoPath = __DIR__ . '/../img/Logo-SYSCPE.png';
} else {
    $logoPath = __DIR__ . '/../'.$rowColegio['logo_colegio'];
}

$mail = new PHPMailer(true);

// 4) Enviar el email con adjunto
try {
    $mail->isSMTP();
    $mail->CharSet = 'UTF-8';
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

    // 
    $mail->setFrom($rowEmail['username'], $nombre_colegio);
    $mail->addAddress($correoAlumno, $alumnoNombreFmt ?: $nombreAlumno);
    $mail->isHTML(true);

    $mail->Subject = 'Reporte de pagos - ' . ($alumnoNombreFmt ?: $nombreAlumno ?: 'Alumno') . " ($id_anio)";

    // Variables seguras para HTML
    $colegioHtml = htmlspecialchars($nombre_colegio, ENT_QUOTES, 'UTF-8');
    $alumnoHtml  = htmlspecialchars($alumnoNombreFmt ?: $nombreAlumno ?: 'N/D', ENT_QUOTES, 'UTF-8');
    $totalHtml   = $moneda . ' ' . number_format($total, 2, ',', '.');
    $nombreColegioSafe = htmlspecialchars($nombre_colegio ?? 'Colegio', ENT_QUOTES, 'UTF-8');

    $mail->Body = '
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f6f7fb;padding:24px 0;">
    <tr>
        <td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;border:1px solid #eaeaea;">
            <tr>
            <td style="padding:24px 24px 12px 24px; text-align:center;">
                ' . (file_exists($logoPath) ? '<img src="cid:logo_colegio" alt="Logo" style="width:72px;height:auto;display:block;margin:0 auto 8px auto;">' : '') . '
                <div style="font-family:Arial,Helvetica,sans-serif;font-size:18px;font-weight:700;color:#111;">' . $colegioHtml . '</div>
                <div style="font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#555;margin-top:4px;">Reporte de pagos por alumno · Año lectivo ' . (int)$id_anio . '</div>
            </td>
            </tr>
            <tr>
            <td style="padding:0 24px 8px 24px;">
                <hr style="border:none;border-top:1px solid #eaeaea;margin:0;">
            </td>
            </tr>
            <tr>
            <td style="padding:0 24px 16px 24px; font-family:Arial,Helvetica,sans-serif; font-size:14px; color:#333; line-height:1.6;">
                <p style="margin:16px 0 8px 0;">Estimado(a) <strong>Padre de familia</strong>,</p>
                <p style="margin:0 0 12px 0;">Adjuntamos el <strong>reporte de pagos</strong> del alumno <strong>' . $alumnoHtml . '</strong> correspondiente al año lectivo <strong>' . (int)$id_anio . '</strong>.</p>
                <p style="margin:0 0 12px 0;">Total pagado: <strong>' . $totalHtml . '</strong></p>
                <p style="margin:0 0 12px 0;">Si tiene alguna consulta, por favor responda a este correo.</p>
                <p style="margin:0;">Saludos cordiales,</p>
            </td>
            </tr>
            <tr>
            <td style="padding:12px 24px 24px 24px; font-family:Arial,Helvetica,sans-serif; font-size:12px; color:#999; text-align:center;">
                <div style="font-size:18px;font-weight:800;letter-spacing:.2px;color:#111;margin-bottom:4px;">' . $nombreColegioSafe . '</div>
                <div>Este mensaje fue generado automáticamente por el sistema.</div>
            </td>
            </tr>
        </table>
        </td>
    </tr>
    </table>';

    $mail->AltBody = 'Reporte de pagos por alumno (' . $id_anio . '). Alumno: ' . ($alumnoNombreFmt ?: $nombreAlumno ?: 'N/D') .
                     '. Total pagado: ' . $totalHtml . '. Se adjunta el PDF.';

    // Adjunta el PDF desde memoria
    $mail->addStringAttachment($pdfString, $filename, 'base64', 'application/pdf');

    $mail->send();
    echo json_encode(['ok' => true, 'msg' => 'Correo enviado a ' . $correoAlumno]);
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'error' => 'No se pudo enviar el correo: ' . $e->getMessage()]);
}
