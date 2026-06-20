<?php
	session_start();
    $nombre     = $_SESSION['nombre'];
    $apellido   = $_SESSION['apellido'];

    error_reporting(E_ALL);
    ini_set('display_errors', '1');

	include_once '../includes/config.php';
	include_once '../includes/security.php';
    date_default_timezone_set('America/Managua');

	if (!empty($_POST['id_recibo'])) {
			
		//
		$id_recibo = clean(mysqli_real_escape_string($link,$_POST['id_recibo']));
        $queryRecibo = mysqli_query($link, "SELECT * FROM tbl_recibomaestro WHERE Id = '$id_recibo'");
        $rowRecibo = mysqli_fetch_array($queryRecibo);
        $id_alumno = $rowRecibo['IdAlumno'];

        // 
        $fecha_actual = date("Y-m-d H:i:s");
        $usuario = $rowRecibo['Cajero'];
        
        // 
        $queryNegocio = mysqli_query($link, "SELECT * FROM tbl_parametros");
        $rowNegocio = mysqli_fetch_array($queryNegocio);

        // 
        $fechaObj = date_create($fecha_actual);
        $fechaFormateada = date_format($fechaObj, 'd/m/Y H:i:s');

        // 
        $queryAlumno = mysqli_query($link, "SELECT * FROM tbl_alumnos WHERE IDALUMNO = '$id_alumno'");
        $rowAlumno = mysqli_fetch_array($queryAlumno);

        // mostra la data de recibo
        echo "
            <input type='hidden' id='id_recibo_email' name='id_recibo_email' value='".$id_recibo."'>
            <input type='hidden' id='email_alumno' name='email_alumno' value='".$rowAlumno['EMAIL']."'>
            <input type='hidden' id='telefono_alumno' name='telefono_alumno' value='".$rowAlumno['TELEFONO']."'>
            <input type='hidden' id='id_alumno_email' name='id_alumno_email' value='".$id_alumno."'>
            <table width='272' style='' cellspacing='0' cellpadding='0'>
                <tr>
                    <td style='text-align:center; font-weight: 900; font-size: 15px; font-family: Trebuchet MS, Arial, Helvetica, sans-serif;'>
                    ".$rowNegocio['NOMBRECOLEGIO']."
                    </td>
                </tr>
                <tr style='height:5px;text-align:center;'>
                    <td><img src='".$rowNegocio['logo_colegio']."' width='100'></td>
                </tr>
                <tr>
                    <td>
                        <table width='272' cellspacing='0' cellpadding='0'>
                            <tr>
                                <td style='text-align:center; font-size: 12px; font-family: Trebuchet MS, Arial, Helvetica, sans-serif;'>
                                Fecha ".$fechaFormateada."
                                </td>
                            </tr>
                            <tr style='height:2px;'>
                                <td></td>
                            </tr>
                            <tr>
                                <td style='text-align:center; font-size: 12px; font-family: Trebuchet MS, Arial, Helvetica, sans-serif;'>
                                GRACIAS POR SU PAGO !!!
                                </td>
                            </tr>
                            <tr style='height:15px;'>
                                <td></td>
                            </tr>
                            <tr>
                                <td style='text-align:center;  font-weight: 900; font-size: 12px; font-family: Trebuchet MS, Arial, Helvetica, sans-serif;'>
                                    Recibo No. ".$id_recibo."
                                </td>
                            </tr>
                            <tr style='height:15px;'>
                                <td></td>
                            </tr>
                            <tr>
                                <td style='text-align:Left; font-size: 12px; font-family: Trebuchet MS, Arial, Helvetica, sans-serif;'>
                                    Cajero ".$usuario."
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr style='height:5px;'>
                    <td></td>
                </tr>
                <tr>
                    <td style='text-align:left; font-weight: 900; font-size: 12px; font-family: Trebuchet MS, Arial, Helvetica, sans-serif;'>
                        ".$rowAlumno['NOMBREAPELLIDO']."
                    </td>
                </tr>
                <tr>
                    <td style='text-align:left;'>---------------------------------------------------</td>
                </tr>
                <tr>
                    <td>
                        <table width='272' cellspacing='0' cellpadding='0'>
                            <tr>
                                <td style='text-align:left; font-size: 12px; font-family: Trebuchet MS, Arial, Helvetica, sans-serif;' width='200'>
                                Concepto
                                </td>
                                <td style='text-align:center; font-size: 12px; font-family: Trebuchet MS, Arial, Helvetica, sans-serif;' width='100'>
                                Valor
                                </td>
                            </tr>
                            <tr>
                                <td colspan='4' style='text-align:left;'>---------------------------------------------------</td>
                            </tr>";

                            // detalles del recibo
                            $queryDetalleRecibo = mysqli_query($link, "SELECT RM.Id AS FACTURA, R.FechaPago AS FECHA, 
                                                                    CASE 
                                                                        WHEN R.IdAlumno = 0 THEN R.NombreApellido
                                                                        ELSE A.NOMBREAPELLIDO 
                                                                    END AS ALUMNO,
                                                                    A.CODIGO,
                                                                    CASE 
                                                                        WHEN CAP.Id = 3 OR COP.IdConcepto = 1  THEN
                                                                            CONCAT(CAP.Concepto, ' ', 
                                                                                CASE MONTH(MesReferencia)
                                                                                    WHEN 1 THEN 'ENERO'
                                                                                    WHEN 2 THEN 'FEBRERO'
                                                                                    WHEN 3 THEN 'MARZO'
                                                                                    WHEN 4 THEN 'ABRIL'
                                                                                    WHEN 5 THEN 'MAYO'
                                                                                    WHEN 6 THEN 'JUNIO'
                                                                                    WHEN 7 THEN 'JULIO'
                                                                                    WHEN 8 THEN 'AGOSTO'
                                                                                    WHEN 9 THEN 'SEPTIEMBRE'
                                                                                    WHEN 10 THEN 'OCTUBRE'
                                                                                    WHEN 11 THEN 'NOVIEMBRE'
                                                                                    WHEN 12 THEN 'DICIEMBRE'
                                                                                    ELSE ''
                                                                                END)
                                                                        WHEN CAP.Id = 6 THEN
                                                                            CONCAT(CAP.Concepto, ' ', 
                                                                                CASE MONTH(MesReferencia)
                                                                                    WHEN 1 THEN 'ENERO'
                                                                                    WHEN 2 THEN 'FEBRERO'
                                                                                    WHEN 3 THEN 'MARZO'
                                                                                    WHEN 4 THEN 'ABRIL'
                                                                                    WHEN 5 THEN 'MAYO'
                                                                                    WHEN 6 THEN 'JUNIO'
                                                                                    WHEN 7 THEN 'JULIO'
                                                                                    WHEN 8 THEN 'AGOSTO'
                                                                                    WHEN 9 THEN 'SEPTIEMBRE'
                                                                                    WHEN 10 THEN 'OCTUBRE'
                                                                                    WHEN 11 THEN 'NOVIEMBRE'
                                                                                    WHEN 12 THEN 'DICIEMBRE'
                                                                                    ELSE ''
                                                                                END)
                                                                        WHEN CAP.Id = 3 OR COP.IdConcepto = 7 THEN
                                                                            CONCAT(COP.Concepto, ' ', 
                                                                                CASE MONTH(MesReferencia)
                                                                                    WHEN 1 THEN 'ENERO'
                                                                                    WHEN 2 THEN 'FEBRERO'
                                                                                    WHEN 3 THEN 'MARZO'
                                                                                    WHEN 4 THEN 'ABRIL'
                                                                                    WHEN 5 THEN 'MAYO'
                                                                                    WHEN 6 THEN 'JUNIO'
                                                                                    WHEN 7 THEN 'JULIO'
                                                                                    WHEN 8 THEN 'AGOSTO'
                                                                                    WHEN 9 THEN 'SEPTIEMBRE'
                                                                                    WHEN 10 THEN 'OCTUBRE'
                                                                                    WHEN 11 THEN 'NOVIEMBRE'
                                                                                    WHEN 12 THEN 'DICIEMBRE'
                                                                                    ELSE ''
                                                                                END)
                                                                        ELSE
                                                                            CONCAT(CAP.Concepto, ' ', COP.Concepto)
                                                                    END AS CONCEPTO,
                                                                    R.VALORTOTAL AS MONTO,
                                                                    RM.MontoTotal AS TOTAL,
                                                                    RM.cajero AS CAJERO,
                                                                    (
                                                                        SELECT  DISTINCT TipoMoneda 
                                                                        FROM tbl_aranceles Ara 
                                                                        WHERE Ara.IdNivel = R.Nivel
                                                                        AND Ara.IdCategoria=R.IdCategoriaConcepto 
                                                                        AND Ara.IdConcepto = R.IdConcepto 
                                                                        AND Ara.Anio = R.Anio
                                                                    ) AS TIPOMONEDA
                                                                FROM tbl_recibomaestro RM
                                                                INNER JOIN tbl_recibo R ON RM.Id = R.IdReciboMaestro
                                                                INNER JOIN tbl_alumnos A ON A.IDALUMNO = R.IdAlumno
                                                                INNER JOIN tbl_categoriapago CAP ON CAP.Id = R.IdCategoriaConcepto
                                                                INNER JOIN tbl_conceptospago COP ON COP.IdConcepto = R.IdConcepto
                                                                WHERE RM.Id = $id_recibo
                                                                ORDER BY RM.Id DESC");
                            $totalRecibo = 0;
                            while($rowDetalle = mysqli_fetch_array($queryDetalleRecibo)){
                                
                                $tipoMoneda = $rowDetalle['TIPOMONEDA'];
                                $montoCordoba = $rowDetalle['MONTO'];
                                
                                if($rowDetalle['TIPOMONEDA'] == 'US$'){
                                    $tipoMoneda = 'C$';

                                    $queryTasa = mysqli_query($link,"SELECT * FROM tasa_cambio WHERE activo_tasa = 1");
                                    $rowTasa = mysqli_fetch_array($queryTasa);
                                    $tasa = $rowTasa['tasa'];

                                    $montoCordoba = $rowDetalle['MONTO'] * $tasa;
                                }

                                echo "
                                <tr>
                                    <td style='text-align:left; font-size: 12px; font-family: Trebuchet MS, Arial, Helvetica, sans-serif;' width='200'>
                                        ".$rowDetalle['CONCEPTO']." ".$tipoMoneda."
                                    </td>
                                    <td style='text-align:right; font-size: 12px; font-family: Trebuchet MS, Arial, Helvetica, sans-serif;' width='100'>
                                        ".$montoCordoba."
                                    </td>
                                </tr>
                                <tr style='height:10px;'>
                                    <td></td>
                                </tr>";
                                $totalRecibo += $montoCordoba;
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
                    <td style='text-align: left;'>---------------------------------------------------</td>
                </tr>
                <tr>
                    <td>
                        <table width='272' cellspacing='0' cellpadding='0'>
                            <tr style='text-align: right;'>
                                <td style='font-size: 12px; font-family: Trebuchet MS, Arial, Helvetica, sans-serif; text-align: right;' width='200'>
                                Total C$:
                                </td>
                                <td style='font-size: 12px; font-family: Trebuchet MS, Arial, Helvetica, sans-serif; text-align: center;' width='100'>
                                ".$totalRecibo."
                                </td>
                            </tr>
                        </table>
                        <tr style='height:35px;'>
                            <td></td>
                        </tr>
                        <tr>
                            <td style='text-align:center;'>Este RECIBO no necesita</td>
                        </tr>
                        <tr style='height:2px;'>
                            <td></td>
                        </tr>
                        <tr>
                            <td style='text-align:center;'>sello ni firma del cajero</td>
                        </tr>
                    </td>
                </tr>
            </table>";
	} else {
		echo "mal";
	}	
?>