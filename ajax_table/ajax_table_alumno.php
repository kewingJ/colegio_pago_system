<?php
    include_once '../includes/config.php';
    include_once '../controller/return_monto_saldo.php';

    error_reporting(E_ALL);
    ini_set('display_errors', '1');
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
$table = 'tbl_alumnos';
 
// Table's primary key
$primaryKey = 'IDALUMNO';
 
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
    array( 'db' => 'IDALUMNO',          'dt' => 0 ),
    array( 'db' => 'FOTO',              'dt' => 1,
        'formatter' => function( $d, $row ) {
            global $link;
            $firstId = $row[0];
            $imagen = procesarAvatar($firstId, $link);
            return $imagen;
        } 
    ),
    array( 'db' => 'NOMBREAPELLIDO',    'dt' => 2,
        'formatter' => function( $d, $row ) {
            $resultado = mb_convert_case(mb_strtolower($d ?? ''), MB_CASE_TITLE, "UTF-8");
            return $resultado;
        } 
    ),
    array( 'db' => 'NOMBREPADRE',       'dt' => 3,
        'formatter' => function( $d, $row ) {
            $resultado = mb_convert_case(mb_strtolower($d ?? ''), MB_CASE_TITLE, "UTF-8");
            return $resultado;
        } 
    ),
    array( 'db' => 'NOMBREMADRE',       'dt' => 4,
        'formatter' => function( $d, $row ) {
            $resultado = mb_convert_case(mb_strtolower($d ?? ''), MB_CASE_TITLE, "UTF-8");
            return $resultado;
        } 
    ),
    array( 'db' => 'TELEFONO',          'dt' => 5),
    array( 'db' => 'IDALUMNO',          'dt' => 6),
);
 
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */
 
require( 'ssp.class.php' );
 
echo json_encode(
    SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
);