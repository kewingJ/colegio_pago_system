<?php
include_once '../includes/config.php';
date_default_timezone_set('America/Managua');

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Año y mes actuales (o usa ?year_lectivo=YYYY si lo pasas por GET)
if(empty($_GET['year_lectivo'])){
    $ano_actual = (int) date('Y');
} else {
    $ano_actual = (int) $_GET['year_lectivo'];
}
$mes_actual     = (int) date('n'); // 1..12
$nivel_escolar  = $_GET['nivel_escolar'] ?? '';
$grado          = $_GET['grado']   ?? '';
$seccion        = $_GET['seccion'] ?? '';
$q              = $_GET['q']       ?? '';

// Helper meses
$map = [
  1 => ['Ene','Enero'],
  2 => ['Feb','Febrero'],
  3 => ['Mar','Marzo'],
  4 => ['Abr','Abril'],
  5 => ['May','Mayo'],
  6 => ['Jun','Junio'],
  7 => ['Jul','Julio'],
  8 => ['Ago','Agosto'],
  9 => ['Sep','Septiembre'],
  10 => ['Oct','Octubre'],
  11 => ['Nov','Noviembre'],
  12 => ['Dic','Diciembre'],
];

// ========== 1) Trae inscripciones/alumnos del año lectivo ==========
$sqlBase = "
  SELECT b.ID AS IdInscripcion, c.NOMBREAPELLIDO, b.IDNIVEL AS IdNivel
  FROM tbl_matricula a
  INNER JOIN tbl_inscripcion b ON a.ID = b.IDMATRICULA
  INNER JOIN tbl_alumnos c     ON a.IDALUMNO = c.IDALUMNO
  WHERE a.ANIO = ?
";
$params = [];
$types  = 'i';           // a.ANIO = ?  -> entero
$params[] = $ano_actual;

// Filtros opcionales (asumiendo que son IDs numéricos)
if ($nivel_escolar !== '' && ctype_digit((string)$nivel_escolar)) {
    $sqlBase .= " AND b.IDNIVEL = ? ";
    $types   .= 'i';
    $params[] = (int)$nivel_escolar;
}

if ($grado !== '' && ctype_digit((string)$grado)) {
    // AJUSTA el nombre de columna si en tu esquema es distinto
    $sqlBase .= " AND b.IDGRADO = ? ";
    $types   .= 'i';
    $params[] = (int)$grado;
}

if ($seccion !== '') {
    // Normalizamos: quitamos espacios y comparamos en mayúsculas
    $sqlBase .= " AND UPPER(TRIM(b.SECCION)) = UPPER(TRIM(?)) ";
    $types   .= 's';
    $params[] = $seccion;
}

if ($q !== '') {
    // Usamos LIKE insensible a mayúsculas: normalizamos ambos lados
    $sqlBase .= " AND UPPER(c.NOMBREAPELLIDO) LIKE UPPER(CONCAT('%', ?, '%')) ";
    $types   .= 's';
    $params[] = $q;
}

$stmt = $link->prepare($sqlBase);

// bind dinámico
$stmt->bind_param($types, ...$params);

$stmt->execute();
$res = $stmt->get_result();

$deudores = [];
$total_alumnos_mora = 0;
$deuda_total = 0.00;

// Prepara consultas reusables
$sqlPagos = "SELECT * FROM tbl_pagosmensualidades WHERE IdInscripcion = ? LIMIT 1";
$stmtPagos = $link->prepare($sqlPagos);

$sqlMonto = "
  SELECT a.Monto, a.TIPOMONEDA
  FROM tbl_aranceles a
  INNER JOIN tbl_categoriapago b ON a.IdCategoria = b.Id
  WHERE b.Concepto = 'MENSUALIDAD' AND a.IdNivel = ? AND a.Anio = ?
  LIMIT 1
";
$stmtMonto = $link->prepare($sqlMonto);

while ($row = $res->fetch_assoc()) {
    $idInscripcion = (int) $row['IdInscripcion'];
    $nombreRaw     = (string) $row['NOMBREAPELLIDO'];
    $idNivel       = (int) $row['IdNivel'];

    // 2) Trae fila de pagos mensualidades
    $stmtPagos->bind_param('i', $idInscripcion);
    $stmtPagos->execute();
    $resPagos = $stmtPagos->get_result();
    $filaPagos = $resPagos->fetch_assoc(); // puede ser null si no existe

    // 3) Recorre meses hasta el mes actual y cuenta mora
    $total_mes_mora = 0;
    $meses_mora = [];

    for ($i = 1; $i <= $mes_actual; $i++) {
        [$abbr, $full] = $map[$i];
        $valor = $filaPagos[$abbr] ?? null; // si no existe, lo tratamos como NO pagado
        if ($valor !== 'X') {
            $total_mes_mora++;
            $meses_mora[] = $full;
        }
    }

    if ($total_mes_mora > 0) {
        // 4) Monto mensual segun nivel/año
        $stmtMonto->bind_param('ii', $idNivel, $ano_actual);
        $stmtMonto->execute();
        $resMonto = $stmtMonto->get_result();
        $filaMonto = $resMonto->fetch_assoc();

        $montoMensual = $filaMonto ? (float)$filaMonto['Monto'] : 0.0;
        $tipomoneda   = $filaMonto ? (string)$filaMonto['TIPOMONEDA'] : '';
        $prefijo = 'C$';
        if ($tipomoneda) {
            $tipUpper = strtoupper(trim($tipomoneda));
            if ($tipUpper === 'USD' || $tipUpper === 'DOLAR' || $tipUpper === 'DÓLAR') {
                $prefijo = '$';
            }
        }

        // Total adeudado por el alumno
        $monto_total = $montoMensual * $total_mes_mora;
        $deuda_total += $monto_total;

        // Formato nombre con mayúscula inicial
        $nombreFmt = mb_convert_case(mb_strtolower($nombreRaw, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');

        $deudores[] = [
            'nombre'      => $nombreFmt,
            'meses_lista' => implode(', ', $meses_mora), // "Enero, Febrero, ..."
            'meses_cnt'   => $total_mes_mora,           // 3
            'monto_total' => $monto_total,              // numérico
            'prefijo'     => $prefijo,                  // "C$" | "$"
        ];

        $total_alumnos_mora++;
    }
}

$stmtMonto->close();
$stmtPagos->close();
$stmt->close();

$deuda_total = number_format($deuda_total, 2, '.', ',');

// Devuelve NÚMERO (no formateado)
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['totalFiltrado' => $deuda_total], JSON_UNESCAPED_UNICODE);
