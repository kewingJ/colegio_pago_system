<?php
/**
 * send_whasap_recibo.php
 * Enviar recibo PDF por WhatsApp Cloud API (document media).
 */

session_start();
include_once '../includes/config.php';
include_once '../includes/security.php';
include_once '../tcpdf/tcpdf.php';
include_once '../includes/tcpdf_netsoluciones.php';

// =============== DEBUG LOCAL (quítalo en prod si quieres) ===============
error_reporting(E_ALL);
ini_set('display_errors', '1');

// =============== HELPERS ===============================================
function wa_log($msg) {
    $line = '['.date('Y-m-d H:i:s').'] '.$msg.PHP_EOL;
    file_put_contents(__DIR__.'/whatsapp_debug.log', $line, FILE_APPEND);
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
    // deja solo dígitos (quita espacios, +, guiones, etc.)
    $digits = preg_replace('/\D+/', '', (string)$raw);
    return $digits;
}

// =============== VALIDACIÓN INPUT ======================================
if (
    empty($_POST['id_recibo_email']) ||
    empty($_POST['contenidoRecibo']) ||
    empty($_POST['id_alumno_email']) ||
    empty($_POST['telefono_alumno'])
) {
    wa_log('FALTAN PARAMETROS POST: '.json_encode($_POST));
    echo "mal";
    exit;
}

$contenidoRecibo = $_POST['contenidoRecibo'];
$telefono_alumno = clean(mysqli_real_escape_string($link, $_POST['telefono_alumno']));
$id_recibo_email = clean(mysqli_real_escape_string($link, $_POST['id_recibo_email']));
$id_alumno_email = clean(mysqli_real_escape_string($link, $_POST['id_alumno_email']));

// =============== PARSEAR HTML DE LÍNEAS Y TOTAL ========================
$detalles   = [];     // [[concepto, valorNum], ...]
$montoTotal = null;   // número
$prefijo    = '';     // 'C$' o '$' si se detecta

libxml_use_internal_errors(true);
$dom = new DOMDocument();
$ok  = $dom->loadHTML('<?xml encoding="UTF-8">'.$contenidoRecibo);
libxml_clear_errors();

if ($ok) {
    $xpath = new DOMXPath($dom);

    // 1) Buscar filas de "Concepto / Valor"
    $trs = $xpath->query('//table//tr');
    foreach ($trs as $tr) {
        $tds = $tr->getElementsByTagName('td');
        if ($tds->length == 2) {
            $c1 = trim($tds->item(0)->textContent);
            $c2 = trim($tds->item(1)->textContent);

            // omitir encabezados/separadores
            if ($c1 === '' || stripos($c1, 'Concepto') !== false || stripos($c2, 'Valor') !== false) continue;
            if (strpos($c1, '---') !== false || strpos($c2, '---') !== false) continue;

            // omitir la fila cruda de "Total" del ticket (para no duplicar)
            if (preg_match('/^\s*Total\b/i', $c1)) continue;

            // detectar prefijo monetario global
            if ($prefijo === '') {
                if (stripos($c1, 'C$') !== false || stripos($c2, 'C$') !== false) $prefijo = 'C$';
                if (stripos($c1, '$')  !== false || stripos($c2, '$')  !== false) $prefijo = ($prefijo ?: '$');
            }

            // limpiar prefijo del texto del concepto (no queremos "C$" en el concepto)
            $conceptoLimpio = preg_replace('/\s*(C\$|\$)\s*$/', '', $c1);

            // obtener valor numérico
            $valorTxt = preg_replace('/[^\d\.\,]/', '', $c2);
            $valorNum = is_numeric(str_replace(',', '.', $valorTxt)) ? (float)str_replace(',', '.', $valorTxt) : null;

            if ($conceptoLimpio !== '' && $valorNum !== null) {
                $detalles[] = [$conceptoLimpio, $valorNum];
            } elseif ($conceptoLimpio !== '' && $c2 !== '') {
                // fallback si no se pudo parsear numérico
                $detalles[] = [$conceptoLimpio, $c2];
            }
        }
    }

    // 2) Buscar total en la sección "Total"
    $totalTrs = $xpath->query('//tr[td[contains(translate(normalize-space(.),"total","TOTAL"),"TOTAL")]]');
    foreach ($totalTrs as $tr) {
        $tds = $tr->getElementsByTagName('td');
        if ($tds->length >= 2) {
            $totalTxt = trim($tds->item($tds->length - 1)->textContent);
            $totalNum = str_replace(',', '.', preg_replace('/[^\d\.\,]/', '', $totalTxt));
            if ($totalTxt !== '') {
                $montoTotal = is_numeric($totalNum) ? (float)$totalNum : null;
                if ($prefijo === '') {
                    if (stripos($totalTxt, 'C$') !== false) $prefijo = 'C$';
                    elseif (strpos($totalTxt, '$') !== false) $prefijo = '$';
                }
                break;
            }
        }
    }
}
// Fallbacks
if ($prefijo === '') $prefijo = 'C$';
$amount = ($montoTotal !== null)
    ? $prefijo.' '.number_format($montoTotal, 2, '.', ',')
    : '—';

