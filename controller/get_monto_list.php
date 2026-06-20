<?php 
	include_once '../includes/config.php';
    include_once '../includes/security.php';
    
    session_start();
    $id = $_SESSION['id_usuario'];

	if (isset($_POST['id_concepto'])) 
	{
        // lista con valores de retorno
        $resultado = array();
		$id_concepto = $_POST['id_concepto'];
		$queryDato = mysqli_query($link,"SELECT * FROM tbl_conceptospago
                                            WHERE IdConcepto = '$id_concepto'");
		$rowData = mysqli_fetch_array($queryDato);
        $id_categoria = $rowData['IdCategoria'];
        $unidades = $rowData['unidades'];

        // 
        $queryDatoDos = mysqli_query($link,"SELECT * FROM tbl_aranceles
                                            WHERE IdCategoria = '$id_categoria'
                                            AND IdConcepto = '$id_concepto'");
		$rowDataDos = mysqli_fetch_array($queryDatoDos);

        $monto = "";
        if(empty($rowDataDos['Monto'])){
            $monto = 'No hay';
        } else {
            $monto = $rowDataDos['Monto'];
        }

        $resultado[0] = $monto;
        $resultado[1] = $unidades;
        echo json_encode($resultado);
	}
?>