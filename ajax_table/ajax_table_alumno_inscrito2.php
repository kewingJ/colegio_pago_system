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

$ano_actual = date('Y');
 
// DB table to use
$table = <<<EOT
 (
    SELECT	I.ID,
			I.IDALUMNO,
			A.NOMBREAPELLIDO,
			N.Nivel,
			G.Grado,			
		    I.SECCION,
		    I.ANIOLECTIVO
	FROM   tbl_inscripcion I	       
	       INNER JOIN tbl_alumnos A
	            ON  I.IDALUMNO = A.IDALUMNO
	       INNER JOIN tbl_grados G
	            ON  G.IdGrados = I.IDGRADO
	       INNER JOIN tbl_nivel N
	            ON  N.IdNivel = I.IDNIVEL
    WHERE I.ANIOLECTIVO = $ano_actual
	ORDER BY
	       N.NIVEL,
	       G.GRADO,
	       A.NOMBREAPELLIDO
 ) temp
EOT;
 
// Table's primary key
$primaryKey = 'ID';
 
// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
    array( 'db' => 'IDALUMNO',      'dt' => 0 ),
    array( 'db' => 'NOMBREAPELLIDO',    'dt' => 1,
        'formatter' => function( $d, $row ) {
            $resultado = mb_convert_case(mb_strtolower($d), MB_CASE_TITLE, "UTF-8");
            return $resultado;
        } 
    ),
    array( 'db' => 'Nivel',         'dt' => 2 ),
    array( 'db' => 'Grado',         'dt' => 3 ),
    array( 'db' => 'SECCION',       'dt' => 4 ),
    array( 'db' => 'ANIOLECTIVO',   'dt' => 5 )

);
 
 
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */
 
require( 'ssp.class.php' );
 
echo json_encode(
    SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
);