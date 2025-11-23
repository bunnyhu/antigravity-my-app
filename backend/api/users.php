<?php
/**
 * Felhasználókezelési API Endpoint
 *
 * Ez az endpoint kezeli a felhasználók listázását, törlését és szerepkör módosítását.
 * Csak admin jogosultsággal rendelkező felhasználók használhatják.
 *
 * Támogatott metódusok:
 * - GET: Összes felhasználó listázása
 * - DELETE: Felhasználó törlése ID alapján
 * - PUT: Felhasználó szerepkörének módosítása
 *
 * Autentikáció: Bearer token az Authorization headerben
 *
 * Bemeneti formátum (DELETE/PUT): { "payload": "jwt_token" }
 * Kimeneti formátum: { "payload": "jwt_token" }
 */

include_once '../config/db.php';
include_once '../utils/jwt_utils.php';

// Adatbázis kapcsolat létrehozása
$database = new Database();
$db = $database->getConnection();

// Authorization header lekérése
$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

// Token ellenőrzése
if (!$authHeader) {
    http_response_code(401);
    echo json_encode(array("message" => "Access denied. No token provided."));
    exit();
}

// Bearer prefix eltávolítása
$token = str_replace("Bearer ", "", $authHeader);
$userData = JWTUtils::validateToken($token);

// Token validálás
if (!$userData) {
    http_response_code(401);
    echo json_encode(array("message" => "Access denied. Invalid token."));
    exit();
}

// Admin jogosultság ellenőrzése
if ($userData->role !== 'admin') {
    http_response_code(403);
    echo json_encode(array("message" => "Access denied. Admins only."));
    exit();
}

// HTTP metódus lekérése
$method = $_SERVER['REQUEST_METHOD'];

// Bejövő payload dekódolása (nem-GET kérések esetén)
$data = null;
$input = json_decode(file_get_contents("php://input"));
if (isset($input->payload)) {
    $data = JWTUtils::decodePayload($input->payload);
}

// ========== GET: Felhasználók listázása ==========
if ($method == 'GET') {
    // Összes felhasználó lekérdezése (jelszó hash nélkül)
    $query = "SELECT id, email, role, created_at FROM users";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // JWT-be kódolt válasz küldése
    echo json_encode(['payload' => JWTUtils::encodePayload($users)]);

    // ========== DELETE: Felhasználó törlése ==========
} elseif ($method == 'DELETE') {
    // ID validálása
    if (!empty($data->id)) {
        $query = "DELETE FROM users WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $data->id);

        // Törlés végrehajtása
        if ($stmt->execute()) {
            echo json_encode(['payload' => JWTUtils::encodePayload(["message" => "User deleted."])]);
        } else {
            http_response_code(503);
            echo json_encode(['payload' => JWTUtils::encodePayload(["message" => "Unable to delete user."])]);
        }
    }

    // ========== PUT: Szerepkör módosítása ==========
} elseif ($method == 'PUT') {
    // ID és role validálása
    if (!empty($data->id) && !empty($data->role)) {
        $query = "UPDATE users SET role = :role WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':role', $data->role);
        $stmt->bindParam(':id', $data->id);

        // Frissítés végrehajtása
        if ($stmt->execute()) {
            echo json_encode(['payload' => JWTUtils::encodePayload(["message" => "User updated."])]);
        } else {
            http_response_code(503);
            echo json_encode(['payload' => JWTUtils::encodePayload(["message" => "Unable to update user."])]);
        }
    }
}
?>