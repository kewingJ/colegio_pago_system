<?php 
	include_once '../includes/config.php';
    include_once '../includes/security.php';
    
    session_start();
    $id = $_SESSION['id_usuario'];

	if (!empty($id)) 
	{
        $mes_referencia = $_POST['mes_referencia'];
        $categoria = $_POST['categoria'];
        $concepto = $_POST['concepto'];
        $monto = $_POST['monto'];
        //
        $fecha_recibo = $_POST['fecha_recibo'];
        $id_categoria = $_POST['id_categoria'];
        $id_concepto = $_POST['id_concepto'];
        $id_nivel = $_POST['id_nivel'];
        
		$queryData = mysqli_query($link,"SELECT * FROM tbl_numeracionfactura");
		$rowData = mysqli_fetch_array($queryData);

        echo '<tr>
                <td>
                    <a href="#0" class="eliminarReciboBtn">
                        <i class="fa fa-trash"></i>
                    </a>
                </td>
                <td>'.$rowData['Numero'].'</td>
                <td>'.$mes_referencia.'</td>
                <td>'.$categoria.'</td>
                <td>'.$concepto.'</td>
                <td>'.$monto.'</td>
                <td style="display: none;">'.$fecha_recibo.'</td>
                <td style="display: none;">'.$id_categoria.'</td>
                <td style="display: none;">'.$id_concepto.'</td>
                <td style="display: none;">'.$id_nivel.'</td>
            </tr>';
    } else {
        echo 'mal';
    }
?>
