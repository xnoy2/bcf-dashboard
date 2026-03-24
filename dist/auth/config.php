<?php
if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
    http_response_code(403);
    exit("Access denied");
}
// 🔥 IMPORTANT for Render (Docker)
$_ENV = array_merge($_ENV, getenv());

return [

  // =========================
  // 🔵 GHL (GoHighLevel)
  // =========================
  "GHL" => [
    "API_KEY" => $_ENV['GHL_API_KEY'] ?? '',
    "LOCATION_ID" => $_ENV['GHL_LOCATION_ID'] ?? ''
  ],

  // =========================
  // 🟡 TRELLO
  // =========================
  "TRELLO" => [
    "API_KEY" => $_ENV['TRELLO_API_KEY'] ?? '',
    "TOKEN" => $_ENV['TRELLO_TOKEN'] ?? '',
    "BOARD_IDS" => [
    "69aab994277746a144e92503", //Joms Board
    "694552fae7a97980a3103f02", // Cza
    "699fea4486a4519c370d029c", // Luke
    "69a5f7a29f3fd592bc75a4cc", // MJ
    "69ae8ad1511ec097687998d6", // MJ
    "6995a1ea8f6761d6fd4abb9c", // Mica
    "69b1711aea10a6c543e28185" // MJ & Christian
    ]
  ],

  // =========================
  // 🟠 CLOUDFLARE
  // =========================
  "CLOUDFLARE" => [
    "API_TOKEN" => $_ENV['CLOUDFLARE_API_TOKEN'] ?? ''
  ]

];