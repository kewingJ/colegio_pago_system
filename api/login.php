<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

include_once '../includes/config.php';
include_once '../includes/Services.php';
include_once '../includes/JWT.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->email) && !empty($data->password)) {
    $auth = new AuthService($link);
    $user = $auth->login($data->email, $data->password);

    if ($user) {
        $payload = [
            "id" => $user['id_usuario'],
            "email" => $user['email_usuario'],
            "name" => $user['nombre_usuario'],
            "iat" => time()
        ];

        $token = JWT::generate($payload);

        // Guardar token en DB (opcional, para persistencia permanente solicitada)
        $stmt = mysqli_prepare($link, "INSERT INTO tbl_api_tokens (id_usuario, token) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "is", $user['id_usuario'], $token);
        mysqli_stmt_execute($stmt);

        echo json_encode([
            "status" => "success",
            "message" => "Autenticación exitosa",
            "token" => $token
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Credenciales inválidas"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
}
?>