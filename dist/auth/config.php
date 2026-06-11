<?php 

if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
    http_response_code(403);
    exit("Access denied");
}

// ==============================
// 🔥 LOAD ENV (IMPORTANT)
// ==============================
$envPath = __DIR__ . '/../../.env';

if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;

        list($name, $value) = explode('=', $line, 2);

        $name = trim($name);
        $value = trim($value);

        $_ENV[$name] = $value;
        putenv("$name=$value");
    }
}

// ==============================
// 🔥 ENV HELPER
// ==============================
function env($key, $default = '') {
    return $_ENV[$key] ?? getenv($key) ?? $default;
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
        "API_KEY" => env('BCF_GHL_API_KEY'),
        "LOCATION_ID" => env('BCF_LOCATION_ID')
      ],

      "CLOUDFLARE" => [
        "API_TOKEN" => env('CF_API_TOKEN')
      ],

      "TRELLO" => [
        "API_KEY" => env('TRELLO_API_KEY'),
        "TOKEN" => env('TRELLO_TOKEN'),
        "BOARD_IDS" => explode(',', env('TRELLO_BOARD_IDS'))
      ]
    ],

    // =========================
    // 🟢 BGR
    // =========================
    "bgr" => [
      "name" => "Bespoke Garden Rooms",

      "GHL" => [
        "API_KEY" => env('BGR_GHL_API_KEY'),
        "LOCATION_ID" => env('BGR_LOCATION_ID')
      ],

      "CLOUDFLARE" => [
        "API_TOKEN" => env('CF_API_TOKEN')
      ],

      "TRELLO" => [
        "API_KEY" => env('TRELLO_API_KEY'),
        "TOKEN" => env('TRELLO_TOKEN'),
        "BOARD_IDS" => explode(',', env('TRELLO_BOARD_IDS'))
      ]
    ],

    // =========================
    // 🟣 BWD
    // =========================
    "bwd" => [
      "name" => "Bespoke Window & Door Systems",

      "GHL" => [
        "API_KEY" => env('BWD_GHL_API_KEY'),
        "LOCATION_ID" => env('BWD_LOCATION_ID')
      ],

      "CLOUDFLARE" => [
        "API_TOKEN" => env('CF_API_TOKEN')
      ],

      "TRELLO" => [
        "API_KEY" => env('TRELLO_API_KEY'),
        "TOKEN" => env('TRELLO_TOKEN'),
        "BOARD_IDS" => explode(',', env('TRELLO_BOARD_IDS'))
      ]
    ]

  ]

];