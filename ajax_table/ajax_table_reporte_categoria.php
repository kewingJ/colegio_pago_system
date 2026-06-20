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
SELECT R.IdReciboMaestro AS NoFACTURA,
       R.Anio AS Anio,
       MONTH(R.MesReferencia) AS MesReferencia,
       A.NOMBREAPELLIDO AS NombreApellido,
       R.FechaPago AS FECHAPAGO,
       CAP.Concepto AS conceptoCategoria,
       COP.Concepto AS conceptoPago,
       R.ValorTotal,
       NI.Nivel AS Nivel
FROM tbl_recibo R
INNER JOIN tbl_categoriapago CAP ON CAP.Id = R.IdCategoriaConcepto
INNER JOIN tbl_conceptospago COP ON COP.IdConcepto = R.IdConcepto
INNER JOIN tbl_alumnos A ON A.IDALUMNO = R.IDALUMNO
INNER JOIN tbl_nivel NI ON NI.IdNivel = R.Nivel
WHERE R.Anulado = 0
 ) temp
EOT;
 
// Table's primary key
$primaryKey = 'NoFACTURA';
 
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
    array( 'db' => 'NoFACTURA',     'dt' => 0 ),
    array( 'db' => 'Anio',          'dt' => 1 ),
    array( 'db' => 'MesReferencia', 'dt' => 2,
        'formatter' => function( $d, $row ) {
            $resultado = "";
            $mes_referencia = $d;
            $meses_en_espanol = array(
                1 => 'Enero',
                2 => 'Febrero',
                3 => 'Marzo',
                4 => 'Abril',
                5 => 'Mayo',
                6 => 'Junio',
                7 => 'Julio',
                8 => 'Agosto',
                9 => 'Septiembre',
                10 => 'Octubre',
                11 => 'Noviembre',
                12 => 'Diciembre'
            );
            $resultado = $meses_en_espanol[$mes_referencia];
            return $resultado;
        } 
    ),
    array( 'db' => 'NombreApellido',    'dt' => 3,
        'formatter' => function( $d, $row ) {
            $resultado = mb_convert_case(mb_strtolower($d), MB_CASE_TITLE, "UTF-8");
            return $resultado;
        } 
    ),
    array( 'db' => 'FECHAPAGO',     'dt' => 4,
        'formatter' => function( $d, $row ) {
            $fecha_objeto = new DateTime($d);
            $resultado = $fecha_objeto->format('d/m/Y');
            return $resultado;
        } 
    ),
    array( 'db' => 'conceptoCategoria', 'dt' => 5 ),
    array( 'db' => 'conceptoPago',      'dt' => 6 ),
    array( 'db' => 'ValorTotal',        'dt' => 7 ),
    array( 'db' => 'Nivel',             'dt' => 8 )
);
 
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */
 
require( 'ssp.class.php' );
 
echo json_encode(
    SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
);