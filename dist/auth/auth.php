<?php
session_start();

if (!isset($_SESSION['user'])) {
    // FIX:
header("Location: /auth/login.php");
    exit;
}
?>