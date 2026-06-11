<?php

if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
    http_response_code(403);
    exit("Access denied");
}

// ==============================
// 🔥 LOAD .env FILE
// config.php is at:  dist/auth/config.php
// .env is at:        .env  (project root)
// Docker copies to:  /var/www/html/
// __DIR__ here =     /var/www/html/dist/auth
// /../../.env =      /var/www/html/.env  ✅
// ==============================
$envPath = __DIR__ . '/../../.env';

if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line); // ✅ strips \r\n (Windows CRLF)
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;

        [$name, $value] = explode('=', $line, 2);
        $name  = trim($name);
        $value = trim($value);

        // ✅ Strip surrounding quotes if present
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
// 🔥 ENV HELPER
// Checks $_ENV first (from .env file, local dev)
// Falls back to getenv() (Render environment variables)
// ==============================
function env($key, $default = '') {
    $val = $_ENV[$key] ?? getenv($key);
    return ($val !== false && $val !== null && $val !== '') ? $val : $default;
}

return [

  // =========================
  // 🌐 MULTI-ACCOUNT CONFIG
  // =========================
  "ACCOUNTS" => [

    // =========================
    // 🔵 BCF
    // =========================
    "bcf" => [
      "name" => "Ballycastle Climbing Frames",

      "GHL" => [
        "API_KEY"     => env('BCF_GHL_API_KEY'),
        "LOCATION_ID" => env('BCF_LOCATION_ID')
      ],

      "CLOUDFLARE" => [
        "API_TOKEN" => env('CF_API_TOKEN')
      ],

      "TRELLO" => [
        "API_KEY"   => env('TRELLO_API_KEY'),
        "TOKEN"     => env('TRELLO_TOKEN'),
        "BOARD_IDS" => explode(',', env('TRELLO_BOARD_IDS'))
      ]
    ],

    // =========================
    // 🟢 BGR
    // =========================
    "bgr" => [
      "name" => "Bespoke Garden Rooms",

      "GHL" => [
        "API_KEY"     => env('BGR_GHL_API_KEY'),
        "LOCATION_ID" => env('BGR_LOCATION_ID')
      ],

      "CLOUDFLARE" => [
        "API_TOKEN" => env('CF_API_TOKEN')
      ],

      "TRELLO" => [
        "API_KEY"   => env('TRELLO_API_KEY'),
        "TOKEN"     => env('TRELLO_TOKEN'),
        "BOARD_IDS" => explode(',', env('TRELLO_BOARD_IDS'))
      ]
    ],

    // =========================
    // 🟣 BWD
    // =========================
    "bwd" => [
      "name" => "Bespoke Window & Door Systems",

      "GHL" => [
        "API_KEY"     => env('BWD_GHL_API_KEY'),
        "LOCATION_ID" => env('BWD_LOCATION_ID')
      ],

      "CLOUDFLARE" => [
        "API_TOKEN" => env('CF_API_TOKEN')
      ],

      "TRELLO" => [
        "API_KEY"   => env('TRELLO_API_KEY'),
        "TOKEN"     => env('TRELLO_TOKEN'),
        "BOARD_IDS" => explode(',', env('TRELLO_BOARD_IDS'))
      ]
    ]

  ]

];