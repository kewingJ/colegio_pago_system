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

        date_default_timezone_set('America/Managua');

        $ano_actual = date('Y');
        $mes_actual = date('m');

        // enviar correo solo los 11 de cada mes
		if (date('d') == $dia_mes) {
            $query = mysqli_query($link,"SELECT b.ID AS IdInscripcion,
                                c.NOMBREAPELLIDO, c.EMAIL, c.IDALUMNO
                                FROM tbl_matricula AS a
                                INNER JOIN tbl_inscripcion AS b
                                ON a.ID = b.IDMATRICULA
                                INNER JOIN tbl_alumnos AS c
                                ON a.IDALUMNO = c.IDALUMNO
                                WHERE a.ANIO = '$ano_actual'");
            $total_alumnos_mora = 0;
            $contador = 0;
            $datos_alumnos_mora = [];
            while($rowDatos = mysqli_fetch_array($query))
            {
                $IdInscripcion = $rowDatos['IdInscripcion'];
                $nombre = $rowDatos['NOMBREAPELLIDO'];
                $email = $rowDatos['EMAIL'];
                $idAlumno = $rowDatos['IDALUMNO'];

                // procesar si el alumno tiene correo asociado
                if(!empty($email))
                {
                    $queryDos = mysqli_query($link,"SELECT * FROM tbl_pagosmensualidades
                                    WHERE IdInscripcion = '$IdInscripcion'");
                    $rowDatosDos = mysqli_fetch_array($queryDos);
                    $total_mes_mora = 0;
                    $meses_mora = '';
                    for ($i = 1; $i <= $mes_actual; $i++) {
                        $mes_abbr = match($i) {
                            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun',
                            7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
                        };
                        $mes_completo = match($i) {
                            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
                            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                        };

                        if(($rowDatosDos[$mes_abbr] ?? '') != 'X'){
                            $total_mes_mora++;
                            $meses_mora .= $mes_completo.", ";
                        }
                    }

                    if($total_mes_mora > 0){
                        $total_alumnos_mora++;
                        $datos_alumnos_mora[$contador] = array(
                            "id_alumno"  => $idAlumno,
                            "nombre"     => mb_convert_case(mb_strtolower($nombre), MB_CASE_TITLE, "UTF-8"),
                            "email"      => $email,
                            "total_mes"  => $total_mes_mora,
                            "meses_mora" => rtrim($meses_mora, ", ")
                        );
                        $contador++;
                    }
                }
            }

            //obtener informacion del colegio
			$queryColegio = mysqli_query($link, "SELECT * FROM tbl_parametros");
            $rowColegio = mysqli_fetch_array($queryColegio);
			$nombre_colegio = $rowColegio['NOMBRECOLEGIO'] ?? 'Colegio';
            // Logo
			if(empty($rowColegio['logo_colegio'])){
				$logoPath = __DIR__ . '/../img/Logo-SYSCPE.png';
			} else {
				$logoPath = __DIR__ . '/../'.$rowColegio['logo_colegio'];
			}

            // Obtener configuración de email una sola vez (Optimización)
            $queryEmail = mysqli_query($link, "SELECT * FROM tbla_email LIMIT 1");
            $rowEmail = mysqli_fetch_array($queryEmail);

            // enviar mensaje email
            if($total_alumnos_mora > 0 && $rowEmail){
                foreach ($datos_alumnos_mora as $item) {

                    $mail = new PHPMailer(true);

                    try {
                        $mail->isSMTP();
                        $mail->CharSet = 'UTF-8';
                        $mail->Host = $rowEmail['host'];
                        $mail->SMTPAuth = true;
                        $mail->Username = $rowEmail['username'];
                        $mail->Password = $rowEmail['password'];
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = $rowEmail['port'];

                        if (file_exists($logoPath)) {
                            $mail->addEmbeddedImage($logoPath, 'logo_colegio', 'logo.png');
                        }

                        $mail->setFrom($rowEmail['username'], $nombre_colegio);
                        $mail->addAddress($item['email'], $item['nombre']);
                        $mail->isHTML(true);

                        $colegio   = htmlspecialchars($nombre_colegio, ENT_QUOTES, 'UTF-8');
                        $destName  = htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8');
                        $mesesN    = (int)$item['total_mes'];
                        $mesLabel  = ($mesesN === 1) ? 'mes' : 'meses';
                        $listaMes  = htmlspecialchars($item['meses_mora'] ?? '', ENT_QUOTES, 'UTF-8');
                        $hoy       = date('d/m/Y \a\ \l\a\s H:i');
                        $titulo    = htmlspecialchars($rowEmail['subject'] ?: 'Aviso de mensualidad pendiente', ENT_QUOTES, 'UTF-8');

                        $mail->Subject = $titulo;
                        $bodyHtml = '
                        <div style="margin:0;padding:24px;background:#f5f7fb;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#111;">
                            <div style="max-width:640px;margin:0 auto;">
                                <div style="text-align:center;margin-bottom:16px;">
                                    <img src="cid:logo_colegio" alt="Logo" style="width:64px;height:64px;border-radius:12px;display:inline-block;">
                                </div>
                                <div style="background:#ffffff;border:1px solid #e9eef5;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.06);padding:24px;">
                                    <h2 style="margin:0 0 8px;font-size:24px;line-height:1.35;font-weight:800;text-align:center;">'.$titulo.'</h2>
                                    <p style="margin:0 0 16px;font-size:13px;color:#667085;text-align:center;">'.$colegio.' &middot; '.$hoy.'</p>
                                    <div style="font-weight:700;margin-bottom:6px;">Estimado(a) padre/madre de familia</div>
                                    <div style="font-size:14px;line-height:1.6;color:#111;margin:0 0 4px;">
                                        Le informamos que el estudiante <strong>'.$destName.'</strong> registra <strong>'.$mesesN.' '.$mesLabel.'</strong> de mora en el pago de su mensualidad.
                                        '.($listaMes !== '' ? '<br>Meses en mora: <strong>'.$listaMes.'</strong>.' : '').'
                                    </div>
                                    <p style="font-size:14px;line-height:1.6;color:#111;margin:12px 0 0;">
                                        Agradecemos realizar el pago a la brevedad o comunicarse con administración para cualquier gestión.
                                    </p>
                                </div>
                                <div style="text-align:center;margin:22px 0 0;">
                                    <div style="font-size:22px;font-weight:800;letter-spacing:.2px;">'.$colegio.'</div>
                                    <div style="font-size:12px;color:#667085;margin-top:8px;">
                                        Este es un recordatorio automático. Si ya realizó el pago, por favor ignore este mensaje.
                                    </div>
                                </div>
                            </div>
                        </div>';
                        $mail->Body = $bodyHtml;
                        $mail->AltBody = "Aviso de mensualidad pendiente - $colegio\nAlumno: $destName\nEn mora: $mesesN $mesLabel".($listaMes ? " ($listaMes)" : "")."\nFecha: $hoy";

                        $mail->send();

                        // Registrar en Bitácora
                        $stmtLog = mysqli_prepare($link, "INSERT INTO tbl_log_emails (destinatario, asunto, cuerpo, estado, id_usuario, id_alumno) VALUES (?, ?, ?, 'Enviado', ?, ?)");
                        $id_usr = $_SESSION['id_usuario'] ?? 0;
                        mysqli_stmt_bind_param($stmtLog, "sssii", $item['email'], $titulo, $bodyHtml, $id_usr, $item['id_alumno']);
                        mysqli_stmt_execute($stmtLog);

                        echo "bien";
                        sleep(2);
                    } catch (Exception $e) {
                        $stmtLog = mysqli_prepare($link, "INSERT INTO tbl_log_emails (destinatario, asunto, cuerpo, estado, id_usuario, id_alumno) VALUES (?, ?, ?, 'Error', ?, ?)");
                        $id_usr = $_SESSION['id_usuario'] ?? 0;
                        $errorMsg = "Error: " . $mail->ErrorInfo;
                        mysqli_stmt_bind_param($stmtLog, "sssii", $item['email'], $titulo, $errorMsg, $id_usr, $item['id_alumno']);
                        mysqli_stmt_execute($stmtLog);
                        echo "mal";
                    }
                }
            }
		} else {
            echo "No es el dia para enviar correos";
        }
?>