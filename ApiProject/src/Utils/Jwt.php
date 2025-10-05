<?php

include_once __DIR__ . '/../Utils/JwtPayload.php';

class JWT {
    private static string $secretKeyPath = __DIR__ . '/../../config/jwtSecret.json';
    private static string $secretKey;

    public static function setSecretKey(): void {

        // Check if the config file exists
        if (!file_exists(JWT::$secretKeyPath)) {
            throw new Exception("Configuration file not found");
        }

        // Read and decode the JSON content
        $jsonContent = file_get_contents(JWT::$secretKeyPath);
        $config = json_decode($jsonContent, true);

        // Validate the decoded configuration
        if ($config === null || !isset($config['auth']) || !ArrayUtils::checkIfValueIsString($config['auth'], 'jwt_secret_key')) {
            throw new Exception("Invalid or incomplete JSON configuration.");
        }

        // Extract credentials
        self::$secretKey = $config['auth']['jwt_secret_key'];
    }

    /**
     * Checks if the authorization bearer token is present, well-formed and then decodes it. 
     * @param array $headers the request headers
     * @return JwtPayload|null If the token is valid returns JwtPayload, otherwise null (absent, malformed, expired or invalid)
     */
    public static function extractFromHeaders(array $headers): JwtPayload|null {
        if (!isset($headers['Authorization'])) {
            return null; // absent
        }
        if (!str_starts_with($headers['Authorization'], "Bearer ")) {
            return null; // malformed
        }

        $token = substr($headers['Authorization'], strlen("Bearer "));
        return JWT::decode($token);
    }

    /**
     * Creates and signs a JWT token, handles signature with HMAC method
     * @param JwtPayload $payload: data to encode
     * @return string token as a base64 encoded string
     */
    public static function encode(JwtPayload $payload): string {
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        $base64UrlHeader = self::base64UrlEncode(json_encode($header));
        $base64UrlPayload = self::base64UrlEncode(json_encode($payload));
        
        $signature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, self::$secretKey, true);
        $base64UrlSignature = self::base64UrlEncode(($signature));

        return $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;
    }

    /**
     * Decodes a JWT token (checks if signature is correct, given secret)
     * @param string $token jwt token - base64 string
     * @return ?JwtPayload decoded payload, provided as an object of JwtPayload
     */
    public static function decode(string $token): ?JwtPayload {
        $tokenParts = explode('.', $token);
        if (count($tokenParts) !== 3) {
            return null; // invalid token format
        }

        [$header_b64, $payload_b64, $sig_b64] = $tokenParts;

        $signature = self::base64UrlEncode(hash_hmac('sha256', $header_b64 . '.' . $payload_b64, self::$secretKey, true)); 
        if (!hash_equals($signature, $sig_b64)) {
            return null; // signature is not the expected one
        }

        $payload = JwtPayload::convert(
            json_decode(
                self::base64UrlDecode($payload_b64), true
            )
        );
        
        return JwtPayload::isExpired($payload) 
            ? null 
            : $payload;
    }

    /**
     * Helper function for base64 url encoding (removes padding and replaces chars)
     * @param string $data
     * @return string $data encoded in base64
     */
    private static function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Helper function for base64 url decoding
     * @param string $data
     * @return bool|string $data decoded from base64 to normal
     */
    private static function base64UrlDecode(string $data): string {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}