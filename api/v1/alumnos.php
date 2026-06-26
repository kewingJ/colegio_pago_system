<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../includes/config.php';
include_once '../../includes/Services.php';
include_once '../../includes/JWT.php';

// Obtener el Header de Autorización de forma robusta
$authHeader = '';
if (function_exists('apache_request_headers')) {
    $headers = apache_request_headers();
    foreach ($headers as $key => $value) {
        if (strcasecmp($key, 'Authorization') === 0) {
            $authHeader = $value;
            break;
        }
    }
}

if (empty($authHeader)) {
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }
}

$token = preg_replace('/^Bearer\s+/i', '', $authHeader);

$userData = JWT::validate($token);

// Verificar si el token existe y está activo en la base de datos
$stmtToken = mysqli_prepare($link, "SELECT id FROM tbl_api_tokens WHERE token = ? AND activo = 1");
mysqli_stmt_bind_param($stmtToken, "s", $token);
mysqli_stmt_execute($stmtToken);
$resToken = mysqli_stmt_get_result($stmtToken);

if (!$userData || mysqli_num_rows($resToken) === 0) {
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