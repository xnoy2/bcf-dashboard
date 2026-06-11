<?php
session_start();
header("Content-Type: application/json");

// ==============================
// ✅ METHOD CHECK (first)
// ==============================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

// ==============================
// ✅ LOAD .env (self-contained, no require)
// login_proc.php is at: dist/api/login_proc.php
// .env is at:           .env  (project root)
// Docker copies to:     /var/www/html/
// So .env lands at:     /var/www/html/.env
// __DIR__ here =        /var/www/html/dist/api
// ../../.env =          /var/www/html/.env  ✅
// ==============================
$envPath = __DIR__ . '/../../.env';

if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;

        [$name, $value] = explode('=', $line, 2);
        $name  = trim($name);
        $value = trim($value);

        // Strip surrounding quotes if present (single or double)
        if (strlen($value) >= 2) {
            $first = $value[0];
            $last  = $value[strlen($value) - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }

        $_ENV[$name] = $value;
        putenv("$name=$value");
    }
}

// ==============================
// ✅ READ CREDENTIALS FROM ENV
// ==============================
$validEmail   = $_ENV['ADMIN_EMAIL']         ?? '';
$passwordHash = $_ENV['ADMIN_PASSWORD_HASH'] ?? '';

// ==============================
// ✅ FALLBACK: if env failed, use
//    Render environment variables
//    (getenv() reads Render's env vars)
// ==============================
if (!$validEmail)   $validEmail   = getenv('ADMIN_EMAIL')         ?: '';
if (!$passwordHash) $passwordHash = getenv('ADMIN_PASSWORD_HASH') ?: '';

// ==============================
// 📥 INPUT
// ==============================
$input    = file_get_contents("php://input");
$data     = json_decode($input, true) ?? [];
$email    = trim($data['email']    ?? '');
$password = trim($data['password'] ?? '');

// ==============================
// 🚨 GUARD: env not loaded
// ==============================
if (!$validEmail || !$passwordHash) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Server configuration error — ENV not loaded",
        "debug"   => [
            "env_path_checked" => $envPath,
            "env_file_exists"  => file_exists($envPath),
            "admin_email_set"  => !empty($validEmail),
            "hash_set"         => !empty($passwordHash)
        ]
    ]);
    exit;
}

// ==============================
// 🔑 LOGIN CHECK
// ==============================
if ($email === $validEmail && password_verify($password, $passwordHash)) {
    $_SESSION['user']    = $email;
    $_SESSION['account'] = 'bcf'; // default account on login
    echo json_encode(["success" => true]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid credentials"
    ]);
}