<?php
    include_once '../includes/config.php';
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
 (
    SELECT a.IdConcepto, b.Concepto as categoria, a.Concepto as concepto, a.unidades 
    FROM tbl_conceptospago as a
    INNER JOIN tbl_categoriapago as b
    ON a.IdCategoria = b.Id
 ) temp
EOT;
 
// Table's primary key
$primaryKey = 'IdConcepto';
 
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
    array( 'db' => 'IdConcepto','dt' => 0 ),
    array( 'db' => 'categoria', 'dt' => 1 ),
    array( 'db' => 'concepto',  'dt' => 2 ),
    array( 'db' => 'unidades',  'dt' => 3 ),
    array( 'db' => 'IdConcepto','dt' => 4 )

);
 
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */
 
require( 'ssp.class.php' );
 
echo json_encode(
    SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
);