// Render de filas (prefijo SIEMPRE en importes) + zebra
$rowsHtml = '';
$idx = 0;
foreach ($detalles as [$concepto, $valor]) {
    $valorOut = is_numeric($valor)
      ? $prefijo.' '.number_format((float)$valor, 2, '.', ',')
      : htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');

    $trClass = ($idx % 2 === 1) ? ' class="row-alt"' : '';
    $rowsHtml .= '
      <tr'.$trClass.'>
        <td>'.htmlspecialchars($concepto, ENT_QUOTES, 'UTF-8').'</td>
        <td style="text-align:right;">'.$valorOut.'</td>
      </tr>';
    $idx++;
}
if ($montoTotal !== null) {
    $rowsHtml .= '
      <tr class="total-row">
        <td>Total pagado</td>
        <td style="text-align:right;">'.$amount.'</td>
      </tr>';
}

// =============== DATOS DE BD (alumno / colegio) =======================
$queryAlumno = mysqli_query($link, "SELECT * FROM tbl_alumnos WHERE IDALUMNO = '$id_alumno_email'");
$rowAlumno   = mysqli_fetch_array($queryAlumno);

$queryColegio = mysqli_query($link, "SELECT * FROM tbl_parametros");
$rowColegio   = mysqli_fetch_array($queryColegio);

$nombre_colegio = $rowColegio['NOMBRECOLEGIO'] ?? 'Colegio';
$logoPath = empty($rowColegio['logo_colegio'])
    ? __DIR__ . '/../img/Logo-SYSCPE.png'
    : __DIR__ . '/../' . $rowColegio['logo_colegio'];

$alumno  = htmlspecialchars($rowAlumno['NOMBREAPELLIDO'] ?? '', ENT_QUOTES, 'UTF-8');
$fechaHoy = date('d/m/Y');

