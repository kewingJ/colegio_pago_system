<?php
		session_start();
		include_once '../includes/config.php';
		include_once '../includes/security.php';
		if (!empty($_POST['year']) &&
            !empty($_POST['id_estado'])) {
                
            // guardo los datos
            $year = clean(mysqli_real_escape_string($link,$_POST['year']));
            $id_estado = clean(mysqli_real_escape_string($link,$_POST['id_estado']));
            $estado = $id_estado == 'Activo' ? 1 : 0;

            // verificar que el año no exista
            $queryData = mysqli_query($link,"SELECT * FROM tbl_anios WHERE anio = '$year'");
            $bandera = mysqli_num_rows($queryData);
			
            if($bandera == 0){
                // verificar si ya hay un año activo
                if($estado == 1){
                    $queryDataDos = mysqli_query($link,"SELECT * FROM tbl_anios WHERE estado = 1");
                    $banderaDos = mysqli_num_rows($queryDataDos);

                    if($banderaDos == 0){
                        $query = mysqli_query($link,"INSERT INTO tbl_anios VALUES (0,'$year', '$estado')") or die(mysqli_error($link));
                        echo "bien";
                    } else {
                        echo "Un año lectivo ya se encuentra activo";
                    }
                } else {
                    $query = mysqli_query($link,"INSERT INTO tbl_anios VALUES (0,'$year', '$estado')") or die(mysqli_error($link));
                    echo "bien";
                }
            } else {
                echo "Este año lectivo ya esta registrado";
            }
		} else {
			echo "Algunos campos estan vacios";
		}
			
		
?>