<?php
		session_start();
		include_once '../includes/config.php';
		include_once '../includes/security.php';
		if (!empty($_POST['id_nivel']) &&
            !empty($_POST['grado'])) {
			
			// guardo los datos
			$id_nivel = clean(mysqli_real_escape_string($link,$_POST['id_nivel']));
			$cupo = clean(mysqli_real_escape_string($link,$_POST['cupo']));
            //obtener el nombre del nivel
            $queryData = mysqli_query($link,"SELECT * FROM tbl_nivel WHERE IdNivel = '$id_nivel'");
            $rowData = mysqli_fetch_array($queryData);
            $nivel = $rowData['Nivel'];

            $grado = clean(mysqli_real_escape_string($link,$_POST['grado']));

            // datos en mayuscula
            $grado = strtoupper($grado);
            $nivel = strtoupper($nivel);
				
			$query = mysqli_query($link,"INSERT INTO tbl_grados VALUES (0,'$grado', '$nivel', $id_nivel, $cupo)") or die(mysqli_error($link));

			echo "bien";
		}else{
			echo "mal";
		}
			
		
?>