<?php
	session_start();
    $nombre     = $_SESSION['nombre'];
    $apellido   = $_SESSION['apellido'];

	include_once '../includes/config.php';
	include_once '../includes/security.php';
    date_default_timezone_set('America/Managua');

	if (!empty($_POST['id_nivel']) &&
        !empty($_POST['id_grado']) &&
        !empty($_POST['id_inscripcion']) &&
        !empty($_POST['id_matricula']) &&
        !empty($_POST['id_seccion'])) {
			
		//
        $id_inscripcion = clean(mysqli_real_escape_string($link,$_POST['id_inscripcion']));
        $id_matricula = clean(mysqli_real_escape_string($link,$_POST['id_matricula']));

        $id_nivel = clean(mysqli_real_escape_string($link,$_POST['id_nivel']));
        $id_grado = clean(mysqli_real_escape_string($link,$_POST['id_grado']));
        $id_seccion = clean(mysqli_real_escape_string($link,$_POST['id_seccion']));
        $email = clean(mysqli_real_escape_string($link,$_POST['email']));
        $id_alumno = clean(mysqli_real_escape_string($link,$_POST['id_alumno_input']));

        // editar
        $queryInscripcion = mysqli_query($link,"UPDATE tbl_inscripcion SET IDNIVEL = '$id_nivel', IDGRADO = '$id_grado', SECCION = '$id_seccion'
                                        WHERE IDMATRICULA = '$id_matricula' AND ID = '$id_inscripcion'") or die(mysqli_error($link));


        $queryUpdateAlumno = mysqli_query($link,"UPDATE tbl_alumnos SET EMAIL = '$email' WHERE IDALUMNO = '$id_alumno'") or die(mysqli_error($link));

        echo 'bien';
	} else {
		echo "Algunos datos estan vacios";
	}	
?>