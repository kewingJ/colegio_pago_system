<?php
		session_start();
		include_once '../includes/config.php';
		include_once '../includes/security.php';
		if (!empty($_POST['tasa_cambio'])) {
			
			//
			$tasa_cambio = clean(mysqli_real_escape_string($link,$_POST['tasa_cambio']));

            // 
            $queryUpdate = mysqli_query($link,"UPDATE tasa_cambio SET activo_tasa = 0") or die(mysqli_error($link));
			
            // 
            $fecha = date('Y-m-d');
			$query = mysqli_query($link,"INSERT INTO tasa_cambio VALUES (0,'$tasa_cambio', '$fecha', 1)") or die(mysqli_error($link));

			echo "bien";
		}else{
			echo "mal";
		}
			
		
?>