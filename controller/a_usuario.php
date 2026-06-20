<?php
	session_start();
	include_once '../includes/config.php';
	include_once '../includes/security.php';

if (!empty($_POST['id_usuario']) && 
    !empty($_POST['nombre']) && 
    !empty($_POST['apellido']) && 
    !empty($_POST['tipo_usuario']) && 
	!empty($_POST['celular']) &&
    !empty($_POST['email'])) {

	$id_usuario = $_POST['id_usuario'];
	$nombre         = clean(mysqli_real_escape_string($link,$_POST['nombre']));
	$apellido       = clean(mysqli_real_escape_string($link,$_POST['apellido']));
	$email          = clean(mysqli_real_escape_string($link,$_POST['email']));
	$pass           = clean(mysqli_real_escape_string($link,$_POST['pass']));
	$celular		= clean(mysqli_real_escape_string($link,$_POST['celular']));
	$tipo_usuario   = clean(mysqli_real_escape_string($link,$_POST['tipo_usuario']));

	if (empty($pass)) {
		//actualizamos la informacion del usuario
		$query = mysqli_query($link,"UPDATE tbla_usuario SET nombre_usuario     = '$nombre',
															apellido_usuario    = '$apellido',
															email_usuario       = '$email',
                                                            tipo_usuario        = '$tipo_usuario',
															telefono           = '$celular'
															WHERE id_usuario = '$id_usuario'") or die(mysqli_error($link));
	} else {
		//encriptar contraseña
		$opciones = [
		'cost' => 12
		];
		$passw = password_hash($pass,PASSWORD_BCRYPT,$opciones);

		//actualizamos la informacion del usuario
		$query = mysqli_query($link,"UPDATE tbla_usuario SET nombre_usuario     = '$nombre',
															apellido_usuario    = '$apellido',
															email_usuario       = '$email',
                                                            tipo_usuario        = '$tipo_usuario',
															password_usuario    = '$passw',
															telefono           = '$celular'
															WHERE id_usuario = '$id_usuario'") or die(mysqli_error($link));
	}
    echo "bien";
} else {
	echo "mal";
}
?>