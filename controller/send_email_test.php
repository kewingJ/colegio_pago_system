<?php
		session_start();
		include_once '../includes/config.php';
		include_once '../includes/security.php';
        include_once '../tcpdf/tcpdf.php';
		include_once '../PHPMailer/src/PHPMailer.php';
		include_once '../PHPMailer/src/SMTP.php';
		include_once '../PHPMailer/src/Exception.php';

		use PHPMailer\PHPMailer\PHPMailer;
		use PHPMailer\PHPMailer\SMTP;
		use PHPMailer\PHPMailer\Exception;

		if (!empty($_POST['email_nuevo'])) {
				$email_nuevo = clean(mysqli_real_escape_string($link,$_POST['email_nuevo']));

				$queryEmail = mysqli_query($link, "SELECT * FROM tbla_email");
            	$rowEmail = mysqli_fetch_array($queryEmail);

                //obtener informacion del colegio
				$queryColegio = mysqli_query($link, "SELECT * FROM tbl_parametros");
            	$rowColegio = mysqli_fetch_array($queryColegio);
				$nombre_colegio = $rowColegio['NOMBRECOLEGIO'];
				
				$mail = new PHPMailer(true);
	
				try {
					// Configuración del servidor SMTP
					// $mail->SMTPDebug = SMTP::DEBUG_SERVER;
					$mail->isSMTP();
					$mail->Host = $rowEmail['host'];
					$mail->SMTPAuth = true;
					$mail->Username = $rowEmail['username'];
					$mail->Password = $rowEmail['password'];
					$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
					$mail->Port = $rowEmail['port'];

                    // Incrustar logo (se usará como cid:logo_colegio en el HTML)
                    $logoPath = __DIR__ . '/../img/Logo-SYSCPE.png'; // ajusta la ruta si es otra
                    if (file_exists($logoPath)) {
                        $mail->addEmbeddedImage($logoPath, 'logo_colegio', 'logo.png');
                    }
				
					// Configuración de correo
					$mail->setFrom($rowEmail['username'], $nombre_colegio);
					$mail->addAddress($email_nuevo, 'Testing');
					$mail->isHTML(true);
				
					// Contenido del correo
					$mail->Subject = $rowEmail['subject'];

                    $fechaAhora = date('d/m/Y \a\ \l\a\s H:i');
                    $nombreColegioSafe = htmlspecialchars($nombre_colegio ?? 'Colegio', ENT_QUOTES, 'UTF-8');
                    $mensajeSafe = nl2br(htmlspecialchars($rowEmail['mensaje'] ?? '', ENT_QUOTES, 'UTF-8'));

                    // HTML tipo “card” (similar a la imagen)
                    $mail->Body = '
                    <div style="margin:0;padding:24px;background:#f5f7fb;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#111;">
                    <div style="max-width:560px;margin:0 auto;">

                        <!-- Logo -->
                        <div style="text-align:center;margin-bottom:16px;">
                        <img src="cid:logo_colegio" alt="Logo" style="width:64px;height:64px;border-radius:12px;display:inline-block;">
                        </div>

                        <!-- Tarjeta -->
                        <div style="background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.06);padding:24px;border:1px solid #eef0f4;">
                        <h2 style="margin:0 0 6px;font-size:22px;line-height:1.35;font-weight:800;text-align:center;">' . htmlspecialchars($rowEmail["subject"] ?? "Notificación", ENT_QUOTES, "UTF-8") . '</h2>

                        <p style="margin:0 0 16px;font-size:13px;color:#667085;text-align:center;">
                            Enviado el ' . $fechaAhora . '
                        </p>

                        <div style="height:1px;background:#eef0f4;margin:12px 0 16px;"></div>

                        <p style="margin:0 0 6px;font-weight:700;">Mensaje</p>
                        <div style="font-size:14px;line-height:1.6;margin:0 0 8px;">' . $mensajeSafe . '</div>

                        <p style="font-size:13px;color:#667085;margin:12px 0 0;">
                            (Este es un envío de prueba para: <strong>' . htmlspecialchars($email_nuevo, ENT_QUOTES, "UTF-8") . '</strong>)
                        </p>
                        </div>

                        <!-- Pie -->
                        <div style="text-align:center;margin-top:22px;">
                        <div style="font-size:20px;font-weight:800;letter-spacing:.2px;margin-bottom:6px;">' . $nombreColegioSafe . '</div>
                        <div style="font-size:12px;color:#8a8f98;">Enviado desde el sistema</div>
                        </div>

                    </div>
                    </div>
                    ';

                    // Texto plano (fallback)
                    $mail->AltBody =
                        ($rowEmail["subject"] ?? "Notificación") . PHP_EOL .
                        "Enviado el: " . $fechaAhora . PHP_EOL .
                        "Mensaje:" . PHP_EOL .
                        ($rowEmail["mensaje"] ?? "") . PHP_EOL .
                        "Este es un envío de prueba para: " . $email_nuevo;
				
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