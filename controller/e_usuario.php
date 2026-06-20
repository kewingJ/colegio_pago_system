<?php
	session_start();
	if(!empty($_POST['id_usuario']))
	{
		include_once '../includes/config.php';
		include_once '../includes/security.php';

		$id_usuario = $_POST['id_usuario'];
		
		//eliminamos al usuario
		$query = mysqli_query($link,"DELETE FROM tbla_usuario WHERE id_usuario = '$id_usuario'") or die(mysqli_error($link));

		echo "bien";
	}
	else {
		echo "mal";
	}
?>