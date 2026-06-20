<?php
		session_start();
		include_once '../includes/config.php';
		include_once '../includes/security.php';
        
        use PHPMailer\PHPMailer\PHPMailer;
        use PHPMailer\PHPMailer\SMTP;
        use PHPMailer\PHPMailer\Exception;

        require '../PHPMailer/src/PHPMailer.php';
        require '../PHPMailer/src/SMTP.php';
        require '../PHPMailer/src/Exception.php';

		if (!empty($_POST['asunto']) &&
            !empty($_POST['correo_alumno']) &&
            !empty($_POST['id_alumno_mail']) &&
            !empty($_POST['mensaje'])) {
			
			// guardo los datos
			$asunto = clean(mysqli_real_escape_string($link,$_POST['asunto']));
            $correo_alumno = clean(mysqli_real_escape_string($link,$_POST['correo_alumno']));
            $id_alumno_mail = clean(mysqli_real_escape_string($link,$_POST['id_alumno_mail']));
            $mensaje = clean(mysqli_real_escape_string($link,$_POST['mensaje']));

            $queryAlumno = mysqli_query($link, "SELECT * FROM tbl_alumnos WHERE IDALUMNO = '$id_alumno_mail'");
            $rowAlumno = mysqli_fetch_array($queryAlumno);

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

            try {
                // Configuración del servidor SMTP
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
            
                // Configuración de correo
                $mail->setFrom($rowEmail['username'], $nombre_colegio);
                $mail->addAddress($correo_alumno, $rowAlumno['NOMBREAPELLIDO']);
                $mail->isHTML(true);
            
                // Contenido del correo
                $colegio = htmlspecialchars($nombre_colegio ?? 'Colegio', ENT_QUOTES, 'UTF-8');
                $alumno  = htmlspecialchars($rowAlumno['NOMBREAPELLIDO'] ?? '', ENT_QUOTES, 'UTF-8');
                $titulo  = htmlspecialchars($asunto ?? 'Aviso', ENT_QUOTES, 'UTF-8');
                $fecha   = date('d/m/Y \a\ \l\a\s H:i'); // 31/10/2025 a las 16:23
                $mensajeHtml = nl2br(htmlspecialchars($mensaje ?? '', ENT_QUOTES, 'UTF-8'));

                $mail->Subject = $asunto;
                $mail->Body = '
                <div style="margin:0;padding:24px;background:#f5f7fb;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#111;">
                <div style="max-width:640px;margin:0 auto;">

                    <!-- Logo -->
                    <div style="text-align:center;margin-bottom:16px;">
                    <img src="cid:logo_colegio" alt="Logo" style="width:64px;height:64px;border-radius:12px;display:inline-block;">
                    </div>

                    <!-- Card -->
                    <div style="background:#ffffff;border:1px solid #e9eef5;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.06);padding:24px;">
                    <h2 style="margin:0 0 8px;font-size:24px;line-height:1.35;font-weight:800;text-align:center;">'.$titulo.'</h2>

                    <p style="margin:0 0 16px;font-size:13px;color:#667085;text-align:center;">
                        '.$colegio.' &middot; '.$fecha.'
                    </p>

                    <div style="font-weight:700;margin-bottom:6px;">Mensaje</div>
                    <div style="font-size:14px;line-height:1.6;color:#111;margin:0 0 4px;">
                        '.$mensajeHtml.'
                    </div>

                    '.($alumno !== '' ? '<p style="margin-top:16px;font-size:13px;color:#667085;"><strong>Alumno:</strong> '.$alumno.'</p>' : '').'
                    </div>

                    <!-- Footer -->
                    <div style="text-align:center;margin:22px 0 0;">
                    <div style="font-size:22px;font-weight:800;letter-spacing:.2px;">'.$colegio.'</div>
                    <div style="font-size:12px;color:#667085;margin-top:8px;">
                    </div>
                    </div>

                </div>
                </div>';
            
                // Envía el correo
                $mail->send();
                echo 'bien';
            } catch (Exception $e) {
                echo 'Error al enviar el correo: ', $mail->ErrorInfo;
            }
		}else{
			echo "mal";
		}
			
		
?>