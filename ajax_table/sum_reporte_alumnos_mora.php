<?php
include_once '../includes/config.php';
date_default_timezone_set('America/Managua');

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Año y mes actuales (o usa ?anio=YYYY si lo pasas por GET)
$ano_actual = isset($_GET['anio']) ? (int) $_GET['anio'] : (int) date('Y');
$mes_actual = (int) date('n'); // 1..12

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
$stmt = $link->prepare($sqlBase);
$stmt->bind_param('i', $ano_actual);
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
