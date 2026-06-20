<?php
include_once '../includes/config.php';

// Sanitiza entradas (pueden venir vacías)
$anio      = $_GET['anio']      ?? '';
$nivel     = $_GET['nivel']     ?? '';
$categoria = $_GET['categoria'] ?? '';
$concepto  = $_GET['concepto']  ?? '';
$mes       = $_GET['mes']       ?? ''; // numérico 1..12 (como tu select #id_mes)

// Base (misma FROM/WHERE que tu SELECT principal)
$sql = "
  SELECT SUM(R.ValorTotal) AS total
  FROM tbl_recibo R
  INNER JOIN tbl_categoriapago CAP ON CAP.Id = R.IdCategoriaConcepto
  INNER JOIN tbl_conceptospago COP ON COP.IdConcepto = R.IdConcepto
  INNER JOIN tbl_alumnos A ON A.IDALUMNO = R.IDALUMNO
  INNER JOIN tbl_nivel NI ON NI.IdNivel = R.Nivel
  WHERE R.Anulado = 0
";

// Aplica filtros SOLO si vienen
$params = [];
if ($anio !== '') {
  $sql .= " AND R.Anio = ? ";
  $params[] = $anio;
}
if ($nivel !== '') {
  $sql .= " AND NI.Nivel = ? ";
  $params[] = $nivel;
}
if ($categoria !== '') {
  $sql .= " AND CAP.Concepto = ? ";
  $params[] = $categoria;
}
if ($concepto !== '') {
  $sql .= " AND COP.Concepto = ? ";
  $params[] = $concepto;
}
if ($mes !== '') {
  // tu tabla tiene MesReferencia numérico (1..12)
  $sql .= " AND MONTH(R.MesReferencia) = ? ";
  $params[] = $mes;
}

// Ejecuta con prepared statements (mysqli)
$stmt = mysqli_prepare($link, $sql);
if (!empty($params)) {
  // arma tipos (todo string salvo mes/anio podrían ser numéricos; "s" también funciona)
  $types = str_repeat('s', count($params));
  mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

$total = (float)($row['total'] ?? 0);

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['totalFiltrado' => $total]);
