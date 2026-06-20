<?php
session_start();
include_once '../includes/config.php';
include_once '../includes/security.php';
include_once '../tcpdf/tcpdf.php';
include_once '../includes/tcpdf_netsoluciones.php';

// =============== CONFIGURACIÓN WHATSAPP CLOUD API =====================
// Pon aquí tu token y phone_number_id (los mismos que usas en send_whasap_recibo.php)
$ACCESS_TOKEN    = 'EAAOFchLkvzUBP5BNN3bI9UGiakqG5WO1nmLxNGcySMiWCdZAfmhBefELRTPggo9ZCU7jd1qH1BuMF8ylZC36ShMdtEErR2eWQj7NtzotaegIafXwmQxgMYRMBmTx6VuWZCAxkBOOqRN43KUn3psi54kfNZBJagcN4sKTDHS4goKepUz560Lf4XactNVoL0bTJzwZDZD';  // <-- tu token permanente
$PHONE_NUMBER_ID = '891544247366984';  // <-- tu phone number id

date_default_timezone_set('America/Managua');

// =============== HELPERS ==============================================
function wa_log($msg) {
    $line = '['.date('Y-m-d H:i:s').'] '.$msg.PHP_EOL;
    file_put_contents(__DIR__.'/whatsapp_debug_cierre_caja.log', $line, FILE_APPEND);
}

function wa_curl_json($url, $headers, $payloadArr) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payloadArr, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 60,
    ]);
    $body  = curl_exec($ch);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    $code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['body'=>$body,'http_code'=>$code,'errno'=>$errno,'error'=>$error];
}

function wa_curl_upload($url, $headers, $filePath) {
    $ch = curl_init($url);
    $postFields = [
        'messaging_product' => 'whatsapp',
        'file' => new CURLFile($filePath, 'application/pdf', basename($filePath))
    ];
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postFields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 60,
    ]);
    $body  = curl_exec($ch);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    $code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['body'=>$body,'http_code'=>$code,'errno'=>$errno,'error'=>$error];
}

function normalize_phone_e164_no_plus($raw) {
    $digits = preg_replace('/\D+/', '', (string)$raw);
    return $digits;
}

// =============== VALIDACIÓN INPUT =====================================
if (empty($_POST['rango'])) {
    echo "mal";
    exit;
}

$rango = $_POST['rango'];
list($fecha_inicio, $fecha_fin) = explode('|', $rango);
$fecha_inicio = DateTime::createFromFormat('d/m/Y', $fecha_inicio)->format('Y-m-d');
$fecha_fin    = DateTime::createFromFormat('d/m/Y', $fecha_fin)->format('Y-m-d');

// =============== OBTENER USUARIO LOGUEADO Y SU TELÉFONO ===============
if (empty($_SESSION['id_usuario'])) {
    wa_log("SIN ID USUARIO EN SESIÓN");
    echo "mal";
    exit;
}

$idUsuario = (int)$_SESSION['id_usuario'];
$qUser = mysqli_query($link, "SELECT * FROM tbla_usuario WHERE id_usuario = '$idUsuario'");
$rowUser = mysqli_fetch_array($qUser);

// TODO: Ajusta el nombre del campo de teléfono real en tu tabla:
$telefonoUsuario = $rowUser['telefono'] ?? ''; // cambia 'telefono' si tu columna se llama diferente

if (empty($telefonoUsuario)) {
    wa_log("Usuario $idUsuario sin teléfono configurado");
    echo "mal";
    exit;
}

// Normalizar teléfono a E.164 sin '+', asumiendo Nicaragua (505)
$to = normalize_phone_e164_no_plus($telefonoUsuario);
if ($to !== '' && strpos($to, '505') !== 0) {
    $to = '505'.$to;
}
if (strlen($to) < 8) {
    wa_log("Teléfono inválido: original='{$telefonoUsuario}' normalizado='{$to}'");
    echo "mal";
    exit;
}

// =============== DATOS DEL COLEGIO ====================================
$queryNegocio = mysqli_query($link, "SELECT * FROM tbl_parametros");
$rowNegocio   = mysqli_fetch_array($queryNegocio);

$nombre_colegio = $rowNegocio['NOMBRECOLEGIO'] ?? 'Colegio';
$logoRel        = $rowNegocio['logo_colegio'] ?? '';
$logoPath       = $logoRel ? (__DIR__.'/../'.$logoRel) : (__DIR__.'/../img/Logo-SYSCPE.png');

// =============== CONSULTA DE DETALLE (igual a get_reporte_facturas.php)===
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

