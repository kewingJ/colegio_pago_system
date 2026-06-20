<?php 
	include_once '../includes/config.php';
    include_once '../includes/security.php';
    
    session_start();
    $id = $_SESSION['id_usuario'];

	if (isset($_POST['id_nivel'])) 
	{
        echo '<option value="">Grados</option>';
		$id_nivel = $_POST['id_nivel'];
		$queryDato = mysqli_query($link,"SELECT * FROM tbl_grados 
                                    WHERE IdNivel = '$id_nivel'");
		while($rowData = mysqli_fetch_array($queryDato)){
            echo '<option value="'.$rowData['IdGrados'].'">'.$rowData['Grado'].'</option>';
        }
	}
?>