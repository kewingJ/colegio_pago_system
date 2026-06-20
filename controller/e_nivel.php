<?php
	session_start();
	if(!empty($_POST['id_nivel']))
	{
		include_once '../includes/config.php';
		include_once '../includes/security.php';

		$id_nivel = $_POST['id_nivel'];

        $query = mysqli_query($link,"DELETE FROM tbl_nivel WHERE IdNivel = '$id_nivel'") or die(mysqli_error($link));

		echo "bien";
	}
	else {
		echo "mal";
		exit;
	}
?>