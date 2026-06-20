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
    SELECT a.IdRecibo,
        b.IDALUMNO,
        b.NOMBREAPELLIDO, 
        a.Anio, 
        MONTH(a.MesReferencia) mes,
        d.Concepto,
        a.subtotal
    FROM tbl_recibo a
    INNER JOIN tbl_alumnos b
    ON a.IdAlumno = b.IDALUMNO
    INNER JOIN tbl_categoriapago c
    ON a.IdCategoriaConcepto = c.Id
    INNER JOIN tbl_conceptospago d
    ON a.IdConcepto = d.IdConcepto
    WHERE c.Concepto = 'TRANSPORTE'
 ) temp
EOT;
 
// Table's primary key
$primaryKey = 'IdRecibo';
 
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
    array( 'db' => 'IdRecibo',          'dt' => 0 ),
    array( 'db' => 'NOMBREAPELLIDO',    'dt' => 1,
        'formatter' => function( $d, $row ) {
            $resultado = mb_convert_case(mb_strtolower($d), MB_CASE_TITLE, "UTF-8");
            return $resultado;
        } 
    ),
    array( 'db' => 'Anio',      'dt' => 2 ),
    array( 'db' => 'mes',       'dt' => 3,
        'formatter' => function( $d, $row ) {
            $resultado = "";
            $mes_referencia = $d;
            $meses_en_espanol = array(
                1 => 'enero',
                2 => 'febrero',
                3 => 'marzo',
                4 => 'abril',
                5 => 'mayo',
                6 => 'junio',
                7 => 'julio',
                8 => 'agosto',
                9 => 'septiembre',
                10 => 'octubre',
                11 => 'noviembre',
                12 => 'diciembre'
            );
            $resultado = $meses_en_espanol[$mes_referencia];
            return ucfirst($resultado);
        } 
    ),
    array( 'db' => 'Concepto',       'dt' => 4,
        'formatter' => function( $d, $row ) {
            $resultado = mb_convert_case(mb_strtolower($d), MB_CASE_TITLE, "UTF-8");
            return $resultado;
        } 
    ),
    array( 'db' => 'subtotal',     'dt' => 5 ),
    array( 'db' => 'IDALUMNO',     'dt' => 6 ),
);
 
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */
 
require( 'ssp.class.php' );
 
echo json_encode(
    SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
);