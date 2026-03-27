<?php
session_start();
header('Content-Type: application/json');

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

// ==============================
// 🔐 CONFIG
// ==============================
$config = require __DIR__ . '/../auth/config.php';

// 🔥 NEW: MULTI-ACCOUNT SUPPORT (SAFE)
$accounts = $config['ACCOUNTS'];
$selectedAccount = $_GET['account'] ?? 'bcf';

// fallback safety
if (!isset($accounts[$selectedAccount])) {
    $selectedAccount = 'bcf';
}

// 🔥 REPLACED (ONLY THIS LINE CHANGED)
$apiToken = $accounts[$selectedAccount]['CLOUDFLARE']['API_TOKEN'];


// ==============================
// 🔧 CURL HELPER
// ==============================
function curlGet($url, $token) {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $token",
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        return null;
    }

    curl_close($ch);
    return json_decode($response, true);
}

// ==============================
// 🌐 DNS CHECK
// ==============================
function checkDNS($domain) {

    $result = [
        "spf" => false,
        "dkim" => false,
        "dmarc" => false,
        "mx" => false
    ];

    $txtRecords = dns_get_record($domain, DNS_TXT);
    if ($txtRecords) {
        foreach ($txtRecords as $record) {
            if (isset($record['txt']) && strpos($record['txt'], 'v=spf1') !== false) {
                $result['spf'] = true;
            }
        }
    }

    $dmarc = dns_get_record("_dmarc.$domain", DNS_TXT);
    if ($dmarc) {
        foreach ($dmarc as $record) {
            if (isset($record['txt']) && strpos($record['txt'], 'v=DMARC1') !== false) {
                $result['dmarc'] = true;
            }
        }
    }

    $dkim = dns_get_record("default._domainkey.$domain", DNS_TXT);
    if (!empty($dkim)) {
        $result['dkim'] = true;
    }

    $mx = dns_get_record($domain, DNS_MX);
    if (!empty($mx)) {
        $result['mx'] = true;
    }

    return $result;
}

// ==============================
// 🌐 GET DOMAINS
// ==============================
$zones = curlGet("https://api.cloudflare.com/client/v4/zones", $apiToken);

if (!$zones || !isset($zones['result'])) {
    echo json_encode(["error" => "Failed to fetch domains"]);
    exit;
}

$domains = $zones['result'];

// ==============================
// 📊 PROCESS DATA
// ==============================
$sslHealthy = 0;
$alerts = 0;

$systemHealth = [
    "healthy" => 0,
    "warning" => 0,
    "critical" => 0
];

$dnsHealth = [
    "spf" => 0,
    "dkim" => 0,
    "dmarc" => 0,
    "mx" => 0
];

$domainsList = [];

foreach ($domains as $domain) {

    $domainName = $domain['name'];

    // ================= SSL CHECK =================
    $ch = curl_init("https://$domainName");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_NOBODY, true);

    curl_exec($ch);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $sslValid = curl_getinfo($ch, CURLINFO_SSL_VERIFYRESULT);

    curl_close($ch);

    $sslOk = ($httpCode >= 200 && $httpCode < 400 && $sslValid === 0);

    if ($sslOk) {
        $sslHealthy++;
        $systemHealth['healthy']++;
    } elseif ($httpCode >= 200 && $httpCode < 500) {
        $systemHealth['warning']++;
    } else {
        $alerts++;
        $systemHealth['critical']++;
    }

    // ================= DNS =================
    $dns = checkDNS($domainName);

    if ($dns['spf']) $dnsHealth['spf']++;
    if ($dns['dkim']) $dnsHealth['dkim']++;
    if ($dns['dmarc']) $dnsHealth['dmarc']++;
    if ($dns['mx']) $dnsHealth['mx']++;

    $domainsList[] = [
        "name" => $domainName,
        "ssl" => $sslOk,
        "spf" => $dns['spf'],
        "dkim" => $dns['dkim'],
        "dmarc" => $dns['dmarc'],
        "mx" => $dns['mx']
    ];
}

// ==============================
// 📊 COMPLIANCE
// ==============================
$totalDomains = count($domains);
if ($totalDomains == 0) $totalDomains = 1;

$sslPercent = round(($sslHealthy / $totalDomains) * 100);

$dnsTotalChecks = $totalDomains * 4;
$dnsPassed = $dnsHealth['spf'] + $dnsHealth['dkim'] + $dnsHealth['dmarc'] + $dnsHealth['mx'];

$dnsPercent = round(($dnsPassed / $dnsTotalChecks) * 100);

// placeholders
$mfaPercent = 65;
$backupPercent = 100;

// ==============================
// 🚨 ALERTS
// ==============================
$alertMessages = [];

if ($sslPercent < 80) {
    $alertMessages[] = "SSL health below 80%";
}

if ($dnsPercent < 80) {
    $alertMessages[] = "DNS configuration issues detected";
}

// ==============================
// 📤 OUTPUT (UNCHANGED)
// ==============================
echo json_encode([
    "domains" => count($domains),
    "ssl_healthy" => $sslHealthy,
    "alerts" => count($alertMessages),

    "mfa" => "$mfaPercent%",
    "email_health" => $dnsPercent > 80 ? "Good" : "Issues",
    "backups" => "$backupPercent%",

    "system_health" => $systemHealth,

    "compliance" => [
        "mfa" => $mfaPercent,
        "backups" => $backupPercent,
        "dns" => $dnsPercent,
        "ssl" => $sslPercent
    ],

    "alerts_list" => $alertMessages,

    "domains_list" => $domainsList
]);