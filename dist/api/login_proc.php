<?php

session_start();
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid request"]);
    exit;
}

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

// 🔥 ENV variables
$validEmail = $_ENV['ADMIN_EMAIL'] ?? '';
$passwordHash = $_ENV['ADMIN_PASSWORD_HASH'] ?? '';

if ($email === $validEmail && password_verify($password, $passwordHash)) {

    $_SESSION['user'] = $email;

    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false]);
}