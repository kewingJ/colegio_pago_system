<?php
	session_start();
    $nombre     = $_SESSION['nombre'];
    $apellido   = $_SESSION['apellido'];

	include_once '../includes/config.php';
	include_once '../includes/security.php';
    date_default_timezone_set('America/Managua');

	if (!empty($_POST['id_inscripcion'])) {
			
		//
        $id_inscripcion = clean(mysqli_real_escape_string($link,$_POST['id_inscripcion']));

        // eliminar
        $queryInscripcion = mysqli_query($link,"DELETE FROM tbl_inscripcion WHERE ID = '$id_inscripcion'") or die(mysqli_error($link));

        $queryPagoMensual = mysqli_query($link,"DELETE FROM tbl_pagosmensualidades WHERE IdInscripcion = '$id_inscripcion'") or die(mysqli_error($link));

        echo 'bien';
	} else {
		echo "Algunos datos estan vacios";
	}	
?>