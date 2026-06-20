<?php
	session_start();
    include_once '../includes/config.php';
	include_once '../includes/security.php';

	if($_SESSION['activo'] == 1)
	{
        $id_usuario = $_SESSION['id_u'];

        //limpiar la tabla
        $query = mysqli_query($link,"TRUNCATE becadetalle") or die(mysqli_error($link));
        
        $query = mysqli_query($link,"TRUNCATE becamaestro") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE conabonos") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE menu") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE mnurol") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE mnurolmenu") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE mnuusuarios") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE placargos") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE plaempleados") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE planomina") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE planominadetalle") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE plaprestamo") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE plaprestamosdetalle") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE platipopago") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE tasa_cambio") or die(mysqli_error($link));

        // $query = mysqli_query($link,"DELETE FROM tbla_usuario WHERE id_usuario <> '$id_usuario'") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE tbl_alumnos") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE tbl_anios") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE tbl_aranceles") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE tbl_cargos") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE tbl_categoriapago") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE tbl_conceptospago") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE tbl_examenes") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE tbl_grados") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE tbl_horarioescolar") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE tbl_horas") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE tbl_inscripcion") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE tbl_materias") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE tbl_matricula") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE tbl_notas") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE tbl_ofertaacademica") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE tbl_pagosmensualidades") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE tbl_profesores") or die(mysqli_error($link));

        $query = mysqli_query($link,"UPDATE tbl_numeracionfactura SET Numero = 0 WHERE 1");

        $query = mysqli_query($link,"TRUNCATE tbl_recibo") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE tbl_recibomaestro") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE tbl_secciones") or die(mysqli_error($link));

        $query = mysqli_query($link,"TRUNCATE tbl_turno") or die(mysqli_error($link));

        $query = mysqli_query($link,"UPDATE tbl_parametros SET NOMBRECOLEGIO = Null,
                                                        logo_colegio = Null,
                                                        TELEFONOS = Null,
                                                        DIRECCION = Null") or die(mysqli_error($link));

        echo "bien";
	}
	else {
		echo "mal";
	}
?>