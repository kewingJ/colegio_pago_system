<?php 
	include_once '../includes/config.php';
    include_once '../includes/security.php';
    
    session_start();
    $id = $_SESSION['id_usuario'];

	if (!empty($id)) 
	{
        $id_alumno = $_POST['id_alumno'];

		$queryData = mysqli_query($link,"SELECT * FROM tbl_alumnos WHERE IDALUMNO = '$id_alumno'");
		$rowData = mysqli_fetch_array($queryData);

        echo $rowData['EMAIL'];
    } else {
        echo 'mal';
    }
?>
