<?php
session_start();
header("Content-Type: application/json");
header("Cache-Control: no-cache, no-store, must-revalidate");

date_default_timezone_set('Asia/Manila');

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

$apiKey = $config['GHL']['API_KEY'];
$locationId = $config['GHL']['LOCATION_ID'];

$headers = [
    "Authorization: Bearer $apiKey",
    "Version: 2021-07-28",
    "Content-Type: application/json"
];

// ==============================
// 1. GET STAGES
// ==============================

$stageMap = [];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://services.leadconnectorhq.com/opportunities/pipelines?locationId=$locationId");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$pipelineResponse = curl_exec($ch);
$pipelineData = json_decode($pipelineResponse, true);

if (!empty($pipelineData['pipelines'])) {
    foreach ($pipelineData['pipelines'] as $pipeline) {
        foreach ($pipeline['stages'] as $stage) {
            $stageMap[$stage['id']] = $stage['name'];
        }
    }
}

// ==============================
// 2. GET OPPORTUNITIES
// ==============================

$allOpportunities = [];
$startAfterId = null;

do {

    $body = [
        "locationId" => $locationId,
        "limit" => 10000
    ];

    if ($startAfterId) {
        $body["startAfterId"] = $startAfterId;
    }

    curl_setopt($ch, CURLOPT_URL, "https://services.leadconnectorhq.com/opportunities/search");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $data = json_decode($response, true);

    if (empty($data['opportunities'])) break;

    $batch = $data['opportunities'];
    $allOpportunities = array_merge($allOpportunities, $batch);

    $last = end($batch);
    $startAfterId = $last['id'] ?? null;

} while (count($batch) === 100);

curl_close($ch);

// ==============================
// CHECK
// ==============================

if (count($allOpportunities) === 0) {
    echo json_encode([
        "weekly_leads" => 0,
        "pipeline_value" => 0,
        "closed_sales" => 0,
        "revenue" => 0,
        "quotes_issued" => 0,
        "stages" => [],
        "weekly_trend" => [],
        "weekly_labels" => [],
        "lead_sources" => [],
        "last_week_leads" => 0
    ]);
    exit;
}

// ==============================
// 3. PROCESS DATA
// ==============================

$totalValue = 0;
$closedSales = 0;
$revenue = 0;
$quotesIssued = 0;
$weeklyLeads = 0;
$lastWeekLeads = 0;

$stages = [];
$weeklyTrend = [];
$leadSources = [];

// 🔥 FIX (THIS WAS MISSING)
$startOfWeek = strtotime("monday this week");
$endOfWeek = strtotime("sunday this week 23:59:59");

$startOfLastWeek = strtotime("monday last week");
$endOfLastWeek = strtotime("sunday last week 23:59:59");

foreach ($allOpportunities as $opp) {

    $createdAt = strtotime($opp['createdAt'] ?? '');
    $value = floatval($opp['monetaryValue'] ?? 0);

    $stageId = $opp['pipelineStageId'] ?? null;
    $stageName = $stageMap[$stageId] ?? 'Unknown';

    // TOTAL
    $totalValue += $value;

    if (!isset($stages[$stageName])) $stages[$stageName] = 0;
    $stages[$stageName]++;

    // CLOSED / REVENUE
    if (stripos($stageName, 'closed') !== false) {
        $closedSales++;
        $revenue += $value;
    }

    if (stripos($stageName, 'quote') !== false) {
        $quotesIssued++;
    }

    // THIS WEEK
    if ($createdAt >= $startOfWeek && $createdAt <= $endOfWeek) {
        $weeklyLeads++;
    }

    // LAST WEEK
    if ($createdAt >= $startOfLastWeek && $createdAt <= $endOfLastWeek) {
        $lastWeekLeads++;
    }

    // TREND
    $label = date('M d', $createdAt);
    if (!isset($weeklyTrend[$label])) $weeklyTrend[$label] = 0;
    $weeklyTrend[$label]++;

    // SOURCES
    $source = strtolower($opp['source'] ?? 'unknown');

    if (strpos($source, 'facebook') !== false || strpos($source, 'meta') !== false) {
        $source = 'Meta Ads';
    } elseif (strpos($source, 'google') !== false) {
        $source = 'Google';
    } elseif (strpos($source, 'tiktok') !== false) {
        $source = 'TikTok';
    } elseif (strpos($source, 'organic') !== false) {
        $source = 'Organic';
    } else {
        $source = 'Referral';
    }

    if (!isset($leadSources[$source])) $leadSources[$source] = 0;
    $leadSources[$source]++;
}

// ==============================
// OUTPUT
// ==============================

echo json_encode([
    "weekly_leads" => $weeklyLeads,
    "pipeline_value" => $totalValue,
    "closed_sales" => $closedSales,
    "revenue" => $revenue,
    "quotes_issued" => $quotesIssued,
    "stages" => $stages,
    "weekly_trend" => array_values($weeklyTrend),
    "weekly_labels" => array_keys($weeklyTrend),
    "lead_sources" => $leadSources,
    "last_week_leads" => $lastWeekLeads
]);