<?php
	session_start();
	if(!empty($_POST['id_concepto']))
	{
		include_once '../includes/config.php';
		include_once '../includes/security.php';

		$id_concepto = $_POST['id_concepto'];

        $query = mysqli_query($link,"DELETE FROM tbl_conceptospago WHERE IdConcepto = '$id_concepto'") or die(mysqli_error($link));

		echo "bien";
	}
	else {
		echo "mal";
		exit;
	}
?>