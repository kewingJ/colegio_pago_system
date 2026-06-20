<?php
		session_start();
		include_once '../includes/config.php';
		include_once '../includes/security.php';
		if (!empty($_POST['nombre']) && 
            !empty($_POST['apellido']) && 
            !empty($_POST['tipo_usuario']) && 
            !empty($_POST['email']) && 
			!empty($_POST['celular']) &&
            !empty($_POST['pass'])) {
			
			// guardo los datos del usuario
			$nombre         = clean(mysqli_real_escape_string($link,$_POST['nombre']));
			$apellido       = clean(mysqli_real_escape_string($link,$_POST['apellido']));
			$email          = clean(mysqli_real_escape_string($link,$_POST['email']));
			$pass           = clean(mysqli_real_escape_string($link,$_POST['pass']));
			$celular		= clean(mysqli_real_escape_string($link,$_POST['celular']));
			$tipo_usuario   = clean(mysqli_real_escape_string($link,$_POST['tipo_usuario']));
			$fecha_r = date('Y-m-d');
			$activo = 1;

			//encriptar contraseña
			$opciones = [
			'cost' => 12
			];
			$passw = password_hash($pass,PASSWORD_BCRYPT,$opciones);
				
			$query = mysqli_query($link,"INSERT INTO tbla_usuario VALUES (0,'$nombre','$apellido','$email','$celular','$passw','$tipo_usuario','$activo','$fecha_r')") or die(mysqli_error($link));

			echo "bien";
		}else{
			echo "mal";
		}
			
		
?>