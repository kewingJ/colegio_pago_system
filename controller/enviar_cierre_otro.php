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

// ================== VALIDACIÓN INPUT ==================
if (empty($_POST['rango']) || empty($_POST['email_otro'])) {
    echo "mal";
    exit;
}

$rango       = $_POST['rango'];
$email_otro  = clean(mysqli_real_escape_string($link, $_POST['email_otro']));

// Parsear rango: DD/MM/YYYY|DD/MM/YYYY
list($fecha_inicio, $fecha_fin) = explode('|', $rango);
$fi_dt = DateTime::createFromFormat('d/m/Y', $fecha_inicio);
$ff_dt = DateTime::createFromFormat('d/m/Y', $fecha_fin);

if (!$fi_dt || !$ff_dt) {
    echo "mal";
    exit;
}

$fi_sql = $fi_dt->format('Y-m-d');
$ff_sql = $ff_dt->format('Y-m-d');

// ================== DATOS DEL COLEGIO ==================
$queryNegocio = mysqli_query($link, "SELECT * FROM tbl_parametros");
$rowNegocio   = mysqli_fetch_array($queryNegocio);

$nombre_colegio = $rowNegocio['NOMBRECOLEGIO'] ?? 'Colegio';
$logoRel        = $rowNegocio['logo_colegio'] ?? '';
$logoPath       = $logoRel ? (__DIR__.'/../'.$logoRel) : (__DIR__.'/../img/Logo-SYSCPE.png');

// Config SMTP
$queryEmail = mysqli_query($link, "SELECT * FROM tbla_email");
$rowEmail   = mysqli_fetch_array($queryEmail);

// ================== CONSULTA DE DETALLE ==================
$sql = "SELECT 
            CASE WHEN R.IE = 'E' THEN 'EGRESOS' ELSE 'INGRESOS' END AS CAJA, 
            CP.Concepto AS NombreCategoria, 
            DATE_FORMAT(R.FechaPago, '%d/%m/%Y') AS FechaPago, 
            SUM(R.ValorTotal) AS VALOR 
        FROM tbl_recibo R 
        INNER JOIN tbl_categoriapago CP 
            ON CP.Id = R.IdCategoriaConcepto 
        WHERE R.Anulado = 0 
          AND R.FechaPago BETWEEN '$fi_sql 00:00:00' AND '$ff_sql 23:59:59'
        GROUP BY R.IE, CP.Concepto, DATE_FORMAT(R.FechaPago, '%d/%m/%Y')";

$queryDetalleRecibo = mysqli_query($link, $sql);

$detalles    = [];
$totalRecibo = 0;
while ($row = mysqli_fetch_array($queryDetalleRecibo)) {
    $valor = (float)$row['VALOR'];
    $detalles[] = [
        'NombreCategoria' => $row['NombreCategoria'],
        'VALOR'           => $valor
    ];
    $totalRecibo += $valor;
}

// ================== GENERAR PDF (ESTILO TITILLIUM) ==================
try {
    // Carpeta donde se guarda el PDF
    $carpeta = __DIR__ . '/recibos';
    if (!is_dir($carpeta)) {
        @mkdir($carpeta, 0775, true);
    }

    // Registrar fuentes Titillium Web
    $fontDir = __DIR__ . '/../fonts/';
    TCPDF_FONTS::addTTFfont($fontDir.'TitilliumWeb-Regular.ttf', 'TrueTypeUnicode', '', 96);
    TCPDF_FONTS::addTTFfont($fontDir.'TitilliumWeb-Bold.ttf', 'TrueTypeUnicode', '', 96);
    TCPDF_FONTS::addTTFfont($fontDir.'TitilliumWeb-Italic.ttf', 'TrueTypeUnicode', '', 96);
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

    // Logo centrado
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
        0,
        'Fecha '.$fi_dt->format('Y-m-d').' al '.$ff_dt->format('Y-m-d'),
        0,
        1,
        'C'
    );

    $pdf->Ln(10);

    // Tabla de datos
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

    // Guardar PDF en servidor
    $nombreArchivo = 'cierre_caja_'.$fi_sql.'_al_'.$ff_sql.'.pdf';
    $ruta_guardado = $carpeta . '/' . $nombreArchivo;
    $pdf->Output($ruta_guardado, 'F');

} catch (Exception $e) {
    // Si algo falla creando el PDF
    echo "mal";
    exit;
}

// ================== ENVIAR CORREO ==================
$mail = new PHPMailer(true);

try {
    // Servidor SMTP
    $mail->isSMTP();
    $mail->CharSet   = 'UTF-8';
    $mail->Host      = $rowEmail['host'];
    $mail->SMTPAuth  = true;
    $mail->Username  = $rowEmail['username'];
    $mail->Password  = $rowEmail['password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port      = $rowEmail['port'];

    // Logo embebido
    if (file_exists($logoPath)) {
        $mail->addEmbeddedImage($logoPath, 'logo_colegio', 'logo.png');
    }

    // Remitente y destino
    $mail->setFrom($rowEmail['username'], $nombre_colegio);
    $mail->addAddress($email_otro);

    $mail->isHTML(true);

    // Adjuntar PDF
    $mail->addAttachment($ruta_guardado, $nombreArchivo);

    $colegioHtml = htmlspecialchars($nombre_colegio ?? 'Colegio', ENT_QUOTES, 'UTF-8');

    $mail->Subject = 'Cierre de caja del '.$fi_dt->format('d/m/Y').' al '.$ff_dt->format('d/m/Y');

    $mail->Body = '
    <div style="margin:0;padding:24px;background:#f5f7fb;
                font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif;
                color:#111;">
      <div style="max-width:600px;margin:0 auto;">

        <div style="text-align:center;margin-bottom:16px;">
          <img src="cid:logo_colegio" alt="Logo" style="width:64px;height:64px;border-radius:12px;display:inline-block;">
        </div>

        <div style="background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.06);
                    padding:24px;border:1px solid #eef0f4;">
          <h2 style="margin:0 0 8px;font-size:20px;line-height:1.4;font-weight:800;text-align:center;">
            Cierre de caja - '.$colegioHtml.'
          </h2>
          <p style="margin:0 0 16px;font-size:13px;color:#667085;text-align:center;">
            Período del <strong>'.$fi_dt->format('d/m/Y').'</strong> al <strong>'.$ff_dt->format('d/m/Y').'</strong>
          </p>

          <p style="margin:0 0 8px;font-size:13px;color:#111;text-align:left;">
            Se adjunta el PDF con el detalle del cierre de caja para el período indicado.
          </p>

          <p style="margin:8px 0 0;font-size:13px;color:#111;text-align:left;">
            <strong>Total:</strong> C$ '.number_format($totalRecibo, 2, '.', ',').'
          </p>
        </div>
      </div>
    </div>
    ';

    $mail->AltBody =
        "Cierre de caja - $nombre_colegio\n".
        "Periodo: ".$fi_dt->format('d/m/Y')." al ".$ff_dt->format('d/m/Y')."\n".
        "Total: C$ ".number_format($totalRecibo, 2, '.', ',')."\n".
        "Se adjunta el PDF con el detalle del cierre.";

    $mail->send();
    echo "bien";

} catch (Exception $e) {
    // Si falla el envío
    echo "mal";
}
