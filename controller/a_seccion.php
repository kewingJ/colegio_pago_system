<?php
    session_start();
	include_once '../includes/config.php';
	include_once '../includes/security.php';
	if (!empty($_POST['id_seccion'])) {
        $id_seccion = $_POST['id_seccion'];
			
		// guardo los datos
		$seccion = clean(mysqli_real_escape_string($link,$_POST['seccion']));

        // datos en mayuscula
        $seccion = strtoupper($seccion);
				
        $query = mysqli_query($link,"UPDATE tbl_secciones SET SECCION = '$seccion'
							WHERE ID = '$id_seccion'") or die(mysqli_error($link));

		echo "bien";
	} else {
		echo "mal";
	}
?>