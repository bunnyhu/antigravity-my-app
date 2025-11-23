<?php
include_once '../config/db.php';
include_once '../utils/jwt_utils.php';

$database = new Database();
$db = $database->getConnection();

// Get headers
$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if (!$authHeader) {
    http_response_code(401);
    echo json_encode(array("message" => "Access denied. No token provided."));
    exit();
}

$token = str_replace("Bearer ", "", $authHeader);
$userData = JWTUtils::validateToken($token);

if (!$userData) {
    http_response_code(401);
    echo json_encode(array("message" => "Access denied. Invalid token."));
    exit();
}

// Check if admin
if ($userData->role !== 'admin') {
    http_response_code(403);
    echo json_encode(array("message" => "Access denied. Admins only."));
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    $query = "SELECT id, email, role, created_at FROM users";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($users);
} elseif ($method == 'DELETE') {
    // Delete user
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data->id)) {
        $query = "DELETE FROM users WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $data->id);
        if ($stmt->execute()) {
            echo json_encode(array("message" => "User deleted."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to delete user."));
        }
    }
} elseif ($method == 'PUT') {
    // Update role
    $data = json_decode(file_get_contents("php://input"));
    if (!empty($data->id) && !empty($data->role)) {
        $query = "UPDATE users SET role = :role WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':role', $data->role);
        $stmt->bindParam(':id', $data->id);
        if ($stmt->execute()) {
            echo json_encode(array("message" => "User updated."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to update user."));
        }
    }
}
?>