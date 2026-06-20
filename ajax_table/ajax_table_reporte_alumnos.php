<?php
    include_once '../includes/config.php';
    include_once '../controller/return_monto_saldo.php';
/*
 * DataTables example server-side processing script.
 *
 * Please note that this script is intentionally extremely simply to show how
 * server-side processing can be implemented, and probably shouldn't be used as
 * the basis for a large complex system. It is suitable for simple use cases as
 * for learning.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */
 
// DB table to use
$table = <<<EOT
 ( SELECT PM.Id,
     A.NOMBREAPELLIDO,
     I.ANIOLECTIVO,
     PM.Ene,
     PM.Feb,
     PM.Mar,
     PM.Abr,
     PM.May,
     PM.Jun,
     PM.Jul,
     PM.Ago,
     PM.Sep,
     PM.Oct,
     PM.Nov,
     PM.Dic,
     I.IDNIVEL,
     I.IDGRADO,
     I.SECCION
FROM  tbl_pagosmensualidades PM
     INNER JOIN tbl_inscripcion I
          ON  PM.IdInscripcion = I.ID
     INNER JOIN tbl_alumnos A
          ON  A.IDALUMNO = I.IDALUMNO
     INNER JOIN tbl_nivel	N
	     ON N.IdNivel = I.IDNIVEL
	INNER JOIN tbl_grados G
		ON G.IdGrados = I.IDGRADO  
ORDER BY
     PM.Id DESC
 ) temp
EOT;
 
