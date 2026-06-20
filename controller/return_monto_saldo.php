<?php 
	function procesarPago($id_pagoMensual, $mes_pago, $link) 
    {
        $mes_pago = $mes_pago;
        $id_pagoMensual = $id_pagoMensual;
        //obtener los datos del pago
        $queryUno = mysqli_query($link,"SELECT * FROM tbl_pagosmensualidades WHERE Id = '$id_pagoMensual'");
        $rowDatosUno = mysqli_fetch_array($queryUno);
        $IdInscripcion  = $rowDatosUno['IdInscripcion'];
        // 
        $queryDos = mysqli_query($link,"SELECT * FROM tbl_inscripcion WHERE ID = '$IdInscripcion'");
        $rowDatosDos = mysqli_fetch_array($queryDos);


		$id_anio        = $rowDatosDos['ANIOLECTIVO'];
        $id_alumno      = $rowDatosDos['IDALUMNO'];
        $id_concepto    = 1;
        $mes            = $mes_pago;
        $id_nivel       = $rowDatosDos['IDNIVEL'];
        $id_categoria   = 3;

        // Convertir la fecha al formato estándar YYYY-MM-DD
        $fecha_formato_estandar = DateTime::createFromFormat('d/m/Y', $mes)->format('Y-m-d');
        $numeroMes = date('m', strtotime($fecha_formato_estandar));
        $saldo = "";

        // verificar si hay recibo y su estado
        $anulado = 1;
        $queryDataRecibo = mysqli_query($link,"SELECT * FROM tbl_recibo 
                                INNER JOIN tbl_categoriapago cpa ON tbl_recibo.IdCategoriaConcepto = cpa.Id
                                WHERE Anio = '$id_anio'
                                AND IdAlumno = '$id_alumno' 
                                AND cpa.Concepto = 'MENSUALIDAD'
                                AND MONTH(MesReferencia) = '$numeroMes'
                                AND Anulado = 0");
        $rowDataRecibo = mysqli_fetch_array($queryDataRecibo);
        if ($rowDataRecibo) {
            $anulado = $rowDataRecibo['Anulado'];
        }

        $query = mysqli_query($link, "SELECT * FROM tbl_cargos AS ca
                                INNER JOIN tbl_conceptospago cp ON cp.IdConcepto = ca.IdConcepto
                                INNER JOIN tbl_categoriapago cpa ON cpa.Id = cp.IdCategoria
                                WHERE ca.AnioLectivo = '$id_anio' 
                                AND ca.IdAlumno = '$id_alumno' 
                                AND cpa.Concepto = 'MENSUALIDAD'
                                AND MONTH(ca.MesReferencia) = '$numeroMes'");
        $bandera = mysqli_num_rows($query);

        if($anulado == 1){
            $bandera = 0;
        }

        if($bandera > 0){
            $queryData = mysqli_query($link, "SELECT C.Saldo FROM tbl_cargos C
                                INNER JOIN tbl_conceptospago cp ON cp.IdConcepto = C.IdConcepto
                                INNER JOIN tbl_categoriapago cpa ON cpa.Id = cp.IdCategoria
                                WHERE C.AnioLectivo = '$id_anio' 
                                AND C.IdAlumno = '$id_alumno' 
                                AND cpa.Concepto = 'MENSUALIDAD'
                                AND	MONTH(C.MesReferencia) = '$numeroMes'");
            $rowData = mysqli_fetch_array($queryData);
            $saldo = $rowData['Saldo'];

            if($saldo < 0) {
                $saldo = "Pagado";
            }

            if($saldo == 0.00) {
                $saldo = "Pagado";
            }
        }
        
        return $saldo;
	}

    function procesarAvatar($id_alumno, $link) 
    {
        //obtener los datos del alumno
        $query = mysqli_query($link,"SELECT * FROM tbl_alumnos WHERE IDALUMNO = '$id_alumno'");
        $rowDatos = mysqli_fetch_array($query);
        $genero  = $rowDatos['GENERO'];
        $foto    = $rowDatos['FOTO'];
        $imagen = "";
        if(empty($foto)) {
            switch ($genero) {
                case 'male':
                    $imagen = "img/avatar_m.png";
                    break;
                case 'female':
                    $imagen = "img/avatar_f.png";
                    break;
                default:
                    $imagen = "img/avatar_m.png";
                    break;
            }
        } else {
            $imagen = $foto;
        } 

        return $imagen;
    }
?>