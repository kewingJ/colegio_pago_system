<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../includes/config.php';
include_once '../../includes/Services.php';
include_once '../../includes/JWT.php';

// Obtener el Header de Autorización
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

// Verificar token en DB
$stmtToken = mysqli_prepare($link, "SELECT id FROM tbl_api_tokens WHERE token = ? AND activo = 1");
mysqli_stmt_bind_param($stmtToken, "s", $token);
mysqli_stmt_execute($stmtToken);
$resToken = mysqli_stmt_get_result($stmtToken);

if (!$userData || mysqli_num_rows($resToken) === 0) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Acceso no autorizado"]);
    exit;
}

// Caching logic
$cacheDir = '../../includes/cache';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0777, true);
}
$cacheFile = $cacheDir . '/colegio.json';
$cacheTime = 3600; // 1 hora

if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
    echo file_get_contents($cacheFile);
    exit;
}

$colegioService = new ColegioService($link);

try {
    $data = $colegioService->getInfoColegio();

    $response = [
        "status" => "success",
        "data" => [
            "nombre" => $data['NOMBRECOLEGIO'],
            "lema" => $data['LEMA'],
            "direccion" => $data['DIRECCION'],
            "telefonos" => $data['TELEFONOS'],
            "logo" => $data['logo_colegio']
        ]
    ];

    $jsonResponse = json_encode($response, JSON_UNESCAPED_UNICODE);

    // Guardar en cache
    file_put_contents($cacheFile, $jsonResponse);

    echo $jsonResponse;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error interno del servidor"]);
}
?>