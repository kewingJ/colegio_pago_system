<?php
/**
 * Simple JWT implementation for the School System
 * No external dependencies required.
 */
class JWT {
    private static $secret = null;

    private static function getSecret() {
        if (self::$secret === null) {
            // En producción, esto debería venir de una variable de entorno o un archivo de configuración fuera del webroot
            self::$secret = getenv('JWT_SECRET') ?: 'Colegio_Secret_Key_2024_@!';
        }
        return self::$secret;
    }

    public static function generate($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::getSecret(), true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function validate($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return false;

        $header = $parts[0];
        $payload = $parts[1];
        $signature = $parts[2];

        $validSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(hash_hmac('sha256', $header . "." . $payload, self::getSecret(), true)));

        if ($signature !== $validSignature) return false;

        $payloadData = json_decode(base64_decode($payload), true);
        return $payloadData;
    }
}
?>