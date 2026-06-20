<?php
session_start();
include_once '../includes/config.php';
include_once '../includes/security.php';
include_once '../tcpdf/tcpdf.php';
include_once '../includes/tcpdf_netsoluciones.php';
include_once '../PHPMailer/src/PHPMailer.php';
include_once '../PHPMailer/src/SMTP.php';
include_once '../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

date_default_timezone_set('America/Managua');

if (empty($_POST['rango'])) {
    echo "mal";
    exit;
}

list($fecha_inicio, $fecha_fin) = explode('|', $_POST['rango']);
$fecha_inicio = DateTime::createFromFormat('d/m/Y', $fecha_inicio)->format('Y-m-d');
$fecha_fin    = DateTime::createFromFormat('d/m/Y', $fecha_fin)->format('Y-m-d');

// ================== DATOS NEGOCIO / EMAIL ==================
$queryNegocio = mysqli_query($link, "SELECT * FROM tbl_parametros");
$rowNegocio   = mysqli_fetch_array($queryNegocio);

$nombre_colegio = $rowNegocio['NOMBRECOLEGIO'] ?? 'Colegio';
$logoRel        = $rowNegocio['logo_colegio'] ?? '';
$logoPath       = $logoRel ? (__DIR__.'/../'.$logoRel) : (__DIR__.'/../img/Logo-SYSCPE.png');

// Config SMTP
$queryEmail = mysqli_query($link, "SELECT * FROM tbla_email");
$rowEmail   = mysqli_fetch_array($queryEmail);

// 👇 Ajusta este correo al del gerente (o sácalo de la BD si ya lo tienes)

$emailGerente = $_SESSION['email_usuario'];

// ================== CONSULTA DETALLE CIERRE ==================
$sql = "SELECT 
            CASE WHEN R.IE = 'E' THEN 'EGRESOS' ELSE 'INGRESOS' END AS CAJA, 
            CP.Concepto AS NombreCategoria, 
            DATE_FORMAT(R.FechaPago, '%d/%m/%Y') AS FechaPago, 
            SUM(R.ValorTotal) AS VALOR 
        FROM tbl_recibo R 
        INNER JOIN tbl_categoriapago CP 
            ON CP.Id = R.IdCategoriaConcepto 
        WHERE R.Anulado = 0 
          AND R.FechaPago BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'
        GROUP BY R.IE, CP.Concepto, DATE_FORMAT(R.FechaPago, '%d/%m/%Y')";
$queryDetalleRecibo = mysqli_query($link, $sql);

$detalles    = [];
$totalRecibo = 0;
while ($row = mysqli_fetch_array($queryDetalleRecibo)) {
    $detalles[] = [
        'NombreCategoria' => $row['NombreCategoria'],
        'VALOR'           => (float)$row['VALOR']
    ];
    $totalRecibo += (float)$row['VALOR'];
}

// ================== GENERAR PDF EN DISCO (TITILLIUM) ==================
$pdf_fontsubsetting = true;
$fontDir = __DIR__ . '/../fonts/';

// Registrar fuentes Titillium
TCPDF_FONTS::addTTFfont($fontDir.'TitilliumWeb-Regular.ttf', 'TrueTypeUnicode', '', 96);
TCPDF_FONTS::addTTFfont($fontDir.'TitilliumWeb-Italic.ttf', 'TrueTypeUnicode', '', 96);
TCPDF_FONTS::addTTFfont($fontDir.'TitilliumWeb-Bold.ttf', 'TrueTypeUnicode', '', 96);
TCPDF_FONTS::addTTFfont($fontDir.'TitilliumWeb-BoldItalic.ttf', 'TrueTypeUnicode', '', 96);

