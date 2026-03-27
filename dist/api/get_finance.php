<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}
$config = require __DIR__ . '/../auth/config.php';

$accounts = $config['ACCOUNTS'];

$selectedAccount = $_SESSION['account'] ?? 'bcf';

$apiKey = $accounts[$selectedAccount]['GHL']['API_KEY'];
$locationId = $accounts[$selectedAccount]['GHL']['LOCATION_ID'];

$headers = [
    "Authorization: Bearer $apiKey",
    "Version: 2021-07-28",
    "Content-Type: application/json"
];

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, "https://services.leadconnectorhq.com/payments/transactions?locationId=$locationId");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);
$data = json_decode($response, true);

curl_close($ch);

// ================= PROCESS =================

$cashIn = 0;
$transactions = $data['transactions'] ?? [];

foreach ($transactions as $t) {
    if (($t['status'] ?? '') === 'succeeded') {
        $cashIn += $t['amount'] ?? 0;
    }
}

// ================= OUTPUT =================

echo json_encode([
    "cash_in" => $cashIn,
    "transactions" => count($transactions)
]);