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
    SELECT a.ID, b.Concepto as categoria, d.Nivel, c.Concepto as pago, a.TipoMoneda, 
    a.Monto, CONCAT('AÑO LECTIVO : ', a.Anio) as Anio 
    FROM tbl_aranceles as a 
    INNER JOIN tbl_categoriapago as b 
    ON a.IdCategoria = b.Id 
    INNER JOIN tbl_conceptospago as c 
    ON a.IdConcepto = c.IdConcepto 
    INNER JOIN tbl_nivel as d 
    ON a.IdNivel = d.IdNivel
    ORDER BY a.Anio DESC
 ) temp
EOT;
 
// Table's primary key
$primaryKey = 'ID';
 
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
    array( 'db' => 'ID',        'dt' => 0 ),
    array( 'db' => 'categoria', 'dt' => 1 ),
    array( 'db' => 'Nivel',     'dt' => 2 ),
    array( 'db' => 'pago',      'dt' => 3 ),
    array( 'db' => 'TipoMoneda','dt' => 4 ),
    array( 'db' => 'Monto',     'dt' => 5 ),
    array( 'db' => 'Anio',      'dt' => 6 ),
    array( 'db' => 'ID',        'dt' => 7 ),

);
 
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */
 
require( 'ssp.class.php' );
 
echo json_encode(
    SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
);