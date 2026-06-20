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
 
$id_alumno = $_GET['id_alumno'];
$id_anio = $_GET['id_anio'];

// DB table to use
$table = <<<EOT
 ( 
    SELECT R.IdReciboMaestro AS NoFACTURA,
       R.FechaPago AS FECHAPAGO,
       A.NOMBREAPELLIDO AS NombreApellido,
       CASE WHEN CAP.Id = 3 THEN CONCAT(CAP.Concepto, ' ', 
            CASE MONTH(R.MesReferencia)
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
       ELSE CONCAT(CAP.Concepto, ' ', COP.Concepto)
       END AS CONCEPTO,
       R.ValorTotal,
       (SELECT TIPOMONEDA
        FROM tbl_aranceles Ara
        WHERE Ara.IdNivel = R.Nivel 
        AND Ara.IdCategoria = R.IdCategoriaConcepto 
        AND Ara.IdConcepto = R.IdConcepto 
        AND Ara.Anio = R.Anio) AS TIPOMONEDA
FROM tbl_recibo R
INNER JOIN tbl_categoriapago CAP ON CAP.Id = R.IdCategoriaConcepto
INNER JOIN tbl_conceptospago COP ON COP.IdConcepto = R.IdConcepto
INNER JOIN tbl_alumnos A ON A.IDALUMNO = R.IDALUMNO
WHERE R.Anulado = 0 AND R.ANIO = '$id_anio' AND R.IdAlumno = '$id_alumno'
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
    array( 'db' => 'FECHAPAGO',     'dt' => 1,
        'formatter' => function( $d, $row ) {
            $fecha_objeto = new DateTime($d);
            $resultado = $fecha_objeto->format('d/m/Y');
            return $resultado;
        } 
    ),
    array( 'db' => 'CONCEPTO',      'dt' => 2 ),
    array( 'db' => 'ValorTotal',    'dt' => 3 )

);
 
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */
 
require( 'ssp.class.php' );
 
echo json_encode(
    SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
);