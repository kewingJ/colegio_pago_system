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
                        switch ($i) {
                            case 1:
                                $mes = "Ene";
                                $mes_completo = "Enero";
                                break;
                            case 2:
                                $mes = "Feb";
                                $mes_completo = "Febrero";
                                break;
                            case 3:
                                $mes = "Mar";
                                $mes_completo = "Marzo";
                                break;
                            case 4:
                                $mes = "Abr";
                                $mes_completo = "Abril";
                                break;
                            case 5:
                                $mes = "May";
                                $mes_completo = "Mayo";
                                break;
                            case 6:
                                $mes = "Jun";
                                $mes_completo = "Junio";
                                break;
                            case 7:
                                $mes = "Jul";
                                $mes_completo = "Julio";
                                break;
                            case 8:
                                $mes = "Ago";
                                $mes_completo = "Agosto";
                                break;
                            case 9:
                                $mes = "Sep";
                                $mes_completo = "Septiembre";
                                break;
                            case 10:
                                $mes = "Oct";
                                $mes_completo = "Octubre";
                                break;
                            case 11:
                                $mes = "Nov";
                                $mes_completo = "Noviembre";
                                break;
                            case 12:
                                $mes = "Dic";
                                $mes_completo = "Diciembre";
                                break;
                        }

                        if($rowDatosDos[$mes] != 'X'){
                            $total_mes_mora++;
                            $meses_mora .= $mes_completo.", ";
                        }
                    }

                    if($total_mes_mora > 0){
                        $total_alumnos_mora++;
                        $datos_alumnos_mora[$contador] = array(
                            "id_alumno"  => $idAlumno,
                            "nombre"     => $resultado = mb_convert_case(mb_strtolower($nombre), MB_CASE_TITLE, "UTF-8"),
                            "email"      => $email,
                            "total_mes"  => $total_mes_mora,
                            "meses_mora" => $meses_mora  // <-- NUEVO (e.g. "EneFebMar")
                        );
                        $contador++;
                        // echo $email.' - '.$total_alumnos_mora.' - '.$nombre.' - '.$IdInscripcion.' - '.$total_mes_mora.' - '.$meses_mora.'<br>';
                    }
                }
            }

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

            // enviar mensaje email
            if($total_alumnos_mora > 0){
                foreach ($datos_alumnos_mora as $item) {
                    $mes = "";
                    if($item['total_mes'] > 1){
                        $mes = "meses";
                    } else {
                        $mes = "mes";
                    }

                    $queryEmail = mysqli_query($link, "SELECT * FROM tbla_email");
                    $rowEmail = mysqli_fetch_array($queryEmail);
                        
                    $mail = new PHPMailer(true);

                    try {
                        // Configuración del servidor SMTP
                        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
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
                        $mail->addAddress($item['email'], $item['nombre']);
                        $mail->isHTML(true);

                        $colegio   = htmlspecialchars($nombre_colegio, ENT_QUOTES, 'UTF-8');
                        $destName  = htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8');
                        $mesesN    = (int)$item['total_mes'];
                        $mesLabel  = ($mesesN === 1) ? 'mes' : 'meses';
                        $listaMes  = htmlspecialchars($item['meses_mora'] ?? '', ENT_QUOTES, 'UTF-8');
                        $hoy       = date('d/m/Y \a\ \l\a\s H:i');
                        $titulo    = htmlspecialchars($rowEmail['subject'] ?: 'Aviso de mensualidad pendiente', ENT_QUOTES, 'UTF-8');

                        // Contenido del correo
                        $mail->Subject = $titulo;
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

                                <!-- Footer -->
                                <div style="text-align:center;margin:22px 0 0;">
                                    <div style="font-size:22px;font-weight:800;letter-spacing:.2px;">'.$colegio.'</div>
                                    <div style="font-size:12px;color:#667085;margin-top:8px;">
                                        Este es un recordatorio automático. Si ya realizó el pago, por favor ignore este mensaje.
                                    </div>
                                </div>

                            </div>
                        </div>';

                        $mail->AltBody =
                        "Aviso de mensualidad pendiente - $colegio\n".
                        "Alumno: $destName\n".
                        "En mora: $mesesN $mesLabel".($listaMes ? " ($listaMes)" : "")."\n".
                        "Fecha: $hoy\n".
                        "Si ya realizó el pago, ignore este mensaje.";
                        
                        // Envía el correo
                        $mail->send();
                        echo "bien";
                        sleep(5);
                    } catch (Exception $e) {
                        // echo 'Error al enviar el correo: ', $mail->ErrorInfo;
                        echo "mal";
                    }
                }
            }
		} else {
            echo "No es el dia para enviar correos";
        }