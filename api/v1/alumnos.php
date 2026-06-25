<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../includes/config.php';
include_once '../../includes/Services.php';
include_once '../../includes/JWT.php';

// Verificar Token
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token = str_replace('Bearer ', '', $authHeader);

$userData = JWT::validate($token);

if (!$userData) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Acceso no autorizado o token inválido"]);
    exit;
}

// Procesar Consulta
$alumnoService = new AlumnoService($link);

try {
    $alumnos = $alumnoService->getListaCompletaAlumnos();

    echo json_encode([
        "status" => "success",
        "count" => count($alumnos),
        "data" => $alumnos
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error interno del servidor"]);
}
?>