<?php
    session_start();
	include_once '../includes/config.php';
	include_once '../includes/security.php';
	if (!empty($_POST['id_categoria'])) {
        $id_categoria = $_POST['id_categoria'];
			
		// guardo los datos
		$categoria = clean(mysqli_real_escape_string($link,$_POST['categoria']));

        // datos en mayuscula
        $categoria = strtoupper($categoria);

		// verifica si la categoria ya existe
		$verifica = mysqli_query($link,"SELECT * FROM tbl_categoriapago WHERE Concepto = '$categoria'");
		$row = mysqli_fetch_array($verifica);
		if($row['Id'] > 0){
			echo "Categoria ya existe";
		} else {
			$query = mysqli_query($link,"UPDATE tbl_categoriapago SET Concepto = '$categoria'
								WHERE Id = '$id_categoria'") or die(mysqli_error($link));
			echo "bien";
		}
	} else {
		echo "mal";
	}
?>