// Table's primary key
$primaryKey = 'Id';
 
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
    array( 'db' => 'Id',            'dt' => 0 ),
    array( 'db' => 'NOMBREAPELLIDO',    'dt' => 1,
        'formatter' => function( $d, $row ) {
            $resultado = mb_convert_case(mb_strtolower($d), MB_CASE_TITLE, "UTF-8");
            return $resultado;
        } 
    ),
    array( 'db' => 'ANIOLECTIVO',   'dt' => 2 ),
    array( 'db' => 'Ene',       'dt' => 3,
        'formatter' => function( $d, $row ) {
            global $link;
            $resultado = "";
            $firstId = $row[0];
            if(empty($d)){
                $resultado = '<span class="badge badge-danger">No pagado</span>';
            } else {
                $respuesta = procesarPago($firstId, '01/01/2024', $link);
                if($respuesta == 'Pagado') {
                    $resultado = '<span class="badge badge-success">Pagado</span>';
                } else {
                    $resultado = '<span class="badge badge-warning">Pendiente</span>';
                }
            }
            return $resultado;
        } 
    ),
    array( 'db' => 'Feb',       'dt' => 4,
        'formatter' => function( $d, $row ) {
            global $link;
            $resultado = "";
            $firstId = $row[0];
            if(empty($d)){
                $resultado = '<span class="badge badge-danger">No pagado</span>';
            } else {
                $respuesta = procesarPago($firstId, '01/02/2024', $link);
                if($respuesta == 'Pagado') {
                    $resultado = '<span class="badge badge-success">Pagado</span>';
                } else {
                    $resultado = '<span class="badge badge-warning">Pendiente</span>';
                }
            }
            return $resultado;
        } 
    ),
    array( 'db' => 'Mar',       'dt' => 5,
        'formatter' => function( $d, $row ) {
            global $link;
            $resultado = "";
            $firstId = $row[0];
            if(empty($d)){
                $resultado = '<span class="badge badge-danger">No pagado</span>';
            } else {
                $respuesta = procesarPago($firstId, '01/03/2024', $link);
                if($respuesta == 'Pagado') {
                    $resultado = '<span class="badge badge-success">Pagado</span>';
                } else {
                    $resultado = '<span class="badge badge-warning">Pendiente</span>';
                }
            }
            return $resultado;
        } 
    ),
    array( 'db' => 'Abr',       'dt' => 6,
        'formatter' => function( $d, $row ) {
            global $link;
            $resultado = "";
            $firstId = $row[0];
            if(empty($d)){
                $resultado = '<span class="badge badge-danger">No pagado</span>';
            } else {
                $respuesta = procesarPago($firstId, '01/04/2024', $link);
                if($respuesta == 'Pagado') {
                    $resultado = '<span class="badge badge-success">Pagado</span>';
                } else {
                    $resultado = '<span class="badge badge-warning">Pendiente</span>';
                }
            }
            return $resultado;
        } 
    ),
    array( 'db' => 'May',       'dt' => 7,
        'formatter' => function( $d, $row ) {
            global $link;
            $resultado = "";
            $firstId = $row[0];
            if(empty($d)){
                $resultado = '<span class="badge badge-danger">No pagado</span>';
            } else {
                $respuesta = procesarPago($firstId, '01/05/2024', $link);
                if($respuesta == 'Pagado') {
                    $resultado = '<span class="badge badge-success">Pagado</span>';
                } else {
                    $resultado = '<span class="badge badge-warning">Pendiente</span>';
                }
            }
            return $resultado;
        } 
    ),
    array( 'db' => 'Jun',       'dt' => 8,
        'formatter' => function( $d, $row ) {
            global $link;
            $resultado = "";
            $firstId = $row[0];
            if(empty($d)){
                $resultado = '<span class="badge badge-danger">No pagado</span>';
            } else {
                $respuesta = procesarPago($firstId, '01/06/2024', $link);
                if($respuesta == 'Pagado') {
                    $resultado = '<span class="badge badge-success">Pagado</span>';
                } else {
                    $resultado = '<span class="badge badge-warning">Pendiente</span>';
                }
            }
            return $resultado;
        } 
    ),
    array( 'db' => 'Jul',       'dt' => 9,
        'formatter' => function( $d, $row ) {
            global $link;
            $resultado = "";
            $firstId = $row[0];
            if(empty($d)){
                $resultado = '<span class="badge badge-danger">No pagado</span>';
            } else {
                $respuesta = procesarPago($firstId, '01/07/2024', $link);
                if($respuesta == 'Pagado') {
                    $resultado = '<span class="badge badge-success">Pagado</span>';
                } else {
                    $resultado = '<span class="badge badge-warning">Pendiente</span>';
                }
            }
            return $resultado;
        } 
    ),
    array( 'db' => 'Ago',       'dt' => 10,
        'formatter' => function( $d, $row ) {
            global $link;
            $resultado = "";
            $firstId = $row[0];
            if(empty($d)){
                $resultado = '<span class="badge badge-danger">No pagado</span>';
            } else {
                $respuesta = procesarPago($firstId, '01/08/2024', $link);
                if($respuesta == 'Pagado') {
                    $resultado = '<span class="badge badge-success">Pagado</span>';
                } else {
                    $resultado = '<span class="badge badge-warning">Pendiente</span>';
                }
            }
            return $resultado;
        } 
    ),
    array( 'db' => 'Sep',       'dt' => 11,
        'formatter' => function( $d, $row ) {
            global $link;
            $resultado = "";
            $firstId = $row[0];
            if(empty($d)){
                $resultado = '<span class="badge badge-danger">No pagado</span>';
            } else {
                $respuesta = procesarPago($firstId, '01/09/2024', $link);
                if($respuesta == 'Pagado') {
                    $resultado = '<span class="badge badge-success">Pagado</span>';
                } else {
                    $resultado = '<span class="badge badge-warning">Pendiente</span>';
                }
            }
            return $resultado;
        } 
    ),
    array( 'db' => 'Oct',       'dt' => 12,
        'formatter' => function( $d, $row ) {
            global $link;
            $resultado = "";
            $firstId = $row[0];
            if(empty($d)){
                $resultado = '<span class="badge badge-danger">No pagado</span>';
            } else {
                $respuesta = procesarPago($firstId, '01/10/2024', $link);
                if($respuesta == 'Pagado') {
                    $resultado = '<span class="badge badge-success">Pagado</span>';
                } else {
                    $resultado = '<span class="badge badge-warning">Pendiente</span>';
                }
            }
            return $resultado;
        } 
    ),
    array( 'db' => 'Nov',       'dt' => 13,
        'formatter' => function( $d, $row ) {
            global $link;
            $resultado = "";
            $firstId = $row[0];
            if(empty($d)){
                $resultado = '<span class="badge badge-danger">No pagado</span>';
            } else {
                $respuesta = procesarPago($firstId, '01/11/2024', $link);
                if($respuesta == 'Pagado') {
                    $resultado = '<span class="badge badge-success">Pagado</span>';
                } else {
                    $resultado = '<span class="badge badge-warning">Pendiente</span>';
                }
            }
            return $resultado;
        } 
    ),
    array( 'db' => 'Dic',       'dt' => 14,
        'formatter' => function( $d, $row ) {
            global $link;
            $resultado = "";
            $firstId = $row[0];
            if(empty($d)){
                $resultado = '<span class="badge badge-danger">No pagado</span>';
            } else {
                $respuesta = procesarPago($firstId, '01/12/2024', $link);
                if($respuesta == 'Pagado') {
                    $resultado = '<span class="badge badge-success">Pagado</span>';
                } else {
                    $resultado = '<span class="badge badge-warning">Pendiente</span>';
                }
            }
            return $resultado;
        } 
    ),
    array( 'db' => 'IDNIVEL',     'dt' => 15 ),
    array( 'db' => 'IDGRADO',     'dt' => 16 ),
    array( 'db' => 'SECCION',     'dt' => 17 )

);
 
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */
 
require( 'ssp.class.php' );
 
echo json_encode(
    SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
);