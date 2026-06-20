<?php
	session_start();
    $nombre     = $_SESSION['nombre'];
    $apellido   = $_SESSION['apellido'];

	include_once '../includes/config.php';
	include_once '../includes/security.php';
    date_default_timezone_set('America/Managua');

	if (!empty($_POST['id_anio']) &&
        !empty($_POST['id_alumno']) &&
        !empty($_POST['id_nivel']) &&
        !empty($_POST['id_grado']) &&
        !empty($_POST['id_seccion'])) {
			
		//
		$id_alumno = clean(mysqli_real_escape_string($link,$_POST['id_alumno']));
        $id_anio = clean(mysqli_real_escape_string($link,$_POST['id_anio']));
        $id_nivel = clean(mysqli_real_escape_string($link,$_POST['id_nivel']));
        $id_grado = clean(mysqli_real_escape_string($link,$_POST['id_grado']));
        $id_seccion = clean(mysqli_real_escape_string($link,$_POST['id_seccion']));
        $email = clean(mysqli_real_escape_string($link,$_POST['email']));

        // obtener el id de la matricula
        $queryDato = mysqli_query($link,"SELECT * FROM tbl_matricula
                                    WHERE ANIO = '$id_anio'
                                    AND IDALUMNO = '$id_alumno'");
		$rowData = mysqli_fetch_array($queryDato);
        $id_matricula = $rowData['ID'];

        // verificar si el alumno ya esta inscrito en el año lectivo seleccionado
        $queryConsulta = mysqli_query($link, "SELECT * FROM tbl_inscripcion 
                                    WHERE ANIOLECTIVO = '$id_anio' 
                                    AND IDMATRICULA = '$id_matricula'");
        $bandera = mysqli_num_rows($queryConsulta);

        if($bandera > 0){
            echo 'Esta Inscripcion ya fue efectuada';
        } else {
            // actualizar email de alumno
            if(!empty($email)){
                $queryUpdateAlumno = mysqli_query($link,"UPDATE tbl_alumnos SET EMAIL = '$email' WHERE IDALUMNO = '$id_alumno'") or die(mysqli_error($link));
            }

            // guardar nueva inscripcion
            $queryInscripcion = mysqli_query($link,"INSERT INTO tbl_inscripcion VALUES (0,'$id_matricula', '$id_alumno', '$id_nivel', '$id_grado', '$id_seccion', '$id_anio', NULL, NULL, 1)") or die(mysqli_error($link));
            $id_inscripcion = mysqli_insert_id($link);

            // guardar historial de pago
            $queryPago = mysqli_query($link,"INSERT INTO tbl_pagosmensualidades VALUES (0,'$id_inscripcion', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '$id_anio')") or die(mysqli_error($link));
            $id_pagoMensuales = mysqli_insert_id($link);

            // actualizar historial de pago si tienes pagos efectuados
            $queryConsultaDos = mysqli_query($link, "SELECT * FROM tbl_cargos AS ca 
                                    INNER JOIN tbl_conceptospago cp ON cp.IdConcepto = ca.IdConcepto
                                    INNER JOIN tbl_categoriapago cpa ON cp.IdCategoria = cpa.Id
                                    WHERE ca.IdAlumno  = '$id_alumno' 
                                    AND ca.AnioLectivo = '$id_anio'
                                    AND cpa.Concepto = 'MENSUALIDAD'");

            while($rowConsultaDos = mysqli_fetch_array($queryConsultaDos)){
                $mes_referencia = $rowConsultaDos['MesReferencia'];
                $fecha = new DateTime($mes_referencia);
                // Obtener el número del mes
                $numeroMes = (int)$fecha->format('m');
                $mes = "";
                switch ($numeroMes) {
                    case 1:
                        $mes = "Ene";
                        break;
                    case 2:
                        $mes = "Feb";
                        break;
                    case 3:
                        $mes = "Mar";
                        break;
                    case 4:
                        $mes = "Abr";
                        break;
                    case 5:
                        $mes = "May";
                        break;
                    case 6:
                        $mes = "Jun";
                        break;
                    case 7:
                        $mes = "Jul";
                        break;
                    case 8:
                        $mes = "Ago";
                        break;
                    case 9:
                        $mes = "Sep";
                        break;
                    case 10:
                        $mes = "Oct";
                        break;
                    case 11:
                        $mes = "Nov";
                        break;
                    case 12:
                        $mes = "Dic";
                        break;
                }

                // 
                $queryUpdatePago = mysqli_query($link,"UPDATE tbl_pagosmensualidades SET $mes = 'X' WHERE Anio = '$id_anio' AND Id = '$id_pagoMensuales'") or die(mysqli_error($link));
            }

            echo 'bien';
        }
	} else {
		echo "Algunos datos estan vacios";
	}	
?>