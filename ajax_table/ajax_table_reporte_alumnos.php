<?php
    include_once '../includes/config.php';
    include_once '../controller/return_monto_saldo.php';

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
     I.SECCION,
     I.IDALUMNO
FROM  tbl_pagosmensualidades PM
     INNER JOIN tbl_inscripcion I
          ON  PM.IdInscripcion = I.ID
     INNER JOIN tbl_alumnos A
          ON  A.IDALUMNO = I.IDALUMNO
 ) temp
EOT;

// Table's primary key
$primaryKey = 'Id';

$columns = array(
    array( 'db' => 'Id',            'dt' => 0 ),
    array( 'db' => 'NOMBREAPELLIDO',    'dt' => 1,
        'formatter' => function( $d, $row ) {
            return mb_convert_case(mb_strtolower($d), MB_CASE_TITLE, "UTF-8");
        }
    ),
    array( 'db' => 'ANIOLECTIVO',   'dt' => 2 ),
    array( 'db' => 'Ene', 'dt' => 3, 'formatter' => function($d, $row) { return formatPagoStatus($d, $row, '01'); } ),
    array( 'db' => 'Feb', 'dt' => 4, 'formatter' => function($d, $row) { return formatPagoStatus($d, $row, '02'); } ),
    array( 'db' => 'Mar', 'dt' => 5, 'formatter' => function($d, $row) { return formatPagoStatus($d, $row, '03'); } ),
    array( 'db' => 'Abr', 'dt' => 6, 'formatter' => function($d, $row) { return formatPagoStatus($d, $row, '04'); } ),
    array( 'db' => 'May', 'dt' => 7, 'formatter' => function($d, $row) { return formatPagoStatus($d, $row, '05'); } ),
    array( 'db' => 'Jun', 'dt' => 8, 'formatter' => function($d, $row) { return formatPagoStatus($d, $row, '06'); } ),
    array( 'db' => 'Jul', 'dt' => 9, 'formatter' => function($d, $row) { return formatPagoStatus($d, $row, '07'); } ),
    array( 'db' => 'Ago', 'dt' => 10, 'formatter' => function($d, $row) { return formatPagoStatus($d, $row, '08'); } ),
    array( 'db' => 'Sep', 'dt' => 11, 'formatter' => function($d, $row) { return formatPagoStatus($d, $row, '09'); } ),
    array( 'db' => 'Oct', 'dt' => 12, 'formatter' => function($d, $row) { return formatPagoStatus($d, $row, '10'); } ),
    array( 'db' => 'Nov', 'dt' => 13, 'formatter' => function($d, $row) { return formatPagoStatus($d, $row, '11'); } ),
    array( 'db' => 'Dic', 'dt' => 14, 'formatter' => function($d, $row) { return formatPagoStatus($d, $row, '12'); } ),
    array( 'db' => 'IDNIVEL',     'dt' => 15 ),
    array( 'db' => 'IDGRADO',     'dt' => 16 ),
    array( 'db' => 'SECCION',     'dt' => 17 ),
    array( 'db' => 'IDALUMNO',    'dt' => 18 )
);

// Cache para recibos y cargos para evitar miles de consultas
$pagoCache = [];

function formatPagoStatus($d, $row, $mesNum) {
    global $link, $pagoCache;
    if(empty($d)){
        return '<span class="badge badge-danger">No pagado</span>';
    }

    $id_anio = $row[2];
    $id_alumno = $row[18];
    $cacheKey = "{$id_alumno}_{$id_anio}_{$mesNum}";

    if (!isset($pagoCache[$cacheKey])) {
        // En lugar de llamar a procesarPago que hace muchas queries,
        // podríamos optimizar esto aún más, pero por ahora usemos el cache
        $pagoCache[$cacheKey] = procesarPago($row[0], "01/{$mesNum}/{$id_anio}", $link);
    }

    if($pagoCache[$cacheKey] == 'Pagado') {
        return '<span class="badge badge-success">Pagado</span>';
    } else {
        return '<span class="badge badge-warning">Pendiente</span>';
    }
}

require( 'ssp.class.php' );

echo json_encode(
    SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
);