// =============== GENERAR PDF (Titillium Web) ===========================
try {
    // carpeta para guardar los cierres de caja
    $carpeta = __DIR__ . '/recibos_caja';
    if (!is_dir($carpeta)) {
        @mkdir($carpeta, 0775, true);
    }

    // Registrar fuente Titillium Web (como en tus otros PDFs)
    $fontDir = __DIR__ . '/../fonts/';
    // Estos archivos deben existir en esa carpeta:
    // TitilliumWeb-Regular.ttf, TitilliumWeb-Bold.ttf
    TCPDF_FONTS::addTTFfont($fontDir.'TitilliumWeb-Regular.ttf', 'TrueTypeUnicode', '', 96);
    TCPDF_FONTS::addTTFfont($fontDir.'TitilliumWeb-Bold.ttf', 'TrueTypeUnicode', '', 96);

    $pdf = new TCPDFNetsoluciones('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(true);
    $pdf->SetMargins(15, 20, 15);
    $pdf->AddPage();

    // Título colegio
    $pdf->SetFont('titilliumweb', 'B', 18);
    $pdf->Cell(0, 10, $nombre_colegio, 0, 1, 'C');

    // Logo centrado
    if (file_exists($logoPath)) {
        $imgWidth  = 50;
        $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
        $xLogo     = $pdf->getMargins()['left'] + ($pageWidth - $imgWidth) / 2;
        $yLogo     = $pdf->GetY() + 2;

        $pdf->Image($logoPath, $xLogo, $yLogo, $imgWidth, 0, '', '', '', true, 300, '', false, false, 0, false, false, false);
        $pdf->SetY($yLogo + 35);
    } else {
        $pdf->Ln(8);
    }

    // Subtítulo y rango
    $pdf->SetFont('titilliumweb', 'B', 14);
    $pdf->Cell(0, 23, 'CIERRE DE CAJA', 0, 1, 'C');

    $pdf->SetFont('titilliumweb', '', 11);
    $pdf->Cell(
        0,
        6,
        'Fecha '.date('d/m/Y', strtotime($fecha_inicio)).' al '.date('d/m/Y', strtotime($fecha_fin)),
        0,
        1,
        'C'
    );

    $pdf->Ln(8);

    // Tabla HTML
    $html = '
    <style>
      .tbl { width:100%; border-collapse:collapse; font-family:"Titillium Web", sans-serif; }
      .tbl th, .tbl td {
          font-size:11px;
          padding:8px 6px;
          border-bottom:1px solid #cbd5e1;
      }
      .tbl th {
          font-weight:700;
          background-color:#e2e8f0;
          text-transform:uppercase;
          letter-spacing:.3px;
      }
      .tbl td.num {
          text-align:right;
      }
      .total-row td {
          font-weight:700;
          border-top:1px solid #94a3b8;
      }
    </style>

    <table class="tbl">
      <thead>
        <tr>
          <th>Concepto</th>
          <th class="num">Monto</th>
        </tr>
      </thead>
      <tbody>
    ';

    foreach ($detalles as $d) {
        $html .= '
        <tr>
          <td>'.htmlspecialchars($d['NombreCategoria'], ENT_QUOTES, 'UTF-8').'</td>
          <td class="num">'.number_format($d['VALOR'], 2, '.', ',').'</td>
        </tr>';
    }

    $html .= '
        <tr class="total-row">
          <td>Total</td>
          <td class="num">'.number_format($totalRecibo, 2, '.', ',').'</td>
        </tr>
      </tbody>
    </table>

    <div style="height:14px;"></div>
    ';

    $pdf->writeHTML($html, true, false, true, false, '');

    // Guardar PDF en disco
    $nombreArchivo = 'cierre_caja_'.$fecha_inicio.'_al_'.$fecha_fin.'.pdf';
    $ruta_guardado = $carpeta . '/' . $nombreArchivo;
    $pdf->Output($ruta_guardado, 'F');

} catch (Exception $e) {
    wa_log("ERROR TCPDF: ".$e->getMessage());
    echo "mal";
    exit;
}

// =============== ENVIAR POR WHATSAPP ===================================
$uploadUrl     = "https://graph.facebook.com/v23.0/{$PHONE_NUMBER_ID}/media";
$uploadHeaders = ["Authorization: Bearer {$ACCESS_TOKEN}"];

// 1) Subir media
$upload = wa_curl_upload($uploadUrl, $uploadHeaders, $ruta_guardado);
wa_log("UPLOAD CIERRE HTTP {$upload['http_code']} | body={$upload['body']} | errno={$upload['errno']} | error={$upload['error']}");

$uploadData = json_decode($upload['body'], true);
$mediaId    = $uploadData['id'] ?? null;

if (!$mediaId) {
    wa_log("UPLOAD CIERRE FAILED. Response: ".$upload['body']);
    echo "mal";
    exit;
}

// 2) Enviar documento al usuario logueado
$sendUrl     = "https://graph.facebook.com/v23.0/{$PHONE_NUMBER_ID}/messages";
$sendHeaders = [
    "Authorization: Bearer {$ACCESS_TOKEN}",
    "Content-Type: application/json"
];

$filename = 'Cierre de caja '.$fecha_inicio.' al '.$fecha_fin.'.pdf';
$payload  = [
    'messaging_product' => 'whatsapp',
    'to'                => $to, // E.164 sin '+'
    'type'              => 'document',
    'document'          => [
        'id'       => $mediaId,
        'filename' => $filename,
        'caption'  => 'Cierre de caja '.$nombre_colegio.' ('.$fecha_inicio.' al '.$fecha_fin.')'
    ]
];

$send = wa_curl_json($sendUrl, $sendHeaders, $payload);
wa_log("SEND CIERRE HTTP {$send['http_code']} | body={$send['body']} | errno={$send['errno']} | error={$send['error']}");

$sendData = json_decode($send['body'], true);

if (isset($sendData['messages'][0]['id'])) {
    echo "bien";
    exit;
}

wa_log("SEND CIERRE FAILED. Response: ".$send['body']);
echo "mal";
exit;