// =============== GENERAR PDF ==========================================
try {
    // Garantizar carpeta
    $carpeta = __DIR__ . '/recibos';
    if (!is_dir($carpeta)) {
        @mkdir($carpeta, 0775, true);
    }

    // --- Fuentes Titillium Web (Google Fonts) ---
    $pdf_fontsubsetting = true;
    $fontDir = __DIR__ . '/../fonts/';

    // Registrar (si ya existen en caché, devuelve fontkey)
    $fw_reg  = TCPDF_FONTS::addTTFfont($fontDir.'TitilliumWeb-Regular.ttf', 'TrueTypeUnicode', '', 96);
    $fw_ita  = TCPDF_FONTS::addTTFfont($fontDir.'TitilliumWeb-Italic.ttf', 'TrueTypeUnicode', '', 96);
    $fw_bold = TCPDF_FONTS::addTTFfont($fontDir.'TitilliumWeb-Bold.ttf', 'TrueTypeUnicode', '', 96);
    $fw_bita = TCPDF_FONTS::addTTFfont($fontDir.'TitilliumWeb-BoldItalic.ttf', 'TrueTypeUnicode', '', 96);

    // Crear PDF
    $pdf = new TCPDFNetsoluciones('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(true);
    $pdf->setFontSubsetting(true);
    $pdf->SetMargins(15, 20, 15);

    // Página
    $pdf->AddPage();

    // Logo centrado
    $imgWidth  = 28;
    $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
    $xLogo     = ($pdf->getMargins()['left']) + ($pageWidth - $imgWidth) / 2;
    $yLogo     = 15;

    if (file_exists($logoPath)) {
        $pdf->Image($logoPath, $xLogo, $yLogo, $imgWidth, 0, '', '', '', true, 300, '', false, false, 0, false, false, false);
    }

    $pdf->SetY($yLogo + 28);

    // Títulos con Titillium
    $pdf->SetFont('titilliumweb', 'B', 16);
    $pdf->Cell(0, 8, $nombre_colegio, 0, 1, 'C');

    $pdf->SetFont('titilliumweb', '', 11);
    $pdf->Cell(0, 6, 'Recibo No. ' . $id_recibo_email, 0, 1, 'C');

    $pdf->Ln(2);

    // -------- HTML principal: estilos + tabla --------
    $html = '
    <style>
      .ctr { text-align:center; }
      .muted { color:#475569; font-size:11px; }
      .tiny { font-size:10px; color:#5a5a5a; }
      .box { border:1px solid #cbd5e1; border-radius:6px; padding:10px; margin-top:8px; }

      .tbl { width:100%; border-collapse:collapse; }
      .tbl th, .tbl td { padding:10px 12px; font-size:12px; border-bottom:1px solid #cbd5e1; }
      .tbl th {
        font-weight:700; text-transform:uppercase; letter-spacing:.3px; text-align:left;
        background-color:#e2e8f0;
      }
      .row-alt { background-color:#f1f5f9; }
      .total-row td { font-weight:700; border-top:1px solid #94a3b8; }
    </style>

    <div class="ctr">
      <div class="muted">Alumno</div>
      <div style="font-weight:700; font-size:13px;">'.htmlspecialchars($alumno, ENT_QUOTES, 'UTF-8').'</div>

      <div style="height:6px;"></div>

      <div class="muted">Fecha de pago</div>
      <div style="font-weight:700; font-size:13px;">'.$fechaHoy.'</div>
    </div>

    <div style="height:10px;"></div>

    <div class="box">
      <table class="tbl">
        <thead>
          <tr>
            <th>Resumen</th>
            <th style="text-align:right;">Importe</th>
          </tr>
        </thead>
        <tbody>
          '.$rowsHtml.'
        </tbody>
      </table>
    </div>

    <div style="height:12px;"></div>

    <div class="ctr muted">¡Gracias por su pago!</div>
    ';

    $pdf->writeHTML($html, true, false, true, false, '');

    // Guardar archivo
    $nombre_archivo = 'reciboNo' . $id_recibo_email . '.pdf';
    $ruta_guardado  = $carpeta . '/' . $nombre_archivo;
    $pdf->Output($ruta_guardado, 'F');

} catch (Exception $e) {
    wa_log('ERROR TCPDF: '.$e->getMessage());
    echo "mal";
    exit;
}

// =============== WHATSAPP CLOUD API ===================================
$ACCESS_TOKEN    = 'EAAOFchLkvzUBP5BNN3bI9UGiakqG5WO1nmLxNGcySMiWCdZAfmhBefELRTPggo9ZCU7jd1qH1BuMF8ylZC36ShMdtEErR2eWQj7NtzotaegIafXwmQxgMYRMBmTx6VuWZCAxkBOOqRN43KUn3psi54kfNZBJagcN4sKTDHS4goKepUz560Lf4XactNVoL0bTJzwZDZD';      // <-- token permanente (System User)
$PHONE_NUMBER_ID = '891544247366984';      // <-- phone number id de tu WABA

// Normalizar teléfono a E.164 sin '+'
$to = normalize_phone_e164_no_plus($telefono_alumno);

// Asegurar prefijo de país Nicaragua (505)
if ($to !== '' && strpos($to, '505') !== 0) {
    // Si el usuario puso solo los 8 dígitos locales (o cualquier variante sin 505), anteponer 505
    $to = '505' . $to;
}

// Validación mínima
if (strlen($to) < 8) {
    wa_log("Telefono inválido. original='{$telefono_alumno}' | normalizado='{$to}'");
    echo "mal";
    exit;
}

// 1) Subir media
$uploadUrl     = "https://graph.facebook.com/v23.0/{$PHONE_NUMBER_ID}/media";
$uploadHeaders = ["Authorization: Bearer {$ACCESS_TOKEN}"];

$upload = wa_curl_upload($uploadUrl, $uploadHeaders, $ruta_guardado);
wa_log("UPLOAD HTTP {$upload['http_code']} | body={$upload['body']} | errno={$upload['errno']} | error={$upload['error']}");

$uploadData = json_decode($upload['body'], true);
$mediaId    = $uploadData['id'] ?? null;

if (!$mediaId) {
    wa_log("UPLOAD FAILED. Response: ".$upload['body']);
    echo "mal";
    exit;
}

// 2) Enviar documento por media id
$sendUrl     = "https://graph.facebook.com/v23.0/{$PHONE_NUMBER_ID}/messages";
$sendHeaders = [
    "Authorization: Bearer {$ACCESS_TOKEN}",
    "Content-Type: application/json"
];

$filename = 'Recibo No '.$id_recibo_email.'.pdf';
$payload  = [
    'messaging_product' => 'whatsapp',
    'to'                => $to,
    'type'              => 'document',
    'document'          => [
        'id'       => $mediaId,
        'filename' => $filename,
        'caption'  => 'Recibo #'.$id_recibo_email.' - '.$nombre_colegio
    ]
];

$send = wa_curl_json($sendUrl, $sendHeaders, $payload);
wa_log("SEND HTTP {$send['http_code']} | body={$send['body']} | errno={$send['errno']} | error={$send['error']}");

$sendData = json_decode($send['body'], true);

// Éxito típico: {"messages":[{"id":"wamid.HBg..."}]}
if (isset($sendData['messages'][0]['id'])) {
    echo "bien";
    exit;
}

// Si llega aquí, algo falló.
wa_log("SEND FAILED. Response: ".$send['body']);
echo "mal";
exit;

/* ======== OPCIONAL: abrir ventana con plantilla si obtienes 131021 ========
$templatePayload = [
  'messaging_product' => 'whatsapp',
  'to' => $to,
  'type' => 'template',
  'template' => [
     'name' => 'plantilla_recibo',
     'language' => ['code' => 'es'],
  ]
];
$t = wa_curl_json($sendUrl, $sendHeaders, $templatePayload);
wa_log("TEMPLATE HTTP {$t['http_code']} | body={$t['body']}");
 // luego reintenta el envío del documento
*/
