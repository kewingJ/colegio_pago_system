<?php
		session_start();
		include_once '../includes/config.php';
		include_once '../includes/security.php';
		if (!empty($_POST['id_anio']) &&
            !empty($_POST['id_estado'])) {
                
            // guardo los datos
            $id_anio = clean(mysqli_real_escape_string($link,$_POST['id_anio']));
            $id_estado = clean(mysqli_real_escape_string($link,$_POST['id_estado']));
            $estado = $id_estado == 'Activo' ? 1 : 0;
			
            if($estado == 1){
                $queryDataDos = mysqli_query($link,"SELECT * FROM tbl_anios WHERE estado = 1");
                $banderaDos = mysqli_num_rows($queryDataDos);

                if($banderaDos == 0){
                    $query = mysqli_query($link,"UPDATE tbl_anios SET estado = '$estado' WHERE idanio = '$id_anio'") or die(mysqli_error($link));
                    echo "bien";
                } else {
                    echo "Un año lectivo ya se encuentra activo";
                }
            } else {
                $query = mysqli_query($link,"UPDATE tbl_anios SET estado = '$estado' WHERE idanio = '$id_anio'") or die(mysqli_error($link));
                echo "bien";
            }
		} else {
			echo "Algunos campos estan vacios";
		}
			
		
?>