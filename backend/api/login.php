<?php
/**
 * Bejelentkezési API Endpoint
 *
 * Ez az endpoint kezeli a felhasználók bejelentkezését.
 * A kérés JWT-ba kódolt payload-ot vár, amely email és jelszó mezőket tartalmaz.
 * Sikeres bejelentkezés esetén JWT tokent ad vissza a felhasználó adataival.
 *
 * Metódus: POST
 * Bemeneti formátum: { "payload": "jwt_token" }
 * A dekódolt payload: { "email": "...", "password": "..." }
 *
 * Kimeneti formátum (sikeres): { "payload": "jwt_token" }
 * A dekódolt válasz: { "message": "...", "token": "...", "user": {...} }
 */

include_once '../config/db.php';
include_once '../utils/jwt_utils.php';

// Adatbázis kapcsolat létrehozása
$database = new Database();
$db = $database->getConnection();

// Bejövő kérés beolvasása és dekódolása
$input = json_decode(file_get_contents("php://input"));
$data = null;

// JWT payload dekódolása, ha létezik
if (isset($input->payload)) {
    $data = JWTUtils::decodePayload($input->payload);
}

// Email és jelszó validálása
if ($data && !empty($data->email) && !empty($data->password)) {
    $email = $data->email;
    $password = $data->password;

    // Felhasználó keresése email alapján
    $query = "SELECT id, email, password_hash, role FROM users WHERE email = :email LIMIT 0,1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    // Ha létezik a felhasználó
    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $password_hash = $row['password_hash'];

        // Jelszó ellenőrzése (bcrypt hash)
        if (password_verify($password, $password_hash)) {
            // JWT token generálása a felhasználó adataival
            $token_payload = array(
                "id" => $row['id'],
                "email" => $row['email'],
                "role" => $row['role']
            );
            $jwt = JWTUtils::generateToken($token_payload);

            // Sikeres bejelentkezés
            http_response_code(200);
            echo json_encode([
                'payload' => JWTUtils::encodePayload(array(
                    "message" => "Successful login.",
                    "token" => $jwt,
                    "user" => array(
                        "id" => $row['id'],
                        "email" => $row['email'],
                        "role" => $row['role']
                    )
                ))
            ]);
        } else {
            // Hibás jelszó
            http_response_code(401);
            echo json_encode(['payload' => JWTUtils::encodePayload(array("message" => "Login failed."))]);
        }
    } else {
        // Felhasználó nem található
        http_response_code(401);
        echo json_encode(['payload' => JWTUtils::encodePayload(array("message" => "Login failed."))]);
    }
} else {
    // Hiányos adatok
    http_response_code(400);
    echo json_encode(['payload' => JWTUtils::encodePayload(array("message" => "Incomplete data."))]);
}
?>