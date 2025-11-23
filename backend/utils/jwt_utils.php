<?php
/**
 * JWT (JSON Web Token) Kezelő Osztály
 *
 * Ez az osztály felelős a JWT tokenek generálásáért, validálásáért és az adatcsomagok
 * JWT-be való kódolásáért/dekódolásáért. HS256 algoritmus használatával írja alá a tokeneket.
 *
 * Két fő felhasználási mód:
 * 1. Autentikáció: generateToken() és validateToken() - lejárati idővel rendelkező tokenek
 * 2. Adatintegritás: encodePayload() és decodePayload() - kérés/válasz body aláírása
 */

include_once __DIR__ . '/../config/env_loader.php';

class JWTUtils
{
    /** @var string A használt JWT aláírási algoritmus (HMAC SHA256) */
    private static $algorithm = 'HS256';

    /**
     * Titkos kulcs lekérése környezeti változóból
     *
     * @return string A JWT aláírásához használt titkos kulcs
     */
    private static function getSecretKey()
    {
        return getenv('JWT_SECRET') ?: "SECRET1234567890";
    }

    /**
     * JWT token generálása automatikus lejárati idővel
     *
     * Létrehoz egy JWT tokent a megadott payload alapján, automatikusan hozzáadva
     * egy 1 órás lejárati időt (exp mező). Ez a metódus autentikációhoz használatos.
     *
     * @param array $payload A tokenbe kódolandó adatok (asszociatív tömb)
     * @return string A generált JWT token (header.payload.signature formátumban)
     *
     * Példa:
     * $token = JWTUtils::generateToken(['user_id' => 123, 'email' => 'user@example.com']);
     */
    public static function generateToken($payload)
    {
        // JWT header létrehozása (typ és alg mezőkkel)
        $header = json_encode(['typ' => 'JWT', 'alg' => self::$algorithm]);

        // Lejárati idő hozzáadása (1 óra)
        $payload['exp'] = time() + (60 * 60); // 1 hour expiration
        $payload = json_encode($payload);

        // Base64URL kódolás (header és payload)
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        // HMAC SHA256 aláírás generálása
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::getSecretKey(), true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        // JWT token összeállítása (header.payload.signature)
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * JWT token validálása és dekódolása
     *
     * Ellenőrzi a token aláírását és lejárati idejét. Ha érvényes, visszaadja a payload-ot.
     *
     * @param string $token A validálandó JWT token
     * @return object|false A dekódolt payload objektum vagy false, ha érvénytelen
     *
     * Ellenőrzések:
     * - Token formátum (3 részből áll-e)
     * - Lejárati idő (exp mező)
     * - Aláírás helyessége
     */
    public static function validateToken($token)
    {
        // Token szétbontása 3 részre (header, payload, signature)
        $tokenParts = explode('.', $token);
        if (count($tokenParts) != 3) {
            return false;
        }

        // Base64URL dekódolás
        $header = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[0]));
        $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1]));
        $signatureProvided = $tokenParts[2];

        // Lejárati idő ellenőrzése
        $payloadObj = json_decode($payload);
        if (isset($payloadObj->exp) && $payloadObj->exp < time()) {
            return false; // Token lejárt
        }

        // Aláírás újragenerálása és összehasonlítása
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::getSecretKey(), true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        // Aláírás ellenőrzése
        if ($base64UrlSignature === $signatureProvided) {
            return $payloadObj;
        }

        return false;
    }

    /**
     * Adatok JWT-be kódolása (lejárati idő nélkül)
     *
     * Kódol egy adatcsomagot JWT formátumba lejárati idő nélkül. Ezt használjuk
     * a kérés/válasz body integritásának biztosítására.
     *
     * @param mixed $data A kódolandó adat (bármilyen JSON-szerializálható típus)
     * @return string A generált JWT token
     *
     * Példa:
     * $payload = JWTUtils::encodePayload(['users' => [...], 'count' => 5]);
     */
    public static function encodePayload($data)
    {
        // JWT header létrehozása
        $header = json_encode(['typ' => 'JWT', 'alg' => self::$algorithm]);
        $payload = json_encode($data);

        // Base64URL kódolás
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        // HMAC SHA256 aláírás generálása
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::getSecretKey(), true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * JWT token dekódolása és aláírás ellenőrzése
     *
     * Dekódol egy JWT tokent és ellenőrzi az aláírását. Lejárati időt NEM ellenőriz,
     * mivel ez a metódus adatok integritásának ellenőrzésére szolgál.
     *
     * @param string $token A dekódolandó JWT token
     * @return object|null A dekódolt adat objektum vagy null, ha az aláírás érvénytelen
     */
    public static function decodePayload($token)
    {
        // Token szétbontása
        $tokenParts = explode('.', $token);
        if (count($tokenParts) != 3) {
            return null;
        }

        // Base64URL dekódolás
        $header = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[0]));
        $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1]));
        $signatureProvided = $tokenParts[2];

        // Aláírás újragenerálása és összehasonlítása
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::getSecretKey(), true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        // Aláírás ellenőrzése
        if ($base64UrlSignature === $signatureProvided) {
            return json_decode($payload);
        }

        return null;
    }
}
?>