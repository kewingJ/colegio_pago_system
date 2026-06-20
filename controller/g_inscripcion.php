<?php
	session_start();
	include_once '../includes/config.php';
	include_once '../includes/security.php';
    date_default_timezone_set('America/Managua');

	if (!empty($_POST['id_anio']) &&
        !empty($_POST['id_alumno']) &&
        !empty($_POST['id_nivel']) &&
        !empty($_POST['id_grado']) &&
        !empty($_POST['id_seccion'])) {

		$id_alumno = clean($_POST['id_alumno']);
        $id_anio = clean($_POST['id_anio']);
        $id_nivel = clean($_POST['id_nivel']);
        $id_grado = clean($_POST['id_grado']);
        $id_seccion = clean($_POST['id_seccion']);
        $email = clean($_POST['email']);

        // obtener el id de la matricula con sentencia preparada
        $stmtMat = mysqli_prepare($link, "SELECT ID FROM tbl_matricula WHERE ANIO = ? AND IDALUMNO = ?");
        mysqli_stmt_bind_param($stmtMat, "si", $id_anio, $id_alumno);
        mysqli_stmt_execute($stmtMat);
        $resMat = mysqli_stmt_get_result($stmtMat);
		$rowData = mysqli_fetch_array($resMat);

        if (!$rowData) {
            echo 'El alumno no tiene matrícula para el año seleccionado';
            exit;
        }
        $id_matricula = $rowData['ID'];

        // verificar si el alumno ya esta inscrito
        $stmtInsCheck = mysqli_prepare($link, "SELECT ID FROM tbl_inscripcion WHERE ANIOLECTIVO = ? AND IDMATRICULA = ?");
        mysqli_stmt_bind_param($stmtInsCheck, "si", $id_anio, $id_matricula);
        mysqli_stmt_execute($stmtInsCheck);
        $resInsCheck = mysqli_stmt_get_result($stmtInsCheck);

        if(mysqli_num_rows($resInsCheck) > 0){
            echo 'Esta Inscripcion ya fue efectuada para este año lectivo';
        } else {
            // actualizar email de alumno
            if(!empty($email)){
                $stmtUpdE = mysqli_prepare($link, "UPDATE tbl_alumnos SET EMAIL = ? WHERE IDALUMNO = ?");
                mysqli_stmt_bind_param($stmtUpdE, "si", $email, $id_alumno);
                mysqli_stmt_execute($stmtUpdE);
            }

            // guardar nueva inscripcion
            $stmtIns = mysqli_prepare($link, "INSERT INTO tbl_inscripcion (IDMATRICULA, IDALUMNO, IDNIVEL, IDGRADO, SECCION, ANIOLECTIVO, IDTURNO) VALUES (?, ?, ?, ?, ?, ?, 1)");
            mysqli_stmt_bind_param($stmtIns, "iiiiss", $id_matricula, $id_alumno, $id_nivel, $id_grado, $id_seccion, $id_anio);
            mysqli_stmt_execute($stmtIns);
            $id_inscripcion = mysqli_insert_id($link);

            // guardar historial de pago
            $stmtPago = mysqli_prepare($link, "INSERT INTO tbl_pagosmensualidades (IdInscripcion, Anio) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmtPago, "is", $id_inscripcion, $id_anio);
            mysqli_stmt_execute($stmtPago);
            $id_pagoMensuales = mysqli_insert_id($link);

            // Sincronizar pagos si ya existen cargos pagados
            $stmtCargos = mysqli_prepare($link, "SELECT ca.MesReferencia FROM tbl_cargos AS ca
                                    INNER JOIN tbl_conceptospago cp ON cp.IdConcepto = ca.IdConcepto
                                    INNER JOIN tbl_categoriapago cpa ON cp.IdCategoria = cpa.Id
                                    WHERE ca.IdAlumno  = ? AND ca.AnioLectivo = ? AND cpa.Concepto = 'MENSUALIDAD' AND ca.Saldo <= 0");
            mysqli_stmt_bind_param($stmtCargos, "is", $id_alumno, $id_anio);
            mysqli_stmt_execute($stmtCargos);
            $resCargos = mysqli_stmt_get_result($stmtCargos);

            while($rowCargo = mysqli_fetch_array($resCargos)){
                $mes_referencia = $rowCargo['MesReferencia'];
                $fecha = new DateTime($mes_referencia);
                $numeroMes = (int)$fecha->format('m');
                $mesAbbr = match($numeroMes) {
                    1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun',
                    7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
                };

                // Actualizar columna del mes
                mysqli_query($link, "UPDATE tbl_pagosmensualidades SET $mesAbbr = 'X' WHERE Id = $id_pagoMensuales");
            }
            echo 'bien';
        }
	} else {
		echo "Algunos datos estan vacios";
	}
?>