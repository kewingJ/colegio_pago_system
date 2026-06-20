<?php
    session_start();
	include_once '../includes/config.php';
	include_once '../includes/security.php';
	if (!empty($_POST['id_categoria']) &&
        !empty($_POST['id_concepto']) &&
        !empty($_POST['concepto'])) {

        $id_concepto = $_POST['id_concepto'];
        $id_categoria = $_POST['id_categoria'];
		$unidades = $_POST['unidades'];
		$nivelesCP = $_POST['nivelesCP'];
			
		// guardo los datos
		$concepto = clean(mysqli_real_escape_string($link,$_POST['concepto']));

        // datos en mayuscula
        $concepto = strtoupper($concepto);

		// verifica si la categoria ya existe
		// $verifica = mysqli_query($link,"SELECT * FROM tbl_conceptospago WHERE Concepto = '$concepto'");
		// 	$row = mysqli_fetch_array($verifica);
		// 	if($row['IdConcepto'] > 0){
		// 		echo "Concepto ya existe";
		// 	} else {	
		$query = mysqli_query($link,"UPDATE tbl_conceptospago SET IdCategoria = '$id_categoria',
												Concepto = '$concepto',
												unidades = '$unidades'
								WHERE IdConcepto = '$id_concepto'") or die(mysqli_error($link));
		echo "bien";
			// }
	} else {
		echo "mal";
	}
?>