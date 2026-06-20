<?php
	session_start();
	if(!empty($_POST['id_grado']))
	{
		include_once '../includes/config.php';
		include_once '../includes/security.php';

		$id_grado = $_POST['id_grado'];

        $query = mysqli_query($link,"DELETE FROM tbl_grados WHERE IdGrados = '$id_grado'") or die(mysqli_error($link));

		echo "bien";
	}
	else {
		echo "mal";
		exit;
	}
?>