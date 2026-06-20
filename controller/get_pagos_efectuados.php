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
        
		$queryData = mysqli_query($link,"SELECT b.IdReciboMaestro, b.FechaPago, c.NOMBREAPELLIDO,
        d.Concepto AS categoria, e.Concepto AS concepto, b.MesReferencia, b.ValorTotal
                                        FROM tbl_recibomaestro AS a
                                        INNER JOIN tbl_recibo AS b
                                        ON a.Id = b.IdReciboMaestro
                                        INNER JOIN tbl_alumnos AS c
                                        ON b.IdAlumno = c.IDALUMNO
                                        INNER JOIN tbl_categoriapago AS d
                                        ON b.IdCategoriaConcepto = d.Id
                                        INNER JOIN tbl_conceptospago AS e
                                        ON b.IdConcepto = e.IdConcepto
                                        WHERE b.IdAlumno = '$id_alumno'
                                        AND YEAR(b.FechaPago)  = '$id_anio'
                                        AND a.Estado = 1");
		while($rowData = mysqli_fetch_array($queryData)){
            $fecha_pago = date('d/m/Y', strtotime($rowData['FechaPago']));
            $mes_referencia = date('n', strtotime($rowData['MesReferencia']));
            $mes_en_espanol = $meses_en_espanol[$mes_referencia];

            echo '<tr>
                    <td>'.$rowData['IdReciboMaestro'].'</td>
                    <td>'.$fecha_pago.'</td>
                    <td>'.$rowData['NOMBREAPELLIDO'].'</td>
                    <td>'.$rowData['categoria'].'</td>
                    <td>'.$rowData['concepto'].'</td>
                    <td>'.strtoupper($mes_en_espanol).'</td>
                    <td>'.$rowData['ValorTotal'].'</td>
                </tr>';
        }

    } else {
        echo 'mal';
    }
?>
