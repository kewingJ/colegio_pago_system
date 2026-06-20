<?php
    session_start();
	include_once '../includes/config.php';
	include_once '../includes/security.php';
	if (!empty($_POST['id_nivel'])) {
        $id_nivel = $_POST['id_nivel'];
			
		// guardo los datos
		$nivel = clean(mysqli_real_escape_string($link,$_POST['nivel']));

        // datos en mayuscula
        $nivel = strtoupper($nivel);
				
        $query = mysqli_query($link,"UPDATE tbl_nivel SET Nivel = '$nivel'
							WHERE IdNivel = '$id_nivel'") or die(mysqli_error($link));

		echo "bien";
	} else {
		echo "mal";
	}
?>