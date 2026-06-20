<?php
	session_start();
    $nombre     = $_SESSION['nombre'];
    $apellido   = $_SESSION['apellido'];

    error_reporting(E_ALL);
    ini_set('display_errors', '1');

	include_once '../includes/config.php';
	include_once '../includes/security.php';
    date_default_timezone_set('America/Managua');

	if (!empty($_POST['rango'])) {
        list($fecha_inicio, $fecha_fin) = explode('|', $_POST['rango']);
        $fecha_inicio = DateTime::createFromFormat('d/m/Y', $fecha_inicio)->format('Y-m-d');
        $fecha_fin = DateTime::createFromFormat('d/m/Y', $fecha_fin)->format('Y-m-d');

        
        // 
        $queryNegocio = mysqli_query($link, "SELECT * FROM tbl_parametros");
        $rowNegocio = mysqli_fetch_array($queryNegocio);

        // mostra la data de recibo
        echo "
            <table width='272' style='margin-left:17px;' cellspacing='0' cellpadding='0'>
                <tr>
                    <td style='text-align:center; font-weight: 900; font-size: 15px; font-family: Trebuchet MS, Arial, Helvetica, sans-serif;'>
                    ".$rowNegocio['NOMBRECOLEGIO']."
                    </td>
                </tr>
                <tr>
                    <td>
                        <table width='272' cellspacing='0' cellpadding='0'>
                            <tr style='height:5px;text-align:center;'>
                                <td><img src='".$rowNegocio['logo_colegio']."' width='100'></td>
                            </tr>
                            <tr>
                                <td style='text-align:center; font-size: 12px; font-family: Trebuchet MS, Arial, Helvetica, sans-serif;'>
                                CIERRE DE CAJA
                                </td>
                            </tr>
                            <tr>
                                <td style='text-align:center; font-size: 12px; font-family: Trebuchet MS, Arial, Helvetica, sans-serif;'>
                                Fecha ".$fecha_inicio." al ".$fecha_fin."
                                </td>
                            </tr>
                            <tr style='height:15px;'>
                                <td></td>
                            </tr>
                            <tr style='height:15px;'>
                                <td></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table width='272' cellspacing='0' cellpadding='0'>";
                            // detalles del recibo
                            $queryDetalleRecibo = mysqli_query($link, "SELECT 
                                                        CASE WHEN R.IE = 'E' THEN 'EGRESOS' ELSE 'INGRESOS' END AS CAJA, 
                                                        CP.Concepto AS NombreCategoria, 
                                                        DATE_FORMAT(R.FechaPago, '%d/%m/%Y') AS FechaPago, 
                                                        SUM(R.ValorTotal) AS VALOR 
                                                        FROM tbl_recibo R 
                                                        INNER JOIN tbl_categoriapago CP 
                                                        ON CP.Id = R.IdCategoriaConcepto 
                                                        WHERE R.Anulado = 0 
                                                        AND R.FechaPago BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'
                                                        GROUP BY R.IE, CP.Concepto, 
                                                        DATE_FORMAT(R.FechaPago, '%d/%m/%Y')");
                            $totalRecibo = 0;
                            while($rowDetalle = mysqli_fetch_array($queryDetalleRecibo)){
                                echo "
                                <tr>
                                    <td style='text-align:left; font-size: 12px; font-family: Trebuchet MS, Arial, Helvetica, sans-serif;' width='200'>
                                        ".$rowDetalle['NombreCategoria']."
                                    </td>
                                    <td style='text-align:right; font-size: 12px; font-family: Trebuchet MS, Arial, Helvetica, sans-serif;' width='72'>
                                        ".$rowDetalle['VALOR']."
                                    </td>
                                </tr>";
                                $totalRecibo += $rowDetalle['VALOR'];
                            }

                            echo "
                            <tr style='height:10px;'>
                                <td></td>
                                <td></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table width='272' cellspacing='0' cellpadding='0'>
                            <tr style='text-align: right;'>
                                <td style='font-size: 12px; font-family: Trebuchet MS, Arial, Helvetica, sans-serif; text-align: left;' width='72'>
                                <strong>TOTAL</strong>
                                </td>
                                <td style='font-size: 12px; font-family: Trebuchet MS, Arial, Helvetica, sans-serif; text-align: right;' width='200'>
                                <strong>".$totalRecibo.".00</strong>
                                </td>
                            </tr>
                        </table>
                        <tr style='height:15px;'>
                            <td></td>
                        </tr>
                    </td>
                </tr>
                <tr>
                    <td><hr></td>
                </tr>
            </table>";
	} else {
		echo "mal";
	}	
?>