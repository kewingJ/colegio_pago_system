<?php
	session_start();
	if(!empty($_POST['id_seccion']))
	{
		include_once '../includes/config.php';
		include_once '../includes/security.php';

		$id_seccion = $_POST['id_seccion'];

        $query = mysqli_query($link,"DELETE FROM tbl_secciones WHERE ID = '$id_seccion'") or die(mysqli_error($link));

		echo "bien";
	}
	else {
		echo "mal";
		exit;
	}
?>