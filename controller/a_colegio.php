<?php
    session_start();
	include_once '../includes/config.php';
	include_once '../includes/security.php';

	if (!empty($_POST['nombre']) &&
        !empty($_POST['telefono']) &&
        !empty($_POST['direccion'])) {
        
		$nombre = clean(mysqli_real_escape_string($link,$_POST['nombre']));
        $telefono = clean(mysqli_real_escape_string($link,$_POST['telefono']));
        $direccion = clean(mysqli_real_escape_string($link,$_POST['direccion']));
        $logo     = isset($_POST['logo_colegio']) ? trim($_POST['logo_colegio']) : '';

        $nombre = strtoupper($nombre);
				
        $query = mysqli_query($link,"UPDATE tbl_parametros SET NOMBRECOLEGIO = '$nombre',
                                                        TELEFONOS = '$telefono',
                                                        logo_colegio = '$logo',
                                                        DIRECCION = '$direccion'") or die(mysqli_error($link));

		echo "bien";
	} else {
		echo "mal";
	}
?>