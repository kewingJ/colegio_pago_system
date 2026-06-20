<?php
	session_start();
	if(!empty($_POST['id_anio']))
	{
		include_once '../includes/config.php';
		include_once '../includes/security.php';

		$id_anio = $_POST['id_anio'];

        $query = mysqli_query($link,"DELETE FROM tbl_anios WHERE idanio = '$id_anio'") or die(mysqli_error($link));

		echo "bien";
	}
	else {
		echo "mal";
		exit;
	}
?>