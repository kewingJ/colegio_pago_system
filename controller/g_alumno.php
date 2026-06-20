<?php
		session_start();
		include_once '../includes/config.php';
		include_once '../includes/security.php';

		error_reporting(E_ALL);
	ini_set('display_errors', '1');

		if (!empty($_POST['nombre']) &&
            !empty($_POST['fecha_nacimiento'])) {

			// guardo los datos
			$nombre             = clean($_POST['nombre']);
			$fecha_cadena       = clean($_POST['fecha_nacimiento']);
			$papa               = clean($_POST['papa']);
			$mama               = clean($_POST['mama']);
			$telefono           = clean($_POST['telefono']);
            $email              = clean($_POST['email']);
            $direccion          = clean($_POST['direccion']);
			$foto               = clean($_POST['foto_alumno'] ?? '');

			// avatar por defecto si no hay foto (opcional)
			if ($foto === '') {
				//imagen de avatar si no tiene foto
				$url = "https://api.genderize.io?name=" . urlencode($nombre);
				$response = @file_get_contents($url);
				$genero = 'male';
				if ($response) {
					$data = json_decode($response, true);
					$genero = $data['gender'] ?? 'male';
				}
				// pon aquí tus rutas locales si las tienes
				$foto = ($genero === 'male') ? 'img/avatar_m.png' : (($genero === 'female') ? 'img/avatar_f.png' : 'img/avatar.png');
				// $foto = ''; // o deja vacío si no usas avatares por defecto
			} else {
				// Si hay foto, necesitamos el genero igualmente para la DB si es requerida
				$url = "https://api.genderize.io?name=" . urlencode($nombre);
				$response = @file_get_contents($url);
				$genero = 'male';
				if ($response) {
					$data = json_decode($response, true);
					$genero = $data['gender'] ?? 'male';
				}
			}

            // formato de fecha
            $fecha_nacimiento = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $fecha_cadena)));

            // datos en mayuscula
            $nombre     = strtoupper($nombre);
            $papa       = strtoupper($papa);
            $mama       = strtoupper($mama);
            $direccion  = strtoupper($direccion);

			$stmt = mysqli_prepare($link, "INSERT INTO tbl_alumnos (NOMBREAPELLIDO, CODIGO, FECHANACIMIENTO, NOMBREMADRE, NOMBREPADRE, DIRECCION, TELEFONO, EMAIL, GENERO, FOTO) VALUES (?, '', ?, ?, ?, ?, ?, ?, ?, ?)");
			mysqli_stmt_bind_param($stmt, "sssssssss", $nombre, $fecha_nacimiento, $mama, $papa, $direccion, $telefono, $email, $genero, $foto);

			if(mysqli_stmt_execute($stmt)) {
				echo "bien";
			} else {
				echo "mal";
			}
		}else{
			echo "mal";
		}
?>