<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['account'] = $_POST['account'] ?? 'bcf';
    echo json_encode(["status" => "success"]);
}