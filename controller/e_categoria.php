<?php
	session_start();
	if(!empty($_POST['id_categoria']))
	{
		include_once '../includes/config.php';
		include_once '../includes/security.php';

		$id_categoria = $_POST['id_categoria'];

        $query = mysqli_query($link,"DELETE FROM tbl_categoriapago WHERE Id = '$id_categoria'") or die(mysqli_error($link));

		echo "bien";
	}
	else {
		echo "mal";
		exit;
	}
?>