<?php
/**
 * Regisztrációs API Endpoint
 *
 * Ez az endpoint kezeli az új felhasználók regisztrációját.
 * A kérés JWT-ba kódolt payload-ot vár, amely email és password mezőket tartalmaz.
 * Ellenőrzi, hogy az email cím még nem foglalt-e, majd létrehozza az új felhasználót.
 *
 * Metódus: POST
 * Bemeneti formátum: { "payload": "jwt_token" }
 * A dekódolt payload: { "email": "...", "password": "..." }
 *
 * Kimeneti formátum: { "payload": "jwt_token" }
 * A dekódolt válasz: { "message": "..." }
 *
 * Alapértelmezett szerepkör: "user"
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

    // Alapértelmezett szerepkör új felhasználóknak
    $role = 'user';

    // Ellenőrzés: létezik-e már ez az email cím
    $query = "SELECT id FROM users WHERE email = :email LIMIT 0,1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    // Ha már létezik a felhasználó
    if ($stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(['payload' => JWTUtils::encodePayload(array("message" => "User already exists."))]);
    } else {
        // Új felhasználó létrehozása
        $query = "INSERT INTO users SET email=:email, password_hash=:password, role=:role";
        $stmt = $db->prepare($query);

        // Jelszó hashelése bcrypt algoritmussal
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        // Paraméterek hozzárendelése
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password_hash);
        $stmt->bindParam(':role', $role);

        // Felhasználó mentése
        if ($stmt->execute()) {
            // Sikeres regisztráció
            http_response_code(201);
            echo json_encode(['payload' => JWTUtils::encodePayload(array("message" => "User created successfully."))]);
        } else {
            // Adatbázis hiba
            http_response_code(503);
            echo json_encode(['payload' => JWTUtils::encodePayload(array("message" => "Unable to create user."))]);
        }
    }
} else {
    // Hiányos adatok
    http_response_code(400);
    echo json_encode(['payload' => JWTUtils::encodePayload(array("message" => "Incomplete data."))]);
}
?>