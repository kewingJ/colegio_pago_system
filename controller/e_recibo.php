<?php
	session_start();
    $nombre     = $_SESSION['nombre'];
    $apellido   = $_SESSION['apellido'];

	include_once '../includes/config.php';
	include_once '../includes/security.php';
    date_default_timezone_set('America/Managua');

	if (!empty($_POST['id_recibo'])) {
			
		//
        $id_recibo = clean(mysqli_real_escape_string($link,$_POST['id_recibo']));

        // anular
        $queryUno = mysqli_query($link,"UPDATE tbl_recibomaestro SET Estado = 0 WHERE Id = '$id_recibo'") or die(mysqli_error($link));

        $queryDos = mysqli_query($link,"UPDATE tbl_recibo SET Anulado = 1 WHERE IdReciboMaestro = '$id_recibo'") or die(mysqli_error($link));

        echo 'bien';
	} else {
		echo "Mal";
	}	
?>