<?php 
	include_once '../includes/config.php';
    include_once '../includes/security.php';
    
    session_start();
    $id = $_SESSION['id_usuario'];

	if (isset($_POST['id_anio'])) 
	{
		$id_anio        = $_POST['id_anio'];
        $id_alumno      = $_POST['id_alumno'];
        $id_concepto    = $_POST['id_concepto'];
        $mes            = $_POST['mes'];
        $id_nivel       = $_POST['id_nivel'];
        $id_categoria   = $_POST['id_categoria'];

        // Convertir la fecha al formato estándar YYYY-MM-DD
        $fecha_formato_estandar = DateTime::createFromFormat('d/m/Y', $mes)->format('Y-m-d');
        $numeroMes = date('m', strtotime($fecha_formato_estandar));
        $saldo = 0.00;

        // verificar si hay recibo y su estado
        $anulado = 1;
        $queryDataRecibo = mysqli_query($link,"SELECT * FROM tbl_recibo 
                                WHERE Anio = '$id_anio'
                                AND IdAlumno = '$id_alumno' 
                                AND IdCategoriaConcepto = '$id_categoria'
                                AND IdConcepto = '$id_concepto'
                                AND MONTH(MesReferencia) = '$numeroMes'
                                AND Anulado = 0");
        $rowDataRecibo = mysqli_fetch_array($queryDataRecibo);
        if ($rowDataRecibo) {
            $anulado = $rowDataRecibo['Anulado'];
        }

        $query = mysqli_query($link, "SELECT * FROM tbl_cargos 
                                WHERE AnioLectivo = '$id_anio' 
                                AND IdAlumno = '$id_alumno' 
                                AND IdConcepto = '$id_concepto' 
                                AND MONTH(MesReferencia) = '$numeroMes'");
        $bandera = mysqli_num_rows($query);

        if($anulado == 1){
            $bandera = 0;
        }
        
        if($bandera > 0){
            $queryData = mysqli_query($link, "SELECT C.Saldo FROM tbl_cargos C
                                WHERE C.AnioLectivo = '$id_anio' 
                                AND C.IdAlumno = '$id_alumno' 
                                AND C.IdConcepto = '$id_concepto' 
                                AND	MONTH(C.MesReferencia) = '$numeroMes'");
            $rowData = mysqli_fetch_array($queryData);
            $saldo = $rowData['Saldo'];
        } else {
            $queryData = mysqli_query($link, "SELECT Monto FROM tbl_aranceles 
                                WHERE IdCategoria = '$id_categoria'  
                                AND IdConcepto = '$id_concepto' 
                                AND IdNivel = '$id_nivel'
                                AND Anio = '$id_anio'");
            $rowData = mysqli_fetch_array($queryData);
            if ($rowData) {
                $saldo = $rowData['Monto'];
            } else {
                $saldo = 'no tiene';
            }
        }
        
        echo $saldo;
	}
?>