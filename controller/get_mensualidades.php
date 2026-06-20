<?php 
	include_once '../includes/config.php';
    include_once '../includes/security.php';
    
    session_start();
    $id = $_SESSION['id_usuario'];

	if (isset($_POST['id_alumno'])) 
	{
        $id_alumno = $_POST['id_alumno'];
        $id_anio = $_POST['year'];

        $meses_en_espanol = array(
            1 => 'enero',
            2 => 'febrero',
            3 => 'marzo',
            4 => 'abril',
            5 => 'mayo',
            6 => 'junio',
            7 => 'julio',
            8 => 'agosto',
            9 => 'septiembre',
            10 => 'octubre',
            11 => 'noviembre',
            12 => 'diciembre'
        );
        
        $anulado = 0;
		$queryData = mysqli_query($link,"SELECT c.MesReferencia AS MESPAGADO, c.Saldo AS PAGADO FROM tbl_cargos c
                                    INNER JOIN tbl_conceptospago cp ON cp.IdConcepto = c.IdConcepto
                                    INNER JOIN tbl_categoriapago cpa ON cp.IdCategoria = cpa.Id
                                    WHERE c.AnioLectivo = '$id_anio' 
                                    AND c.IdAlumno = '$id_alumno' 
                                    AND cpa.Concepto = 'MENSUALIDAD'
                                    ORDER BY MONTH(c.MesReferencia)");
		while($rowData = mysqli_fetch_array($queryData)){
            $mes_referencia = date('n', strtotime($rowData['MESPAGADO']));
            $mes_en_espanol = $meses_en_espanol[$mes_referencia];

            $resultado = $rowData['PAGADO'] <= 0 ? 'SI' : 'NO';

            //si el recibo no esta anulado, mostrar pagado
            $queryDataRecibo = mysqli_query($link,"SELECT * FROM tbl_recibo 
                                INNER JOIN tbl_categoriapago cpa ON tbl_recibo.IdCategoriaConcepto = cpa.Id
                                WHERE Anio = '$id_anio'
                                AND IdAlumno = '$id_alumno' 
                                AND cpa.Concepto = 'MENSUALIDAD'
                                AND MONTH(MesReferencia) = $mes_referencia");
            $rowDataRecibo = mysqli_fetch_array($queryDataRecibo);
            $anulado = $rowDataRecibo['Anulado'];

            if($anulado == 0){
                echo '<tr>
                        <td>'.strtoupper($mes_en_espanol).'</td>
                        <td>'.$resultado.'</td>
                    </tr>';
            }
        }

    } else {
        echo 'mal';
    }
?>
