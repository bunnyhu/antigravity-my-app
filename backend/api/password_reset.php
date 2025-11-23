<?php
include_once '../config/db.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->email)) {
    // In a real app, we would generate a token, save it to DB, and send an email.
    // For this demo, we'll just simulate success.
    http_response_code(200);
    echo json_encode(array("message" => "If the email exists, a reset link has been sent."));
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Email is required."));
}
?>