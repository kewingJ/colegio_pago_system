<?php
		session_start();
		include_once '../includes/config.php';
		include_once '../includes/security.php';
		if (!empty($_POST['nivel'])) {
			
			// guardo los datos
			$nivel = clean(mysqli_real_escape_string($link,$_POST['nivel']));

            // datos en mayuscula
            $nivel = strtoupper($nivel);
				
			$query = mysqli_query($link,"INSERT INTO tbl_nivel VALUES (0,'$nivel', 0)") or die(mysqli_error($link));

			echo "bien";
		}else{
			echo "mal";
		}
			
		
?>