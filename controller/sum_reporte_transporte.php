<?php
include_once '../includes/config.php';
date_default_timezone_set('America/Managua');

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Parámetros de filtro (puedes ajustar según tus necesidades)
$ano_actual = isset($_GET['anio']) ? (int) $_GET['anio'] : '';
$id_alumno = isset($_GET['id_alumno']) ? (int) $_GET['id_alumno'] : '';

// Construir la cláusula WHERE dinámicamente
$where_clauses = [];
if (!empty($ano_actual)) {
    $where_clauses[] = "AND a.Anio = $ano_actual";
}
if (!empty($id_alumno)) {
    $where_clauses[] = "AND b.IDALUMNO = $id_alumno";
}
$where_sql = '';
if (!empty($where_clauses)) {
    $where_sql = ' ' . implode(' ', $where_clauses);
}

// consulta para obtener el total filtrado
$total_general_query = "
    SELECT SUM(a.subtotal) OVER() AS total_general
    FROM tbl_recibo a
    INNER JOIN tbl_alumnos b
    ON a.IdAlumno = b.IDALUMNO
    INNER JOIN tbl_categoriapago c
    ON a.IdCategoriaConcepto = c.Id
    INNER JOIN tbl_conceptospago d
    ON a.IdConcepto = d.IdConcepto
    WHERE c.Concepto = 'TRANSPORTE' $where_sql
";
$total_general_result = mysqli_query($link, $total_general_query);
$total_general = 0.00;
if ($total_general_result && mysqli_num_rows($total_general_result) > 0) {
    $row = mysqli_fetch_assoc($total_general_result);
    $total_general = $row['total_general'];
}

$deuda_total = number_format($total_general, 2, '.', ',');

// retornar el total filtrado
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['totalFiltrado' => $deuda_total]);
?>