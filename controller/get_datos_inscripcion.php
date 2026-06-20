<?php 
	include_once '../includes/config.php';
    include_once '../includes/security.php';
    
    session_start();
    $id = $_SESSION['id_usuario'];

	if (isset($_POST['id_alumno'])) 
	{
        $id_alumno = $_POST['id_alumno'];
        $id_anio = $_POST['year'];
        
		$queryData = mysqli_query($link,"SELECT I.ID, I.IDMATRICULA, I.IDALUMNO, A.NOMBREAPELLIDO,
                                    I.IDNIVEL, N.Nivel, I.IDGRADO, G.Grado, I.SECCION, I.ANIOLECTIVO
                                    FROM tbl_inscripcion I
                                    INNER JOIN tbl_alumnos A ON I.IDALUMNO = A.IDALUMNO
                                    INNER JOIN tbl_nivel N ON N.IdNivel = I.IDNIVEL
                                    INNER JOIN tbl_grados G ON G.IdGrados = I.IDGRADO
                                    WHERE  I.IDALUMNO = '$id_alumno'
                                    ORDER BY I.ANIOLECTIVO DESC
                                    LIMIT 1");
		$rowData = mysqli_fetch_array($queryData);
        if($rowData){
            echo '<h5>'.$rowData['Nivel'].' '.$rowData['Grado'].' '.$rowData['SECCION'].'</h5>';
            echo '<input type="hidden" id="nivel_id" value="'.$rowData['IDNIVEL'].'">';
        }

    } else {
        echo 'mal';
    }
?>
