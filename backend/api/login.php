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

    $query = "SELECT id, email, password_hash, role FROM users WHERE email = :email LIMIT 0,1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $password_hash = $row['password_hash'];

        if (password_verify($password, $password_hash)) {
            $token_payload = array(
                "id" => $row['id'],
                "email" => $row['email'],
                "role" => $row['role']
            );
            $jwt = JWTUtils::generateToken($token_payload);

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
            http_response_code(401);
            echo json_encode(['payload' => JWTUtils::encodePayload(array("message" => "Login failed."))]);
        }
    } else {
        http_response_code(401);
        echo json_encode(['payload' => JWTUtils::encodePayload(array("message" => "Login failed."))]);
    }
} else {
    http_response_code(400);
    echo json_encode(['payload' => JWTUtils::encodePayload(array("message" => "Incomplete data."))]);
}
?>