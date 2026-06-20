<?php
		session_start();
		include_once '../includes/config.php';
		include_once '../includes/security.php';
		if (!empty($_POST['seccion'])) {
			
			// guardo los datos
			$seccion = clean(mysqli_real_escape_string($link,$_POST['seccion']));
            
            //
			$seccion = strtoupper($seccion);

			$query = mysqli_query($link,"INSERT INTO tbl_secciones VALUES (0,'$seccion')") or die(mysqli_error($link));

			echo "bien";
		}else{
			echo "mal";
		}
			
		
?>