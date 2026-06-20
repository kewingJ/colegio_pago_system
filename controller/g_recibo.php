<?php
	session_start();
    $id_usuario = $_SESSION['id_usuario'];
    $nombre     = $_SESSION['nombre'];
    $apellido   = $_SESSION['apellido'];

    error_reporting(E_ALL);
    ini_set('display_errors', '1');

	include_once '../includes/config.php';
	include_once '../includes/security.php';
    date_default_timezone_set('America/Managua');

	if (!empty($_POST['id_alumno']) &&
        !empty($_POST['id_anio'])) {
			
		//
		$id_alumno = clean(mysqli_real_escape_string($link,$_POST['id_alumno']));
        $id_anio = clean(mysqli_real_escape_string($link,$_POST['id_anio']));
        $total_factura = clean(mysqli_real_escape_string($link,$_POST['total_factura']));
        $fecha_actual = date("Y-m-d H:i:s");
        $usuario = $nombre.' '.$apellido;
        //
        $observacion = "Pago realizado por Id usuario: ".$id_usuario;

        // guardamos el recibo maestro
        $queryReciboMaestro = mysqli_query($link,"INSERT INTO tbl_recibomaestro VALUES (0,'$fecha_actual', 1, '$total_factura', '$id_alumno', '$observacion', '$usuario')") or die(mysqli_error($link));
        $id_recibo = mysqli_insert_id($link);
        
        // actualizamos el id del recibo
        $queryFactura = mysqli_query($link,"UPDATE tbl_numeracionfactura SET Numero = '$id_recibo' WHERE 1") or die(mysqli_error($link));
        
        // guardamos el detalle de recibo
        $items = $_POST['items']; 

        foreach ($items as $item) {
            // 
            $hora_actual = date("H:i:s");
            $fechaOriginal = DateTime::createFromFormat('d/m/Y', $item['mes_referencia'])->format('Y-m-d');
            $mes_referencia = $fechaOriginal.' '.$hora_actual;

            // 
            $monto = $item['monto'];
            $id_nivel = $item['id_nivel'];
            $id_categoria = $item['id_categoria'];
            $id_concepto = $item['id_concepto'];

            $queryRecibo = mysqli_query($link,"INSERT INTO tbl_recibo VALUES (0,'$id_recibo', '$fecha_actual', '$mes_referencia', '$id_anio', '$id_alumno', '', '$id_nivel',
            '', '', 0, '$id_concepto', '', '$id_categoria', '', '$monto', 0, '$monto', '$monto', '$monto', '$monto', '', 0, 'I', '')") or die(mysqli_error($link));

            // verificar si en la tabla cargos ya esta todo pagado
            $fecha_formato_estandar = DateTime::createFromFormat('d/m/Y', $item['mes_referencia'])->format('Y-m-d');
            $numeroMes = date('m', strtotime($fecha_formato_estandar));
            $saldo = 0;

            $queryConsulta = mysqli_query($link, "SELECT * FROM tbl_cargos 
                                    WHERE AnioLectivo = '$id_anio' 
                                    AND IdAlumno = '$id_alumno' 
                                    AND IdConcepto = '$id_concepto' 
                                    AND MONTH(MesReferencia) = '$numeroMes'");
            $bandera = mysqli_num_rows($queryConsulta);
            if ($bandera > 0) {
                $rowConsulta = mysqli_fetch_array($queryConsulta);
                if($rowConsulta['Saldo'] > 0) {
                    $saldo = $rowConsulta['Saldo'] - $monto;
                    $id_cargo =  $rowConsulta['Id'];
                    // actualizar saldo
                    $queryUpdate = mysqli_query($link,"UPDATE tbl_cargos SET Saldo = '$saldo', FechaModificacion = '$fecha_actual' WHERE Id = '$id_cargo'") or die(mysqli_error($link));
                }
            } else {
                $queryConsultaDos = mysqli_query($link, "SELECT * FROM tbl_aranceles 
                                    WHERE IdCategoria = '$id_categoria' 
                                    AND IdConcepto = '$id_concepto' 
                                    AND IdNivel = '$id_nivel' 
                                    AND Anio = '$id_anio'");
                $rowConsultaDos = mysqli_fetch_array($queryConsultaDos);
                if($rowConsultaDos){
                    $montoArancel = $rowConsultaDos['Monto'];
                    $saldo = $montoArancel - $monto;
                } else {
                    $montoArancel = 0;
                    $saldo = 0;
                }
                // crear nuevo registro
                $queryReciboMaestro = mysqli_query($link,"INSERT INTO tbl_cargos VALUES (0,'$id_alumno', '$id_anio', '$id_nivel', NULL, NULL, '$id_concepto', '$mes_referencia', '$montoArancel', '$saldo', '$fecha_actual', NULL, NULL, 0)") or die(mysqli_error($link));
            }

            // Creamos la matricula del alumno si se pago matricula
            $queryData = mysqli_query($link,"SELECT * FROM tbl_categoriapago WHERE Id = '$id_categoria'");
            $rowData = mysqli_fetch_array($queryData);
            $categoria = $rowData['Concepto'];
            if($categoria == "MATRICULA" || $categoria == "PRE-MATRICULA" || $categoria == "PAGO MATRICULA"){
                $queryConsultaTres = mysqli_query($link, "SELECT * FROM tbl_matricula 
                                                WHERE IDALUMNO = '$id_alumno' 
                                                AND ANIO = '$id_anio'");
                $banderaMatricula = mysqli_num_rows($queryConsultaTres);
                if($banderaMatricula == 0){
                    $queryMatricula = mysqli_query($link,"INSERT INTO tbl_matricula VALUES (0,'$id_alumno', '$id_anio')") or die(mysqli_error($link));
                }
            }
        }

        // Creamos la matricula del alumno
        // $queryConsultaTres = mysqli_query($link, "SELECT * FROM tbl_matricula 
        //                             WHERE IDALUMNO = '$id_alumno' 
        //                             AND ANIO = '$id_anio'");
        // $banderaMatricula = mysqli_num_rows($queryConsultaTres);
        // if($banderaMatricula == 0){
        //     $queryMatricula = mysqli_query($link,"INSERT INTO tbl_matricula VALUES (0,'$id_alumno', '$id_anio')") or die(mysqli_error($link));
        // }

        // Verificar si el alumno esta inscrito
        $queryConsultaCuatro = mysqli_query($link, "SELECT * FROM tbl_inscripcion 
                                    WHERE ANIOLECTIVO = '$id_anio' 
                                    AND IDALUMNO = '$id_alumno'");
        $banderaInscripcion = mysqli_num_rows($queryConsultaCuatro);

        if($banderaInscripcion > 0){
            $rowConsultaCuatro = mysqli_fetch_array($queryConsultaCuatro);
            $id_inscripcion = $rowConsultaCuatro['ID'];

            // actualizar historial de pago si tienes pagos efectuados
            $queryConsultaDos = mysqli_query($link, "SELECT * FROM tbl_cargos AS ca 
                                    INNER JOIN tbl_conceptospago cp ON cp.IdConcepto = ca.IdConcepto
                                    INNER JOIN tbl_categoriapago cpa ON cp.IdCategoria = cpa.Id
                                    WHERE ca.IdAlumno  = '$id_alumno' 
                                    AND ca.AnioLectivo = '$id_anio'
                                    AND cpa.Concepto = 'MENSUALIDAD'");
            while($rowConsultaDos = mysqli_fetch_array($queryConsultaDos)){
                $mes_referencia = $rowConsultaDos['MesReferencia'];
                $fecha = new DateTime($mes_referencia);
                // Obtener el número del mes
                $numeroMes = (int)$fecha->format('m');
                $mes = "";
                switch ($numeroMes) {
                    case 1:
                        $mes = "Ene";
                        break;
                    case 2:
                        $mes = "Feb";
                        break;
                    case 3:
                        $mes = "Mar";
                        break;
                    case 4:
                        $mes = "Abr";
                        break;
                    case 5:
                        $mes = "May";
                        break;
                    case 6:
                        $mes = "Jun";
                        break;
                    case 7:
                        $mes = "Jul";
                        break;
                    case 8:
                        $mes = "Ago";
                        break;
                    case 9:
                        $mes = "Sep";
                        break;
                    case 10:
                        $mes = "Oct";
                        break;
                    case 11:
                        $mes = "Nov";
                        break;
                    case 12:
                        $mes = "Dic";
                        break;
                }

                // 
                $queryUpdatePago = mysqli_query($link,"UPDATE tbl_pagosmensualidades SET $mes = 'X' WHERE Anio = '$id_anio' AND IdInscripcion = '$id_inscripcion'") or die(mysqli_error($link));
            }
        }

        $queryNegocio = mysqli_query($link, "SELECT * FROM tbl_parametros");
        $rowNegocio = mysqli_fetch_array($queryNegocio);

        $fechaObj = date_create($fecha_actual);
        $fechaFormateada = date_format($fechaObj, 'd/m/Y H:i:s');

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
                                                                    R.ValorTotal AS MONTO,
                                                                    RM.MontoTotal AS TOTAL,
                                                                    RM.Cajero AS CAJERO,
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
                                echo "
                                <tr>
                                    <td style='text-align:left; font-size: 12px; font-family: Trebuchet MS, Arial, Helvetica, sans-serif;' width='200'>
                                        ".$rowDetalle['CONCEPTO']." ".$rowDetalle['TIPOMONEDA']."
                                    </td>
                                    <td style='text-align:right; font-size: 12px; font-family: Trebuchet MS, Arial, Helvetica, sans-serif;' width='100'>
                                        ".$rowDetalle['MONTO']."
                                    </td>
                                </tr>
                                <tr style='height:10px;'>
                                    <td></td>
                                </tr>";
                                $totalRecibo += $rowDetalle['MONTO'];
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