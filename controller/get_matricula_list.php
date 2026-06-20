<?php 
	include_once '../includes/config.php';
    include_once '../includes/security.php';
    
    session_start();
    $id = $_SESSION['id_usuario'];

	if (isset($_POST['id_anio'])) 
	{
        echo '<option value="">Alumnos</option>';
		$id_anio = $_POST['id_anio'];
		$queryDato = mysqli_query($link,"SELECT M.IDALUMNO, A.NOMBREAPELLIDO
                                    FROM tbl_matricula M
                                    INNER JOIN tbl_alumnos A
                                    ON A.IDALUMNO = M.IDALUMNO
                                    WHERE M.ANIO = '$id_anio'");
		while($rowData = mysqli_fetch_array($queryDato)){
            echo '<option value="'.$rowData['IDALUMNO'].'">'.$rowData['NOMBREAPELLIDO'].'</option>';
        }
	}
?>