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

    if (!empty($_POST['id_recibo_email']) &&
        !empty($_POST['contenidoRecibo']) &&
        !empty($_POST['id_alumno_email'])) {

        $contenidoRecibo = $_POST['contenidoRecibo'];
        $email_alumno    = clean(mysqli_real_escape_string($link,$_POST['email_alumno']));
        $id_recibo_email = clean(mysqli_real_escape_string($link,$_POST['id_recibo_email']));
        $id_alumno_email = clean(mysqli_real_escape_string($link,$_POST['id_alumno_email']));

        // ====== PARSEAR contenidoRecibo (HTML del ticket) ======
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

        // --------- Render de filas para EMAIL (se deja como estaba) ---------
        $rowsHtml = '';
        foreach ($detalles as [$concepto, $valor]) {
            if (is_numeric($valor)) {
                $valorOut = $prefijo.' '.number_format((float)$valor, 2, '.', ',');
            } else {
                $valorOut = htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
            }
            $rowsHtml .= '
            <tr>
                <td style="padding:12px 16px;">'.htmlspecialchars($concepto, ENT_QUOTES, 'UTF-8').'</td>
                <td style="padding:12px 16px;text-align:right;">'.$valorOut.'</td>
            </tr>';
        }
        if ($montoTotal !== null) {
            $rowsHtml .= '
            <tr>
                <td style="padding:12px 16px;font-weight:700;border-top:1px solid #eef0f4;">Total pagado</td>
                <td style="padding:12px 16px;text-align:right;font-weight:700;border-top:1px solid #eef0f4;">'.$amount.'</td>
            </tr>';
        }

        // --------- Render de filas para PDF (igual al WhatsApp) -------------
        $rowsHtmlPdf = '';
        $idxPdf      = 0;
        foreach ($detalles as [$concepto, $valor]) {
            $valorOutPdf = is_numeric($valor)
                ? $prefijo.' '.number_format((float)$valor, 2, '.', ',')
                : htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');

            $trClass = ($idxPdf % 2 === 1) ? ' class="row-alt"' : '';
            $rowsHtmlPdf .= '
              <tr'.$trClass.'>
                <td>'.htmlspecialchars($concepto, ENT_QUOTES, 'UTF-8').'</td>
                <td style="text-align:right;">'.$valorOutPdf.'</td>
              </tr>';
            $idxPdf++;
        }
        if ($montoTotal !== null) {
            $rowsHtmlPdf .= '
              <tr class="total-row">
                <td>Total pagado</td>
                <td style="text-align:right;">'.$amount.'</td>
              </tr>';
        }

        // ========================================================================
        // segundo paso enviar email
        $queryAlumno = mysqli_query($link, "SELECT * FROM tbl_alumnos WHERE IDALUMNO = '$id_alumno_email'");
        $rowAlumno   = mysqli_fetch_array($queryAlumno);

        $queryEmail = mysqli_query($link, "SELECT * FROM tbla_email");
        $rowEmail   = mysqli_fetch_array($queryEmail);

        //obtener informacion del colegio
        $queryColegio   = mysqli_query($link, "SELECT * FROM tbl_parametros");
        $rowColegio     = mysqli_fetch_array($queryColegio);
        $nombre_colegio = $rowColegio['NOMBRECOLEGIO'];

        // Logo
        if (empty($rowColegio['logo_colegio'])) {
            $logoPath = __DIR__ . '/../img/Logo-SYSCPE.png';
        } else {
            $logoPath = __DIR__ . '/../'.$rowColegio['logo_colegio'];
        }

        $alumno  = htmlspecialchars($rowAlumno['NOMBREAPELLIDO'] ?? '', ENT_QUOTES, 'UTF-8');
        $fechaHoy = date('d/m/Y');

        // ===== 1) Crear PDF con mismos estilos que el de WhatsApp =====

        // Asegurar carpeta de recibos (opcional pero seguro)
        $carpetaRecibos = __DIR__ . '/recibos';
        if (!is_dir($carpetaRecibos)) {
            @mkdir($carpetaRecibos, 0775, true);
        }

        // --- Fuentes Titillium Web (Google Fonts) ---
        $pdf_fontsubsetting = true;
        $fontDir = __DIR__ . '/../fonts/';

        // Registrar (si ya existen en caché, devuelve fontkey)
        $fw_reg  = TCPDF_FONTS::addTTFfont($fontDir.'TitilliumWeb-Regular.ttf', 'TrueTypeUnicode', '', 96);
        $fw_ita  = TCPDF_FONTS::addTTFfont($fontDir.'TitilliumWeb-Italic.ttf', 'TrueTypeUnicode', '', 96);
        $fw_bold = TCPDF_FONTS::addTTFfont($fontDir.'TitilliumWeb-Bold.ttf', 'TrueTypeUnicode', '', 96);
        $fw_bita = TCPDF_FONTS::addTTFfont($fontDir.'TitilliumWeb-BoldItalic.ttf', 'TrueTypeUnicode', '', 96);

        $pdf = new TCPDFNetsoluciones('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->setFontSubsetting(true);

        // márgenes cómodos
        $pdf->SetMargins(15, 20, 15);
        $pdf->AddPage();

        // calcular posición X centrada para el logo
        $imgWidth  = 28; // ancho del logo en mm
        $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
        $xLogo     = ($pdf->getMargins()['left']) + ($pageWidth - $imgWidth) / 2;
        $yLogo     = 15;

        if (file_exists($logoPath)) {
            // colocar el logo centrado
            $pdf->Image($logoPath, $xLogo, $yLogo, $imgWidth, 0, '', '', '', true, 300, '', false, false, 0, false, false, false);
        }

        // saltar bajo el logo
        $pdf->SetY($yLogo + 28);

        // Título centrado (nombre del colegio) con Titillium
        $pdf->SetFont('titilliumweb', 'B', 16);
        $pdf->Cell(0, 8, $nombre_colegio, 0, 1, 'C');

        // Subtítulo: Recibo #
        $pdf->SetFont('titilliumweb', '', 11);
        $pdf->Cell(0, 6, 'Recibo No. ' . $id_recibo_email, 0, 1, 'C');

        $pdf->Ln(2);

        // HTML del PDF igual al de WhatsApp (tabla, zebra y frase diseñada por...)
        $htmlHeader = '
        <style>
          .ctr { text-align:center; }
          .muted { color:#475569; font-size:11px; }
          .box { border:1px solid #cbd5e1; border-radius:6px; padding:10px; margin-top:8px; }

          .tbl { width:100%; border-collapse:collapse; }
          .tbl th, .tbl td {
              padding:10px 12px; font-size:12px; border-bottom:1px solid #cbd5e1;
          }
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
              '.$rowsHtmlPdf.'
            </tbody>
          </table>
        </div>

        <div style="height:12px;"></div>

        <div class="ctr muted">¡Gracias por su pago!</div>
        ';

        $pdf->writeHTML($htmlHeader, true, false, true, false, '');

        // Guardar PDF
        $nombre_archivo = 'reciboNo'.$id_recibo_email.'.pdf';
        $ruta_guardado  = $carpetaRecibos . '/' . $nombre_archivo;
        $pdf->Output($ruta_guardado, 'F');

        // ===== 2) Envío del correo con PHPMailer =====
        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor SMTP
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            $mail->isSMTP();
            $mail->CharSet  = 'UTF-8';
            $mail->Host     = $rowEmail['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $rowEmail['username'];
            $mail->Password = $rowEmail['password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port     = $rowEmail['port'];

            // Incrustar logo para el correo
            if (file_exists($logoPath)) {
                $mail->addEmbeddedImage($logoPath, 'logo_colegio', 'logo.png');
            }

            // Configuración de correo
            $mail->setFrom($rowEmail['username'], $nombre_colegio);
            $mail->addAddress($email_alumno, $rowAlumno['NOMBREAPELLIDO']);
            $mail->isHTML(true);

            // Adjuntar el PDF recién generado
            $mail->addAttachment($ruta_guardado, 'Recibo No '.$id_recibo_email.'.pdf');

            $mail->Subject = "Recibo No ".$id_recibo_email;

            $colegio   = htmlspecialchars($nombre_colegio ?? 'Colegio', ENT_QUOTES, 'UTF-8');
            $alumnoTxt = htmlspecialchars($rowAlumno['NOMBREAPELLIDO'] ?? '', ENT_QUOTES, 'UTF-8');
            $numRecibo = (int)$id_recibo_email;

            // Fecha en español (simple y compatible con correos)
            $fechaHoy = date('d/m/Y');

            $mail->Body = '
            <div style="margin:0;padding:24px;background:#f5f7fb;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#111;">
              <div style="max-width:600px;margin:0 auto;">

                <!-- Logo -->
                <div style="text-align:center;margin-bottom:16px;">
                  <img src="cid:logo_colegio" alt="Logo" style="width:64px;height:64px;border-radius:12px;display:inline-block;">
                </div>

                <!-- Card -->
                <div style="background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.06);padding:24px;border:1px solid #eef0f4;">
                  <h2 style="margin:0 0 6px;font-size:22px;line-height:1.35;font-weight:800;text-align:center;">
                    Recibo de '.$colegio.'
                  </h2>
                  <p style="margin:0 0 16px;font-size:13px;color:#667085;text-align:center;">
                    Recibo <strong>#'.$numRecibo.'</strong><br>
                    Alumno: <strong>'.$alumnoTxt.'</strong>
                  </p>

                  <!-- 2 columnas: monto y fecha -->
                  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin:8px 0 16px;">
                    <tr>
                      <td style="width:50%;vertical-align:top;padding:8px;text-align:left;">
                        <div style="font-size:11px;color:#8a8f98;letter-spacing:.3px;text-transform:uppercase;margin-bottom:4px;text-align:left;">Monto pagado</div>
                        <div style="font-size:16px;font-weight:700;text-align:left;">'.$amount.'</div>
                      </td>
                      <td style="width:50%;vertical-align:top;padding:8px;text-align:end;">
                        <div style="font-size:11px;color:#8a8f98;letter-spacing:.3px;text-transform:uppercase;margin-bottom:4px;text-align:end;">Fecha de pago</div>
                        <div style="font-size:16px;font-weight:700;text-align:end;">'.$fechaHoy.'</div>
                      </td>
                    </tr>
                  </table>

                  <!-- Resumen (líneas del recibo) -->
                  <div style="font-size:11px;color:#8a8f98;letter-spacing:.3px;text-transform:uppercase;margin:6px 0;">Resumen</div>
                  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #eef0f4;border-radius:8px;overflow:hidden;">
                    '.$rowsHtml.'
                  </table>

                  <div style="border-top:1px solid #eef0f4;margin:18px 0 0;padding-top:14px;font-size:13px;color:#667085;text-align:left;">
                    Se adjunta el PDF del <strong>Recibo #'.$numRecibo.'</strong> para tu control.
                  </div>
                </div>
              </div>
            </div>
            ';

            $mail->AltBody =
                "Recibo de $colegio\n".
                "Recibo #$numRecibo\n".
                "Alumno: $alumnoTxt\n".
                "Monto pagado: $amount\n".
                "Fecha de pago: $fechaHoy\n".
                "Se adjunta el PDF del recibo con el detalle.";

            // Envía el correo
            $mail->send();
            echo "bien";
        } catch (Exception $e) {
            // echo 'Error al enviar el correo: ', $mail->ErrorInfo;
            echo "mal";
        }
    } else {
        echo "mal";
    }
