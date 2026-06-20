<?php
		session_start();
		include_once '../includes/config.php';
		include_once '../includes/security.php';

		error_reporting(E_ALL);
		ini_set('display_errors', '1');

		if (!empty($_POST['id_categoria'])) {
			
			// guardo los datos
			$id_categoria = clean(mysqli_real_escape_string($link,$_POST['id_categoria']));
            $concepto = clean(mysqli_real_escape_string($link,$_POST['concepto']));
			$unidades = clean(mysqli_real_escape_string($link,$_POST['unidades']));
			$nivelesCP = $_POST['nivelesCP'];

            // datos en mayuscula
			if (!empty($concepto)) {
            	$concepto = strtoupper($concepto);
			} else {
				//obtener la categoria para asignarla al concepto
				$queryCategoria = mysqli_query($link, "SELECT * FROM tbl_categoriapago WHERE Id = '$id_categoria'");
				$rowCategoria = mysqli_fetch_array($queryCategoria);
				$concepto = strtoupper($rowCategoria['Concepto']);
			}

			// verifica si la categoria ya existe
			// $verifica = mysqli_query($link,"SELECT * FROM tbl_conceptospago WHERE Concepto = '$concepto'");
			// $row = mysqli_fetch_array($verifica);
			// if($row['IdConcepto'] > 0){
			// 	echo "Concepto ya existe";
			// } else {

			// insertar el concepto y verificar si se asigna a todos los niveles en el checkbox
			if(!empty($nivelesCP)){
				foreach ($nivelesCP as $nivel) {
					$conceptoAux = strtoupper($concepto . " - " . $nivel);
					$query = mysqli_query($link,"INSERT INTO tbl_conceptospago VALUES (0,'$id_categoria', '$conceptoAux', NULL, NULL, '$unidades')") or die(mysqli_error($link));
				}
			} else {
				$query = mysqli_query($link,"INSERT INTO tbl_conceptospago VALUES (0,'$id_categoria', '$concepto', NULL, NULL, '$unidades')") or die(mysqli_error($link));
			}
			echo "bien";
			// }
		}else{
			echo "mal";
		}
			
		
?>