$pdf = new TCPDFNetsoluciones('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true);
$pdf->setFontSubsetting(true);
$pdf->SetMargins(15, 20, 15);
$pdf->AddPage();

// Título colegio
$pdf->SetFont('titilliumweb', 'B', 18);
$pdf->Cell(0, 10, $nombre_colegio, 0, 1, 'C');

// Logo
if (file_exists($logoPath)) {
    $imgWidth  = 60;
    $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
    $xLogo     = $pdf->getMargins()['left'] + ($pageWidth - $imgWidth) / 2;
    $yLogo     = $pdf->GetY() + 2;

    $pdf->Image($logoPath, $xLogo, $yLogo, $imgWidth, 0, '', '', '', true, 300, '', false, false, 0, false, false, false);
    $pdf->SetY($yLogo + 40);
} else {
    $pdf->Ln(10);
}

// Subtítulo
$pdf->SetFont('titilliumweb', 'B', 14);
$pdf->Cell(0, 23, 'CIERRE DE CAJA', 0, 1, 'C');

$pdf->SetFont('titilliumweb', '', 11);
$pdf->Cell(
    0,
    6,
    'Fecha '.date('Y-m-d', strtotime($fecha_inicio)).' al '.date('Y-m-d', strtotime($fecha_fin)),
    0,
    1,
    'C'
);

$pdf->Ln(10);

// Tabla
$html = '
<style>
  .ctr { text-align:center; }
  .tiny { font-size:10px; color:#5a5a5a; }
  .tbl { width:100%; border-collapse:collapse; }
  .tbl th, .tbl td {
      font-size:11px;
      padding:6px 4px;
  }
  .tbl th {
      font-weight:700;
      border-bottom:1px solid #000;
  }
  .tbl td.num {
      text-align:right;
  }
</style>

<table class="tbl">
  <thead>
    <tr>
      <th>CONCEPTO</th>
      <th class="num" style="text-align:right;">MONTO</th>
    </tr>
  </thead>
  <tbody>';
foreach ($detalles as $d) {
    $html .= '
    <tr>
      <td>'.htmlspecialchars($d['NombreCategoria'], ENT_QUOTES, 'UTF-8').'</td>
      <td class="num">'.number_format($d['VALOR'], 2, '.', ',').'</td>
    </tr>';
}
$html .= '
    <tr>
      <td style="font-weight:700; padding-top:6px;">TOTAL</td>
      <td class="num" style="font-weight:700; padding-top:6px;">'.number_format($totalRecibo, 2, '.', ',').'</td>
    </tr>
  </tbody>
</table>
';

$pdf->writeHTML($html, true, false, true, false, '');

// Guardar en carpeta (por si quieres historico)
$carpeta = __DIR__ . '/recibos';
if (!is_dir($carpeta)) {
    @mkdir($carpeta, 0775, true);
}
$nombreArchivo = 'cierre_caja_'.$fecha_inicio.'_al_'.$fecha_fin.'.pdf';
$ruta_guardado = $carpeta . '/' . $nombreArchivo;

$pdf->Output($ruta_guardado, 'F');

// ================== ENVIAR CORREO AL GERENTE ==================
$mail = new PHPMailer(true);

try {
    // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->isSMTP();
    $mail->CharSet   = 'UTF-8';
    $mail->Host      = $rowEmail['host'];
    $mail->SMTPAuth  = true;
    $mail->Username  = $rowEmail['username'];
    $mail->Password  = $rowEmail['password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port      = $rowEmail['port'];

    // Logo incrustado (opcional)
    if (file_exists($logoPath)) {
        $mail->addEmbeddedImage($logoPath, 'logo_colegio', 'logo.png');
    }

    $mail->setFrom($rowEmail['username'], $nombre_colegio);
    $mail->addAddress($emailGerente, 'Gerente');

    $mail->isHTML(true);

    // Adjuntar PDF
    $mail->addAttachment($ruta_guardado, 'Cierre de caja '.$fecha_inicio.' al '.$fecha_fin.'.pdf');

    $mail->Subject = 'Cierre de caja del '.$fecha_inicio.' al '.$fecha_fin;

    $fechaTexto = 'del '.date('d/m/Y', strtotime($fecha_inicio)).' al '.date('d/m/Y', strtotime($fecha_fin));
    $totalTexto = number_format($totalRecibo, 2, '.', ',');

    $mail->Body = '
    <div style="margin:0;padding:24px;background:#f5f7fb;
        font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#111;">
      <div style="max-width:600px;margin:0 auto;">
        <div style="text-align:center;margin-bottom:16px;">
          <img src="cid:logo_colegio" alt="Logo" style="width:64px;height:64px;border-radius:12px;display:inline-block;">
        </div>

        <div style="background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.06);
                    padding:24px;border:1px solid #eef0f4;">
          <h2 style="margin:0 0 6px;font-size:20px;line-height:1.35;font-weight:800;text-align:center;">
            Cierre de caja - '.$nombre_colegio.'
          </h2>
          <p style="margin:0 0 16px;font-size:13px;color:#667085;text-align:center;">
            Periodo <strong>'.$fechaTexto.'</strong><br>
            Total registrado: <strong>C$ '.$totalTexto.'</strong>
          </p>

          <p style="margin:0 0 10px;font-size:13px;color:#667085;text-align:left;">
            Se adjunta en PDF el detalle del cierre de caja para su revisión.
          </p>
        </div>
      </div>
    </div>
    ';

    $mail->AltBody =
        "Cierre de caja - $nombre_colegio\n".
        "Periodo $fechaTexto\n".
        "Total registrado: C$ $totalTexto\n".
        "Se adjunta el PDF con el detalle.";

    $mail->send();
    echo "bien";
} catch (Exception $e) {
    // Puedes loguear $mail->ErrorInfo si quieres
    echo "mal";
}
