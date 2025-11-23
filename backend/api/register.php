<?php

include_once '../config/db.php';
include_once '../utils/jwt_utils.php';

$database = new Database();
$db = $database->getConnection();

$input = json_decode(file_get_contents("php://input"));
$data = null;
if (isset($input->payload)) {
    $data = JWTUtils::decodePayload($input->payload);
}

if ($data && !empty($data->email) && !empty($data->password)) {
    $email = $data->email;
    $password = $data->password;
    // Default role is user
    $role = 'user';

    // Check if email exists
    $query = "SELECT id FROM users WHERE email = :email LIMIT 0,1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(['payload' => JWTUtils::encodePayload(array("message" => "User already exists."))]);
    } else {
        $query = "INSERT INTO users SET email=:email, password_hash=:password, role=:role";
        $stmt = $db->prepare($query);

        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password_hash);
        $stmt->bindParam(':role', $role);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(['payload' => JWTUtils::encodePayload(array("message" => "User created successfully."))]);
        } else {
            http_response_code(503);
            echo json_encode(['payload' => JWTUtils::encodePayload(array("message" => "Unable to create user."))]);
        }
    }
} else {
    http_response_code(400);
    echo json_encode(['payload' => JWTUtils::encodePayload(array("message" => "Incomplete data."))]);
}
?>