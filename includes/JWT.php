<?php
/**
 * Simple JWT implementation for the School System
 * No external dependencies required.
 */
class JWT {
    private static $secret = null;

    private static function getSecret() {
        if (self::$secret === null) {
            self::$secret = getenv('JWT_SECRET') ?: 'Colegio_Secret_Key_2024_@!';
        }
        return self::$secret;
    }

    private static function base64url_encode($data) {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    private static function base64url_decode($data) {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $data .= str_repeat('=', $padlen);
        }
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }

    public static function generate($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $base64UrlHeader = self::base64url_encode($header);

        $base64UrlPayload = self::base64url_encode(json_encode($payload));

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::getSecret(), true);
        $base64UrlSignature = self::base64url_encode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function validate($token) {
        if (empty($token)) return false;

        $parts = explode('.', $token);
        if (count($parts) !== 3) return false;

        $header = $parts[0];
        $payload = $parts[1];
        $signature = $parts[2];

        $validSignature = self::base64url_encode(hash_hmac('sha256', $header . "." . $payload, self::getSecret(), true));

        if ($signature !== $validSignature) return false;

        $payloadData = json_decode(self::base64url_decode($payload), true);
        return $payloadData;
    }
}
?>