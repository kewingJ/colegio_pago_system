<?php
	session_start();
	include_once '../includes/config.php';
	include_once '../includes/security.php';

    error_reporting(E_ALL);
    ini_set('display_errors', '1');

if (!empty($_POST['id_alumno']) && 
    !empty($_POST['nombre']) && 
    !empty($_POST['fecha_nacimiento'])) {

    $id_alumno = $_POST['id_alumno'];
	$nombre             = clean(mysqli_real_escape_string($link,$_POST['nombre']));
	$fecha_cadena       = clean(mysqli_real_escape_string($link,$_POST['fecha_nacimiento']));
	$papa               = clean(mysqli_real_escape_string($link,$_POST['papa']));
	$mama               = clean(mysqli_real_escape_string($link,$_POST['mama']));
	$telefono           = clean(mysqli_real_escape_string($link,$_POST['telefono']));
    $email              = clean(mysqli_real_escape_string($link,$_POST['email']));
    $direccion          = clean(mysqli_real_escape_string($link,$_POST['direccion']));
    $foto               = clean(mysqli_real_escape_string($link, $_POST['foto_alumno_edit'] ?? ''));

    // formato de fecha
    $fecha_nacimiento = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $fecha_cadena)));

    // datos en mayuscula
    $nombre     = strtoupper($nombre);
    $papa       = strtoupper($papa);
    $mama       = strtoupper($mama);
    $direccion  = strtoupper($direccion);

    // avatar por defecto si no hay foto (opcional)
	if ($foto === '') {
        //imagen de avatar si no tiene foto
        $url = "https://api.genderize.io?name=" . urlencode($nombre);
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        $genero = $data['gender'];
		// pon aquí tus rutas locales si las tienes
		$foto = ($genero === 'male') ? 'img/avatar_m.png' : (($genero === 'female') ? 'img/avatar_f.png' : 'img/avatar.png');
		// $foto = ''; // o deja vacío si no usas avatares por defecto
	}

	//actualizamos la informacion del usuario
	$query = mysqli_query($link,"UPDATE tbl_alumnos SET NOMBREAPELLIDO    = '$nombre',
														FECHANACIMIENTO   = '$fecha_nacimiento',
														NOMBREMADRE       = '$mama',
                                                        NOMBREPADRE       = '$papa',
                                                        DIRECCION         = '$direccion',
                                                        TELEFONO          = '$telefono',
                                                        EMAIL             = '$email',
                                                        FOTO              = '$foto'
														WHERE IDALUMNO = '$id_alumno'") or die(mysqli_error($link));
    echo "bien";
} else {
	echo "mal";
}
?>