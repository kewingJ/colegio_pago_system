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
    SELECT 
      a.Id            AS ReciboId,
      a.Fecha,
      a.Estado,
      b.NOMBREAPELLIDO,
      a.IdAlumno      AS IdAlumnoRecibo
      -- Si necesitas también el del maestro, puedes dejar solo uno:
      -- , b.IDALUMNO  AS IdAlumnoAlumno
    FROM tbl_recibomaestro a
    INNER JOIN tbl_alumnos b
      ON a.IdAlumno = b.IDALUMNO
 ) temp
EOT;

// Table's primary key
$primaryKey = 'ReciboId';

// Column mapping para DataTables
$columns = array(
    array( 'db' => 'ReciboId',       'dt' => 0 ),
    array( 'db' => 'ReciboId',       'dt' => 1 ),
    array( 'db' => 'NOMBREAPELLIDO', 'dt' => 2,
        'formatter' => function( $d, $row ) {
            return mb_convert_case(mb_strtolower($d), MB_CASE_TITLE, "UTF-8");
        }
    ),
    array( 'db' => 'Fecha',          'dt' => 3,
        'formatter' => function( $d, $row ) {
            try {
                $fecha_objeto = new DateTime($d);
                return $fecha_objeto->format('d/m/Y H:i:s');
            } catch (Exception $e) {
                return $d; // fallback si viene null/invalid
            }
        }
    ),
    array( 'db' => 'Estado',         'dt' => 4 ),
    array( 'db' => 'ReciboId',       'dt' => 5 ),
);
 
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */
 
require( 'ssp.class.php' );
 
echo json_encode(
    SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
);