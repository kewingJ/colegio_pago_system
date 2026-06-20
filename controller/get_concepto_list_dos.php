<?php 
	include_once '../includes/config.php';
    include_once '../includes/security.php';
    
    session_start();
    $id = $_SESSION['id_usuario'];

	if (isset($_POST['id_categoria'])) 
	{
        echo '<option value="">Concepto pago</option>';
		$categoria = $_POST['id_categoria'];
        $queryDato = mysqli_query($link,"SELECT * FROM tbl_categoriapago WHERE Concepto = '$categoria'");
        $rowData = mysqli_fetch_array($queryDato);
        $id_categoria = $rowData['Id'];

        // 
		$queryDato = mysqli_query($link,"SELECT * FROM tbl_conceptospago
                                            WHERE IdCategoria = '$id_categoria'");
		while($rowData = mysqli_fetch_array($queryDato)){
            echo '<option value="'.$rowData['Concepto'].'">'.$rowData['Concepto'].'</option>';
        }
	}
?>