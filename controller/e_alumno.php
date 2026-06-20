<?php
	session_start();
	if(!empty($_POST['id_alumno']))
	{
		include_once '../includes/config.php';
		include_once '../includes/security.php';

		$id_alumno = $_POST['id_alumno'];

        $query = mysqli_query($link,"DELETE FROM tbl_alumnos WHERE IDALUMNO = '$id_alumno'") or die(mysqli_error($link));

		echo "bien";
	}
	else {
		echo "mal";
		exit;
	}
?>