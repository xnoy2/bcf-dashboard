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
$accounts = $config['ACCOUNTS'] ?? [];

// ✅ SUPPORT BOTH SESSION + AJAX
$selectedAccount = $_GET['account'] ?? $_SESSION['account'] ?? 'bcf';

// ==============================
// 🔧 MAIN FUNCTION (FIXED ONLY)
// ==============================
function processAccount($apiKey, $locationId)
{
    $headers = [
        "Authorization: Bearer $apiKey",
        "Version: 2021-04-15", // ✅ FIXED VERSION
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
    // 2. GET OPPORTUNITIES (FIXED)
    // ==============================
    $allOpportunities = [];
    $page = 1;

    do {
        $body = [
            "locationId" => $locationId,
            "limit" => 100,
            "page" => $page
        ];

        curl_setopt($ch, CURLOPT_URL, "https://services.leadconnectorhq.com/opportunities/search");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        // ✅ ADD ERROR DEBUG (SAFE)
        if (curl_errno($ch)) {
            return ["error" => curl_error($ch)];
        }

        $data = json_decode($response, true);

        if (!$data || empty($data['opportunities'])) break;

        $batch = $data['opportunities'];
        $allOpportunities = array_merge($allOpportunities, $batch);

        $page++; // ✅ FIX pagination

    } while (!empty($batch));

    curl_close($ch);

    // ==============================
    // EMPTY DATA
    // ==============================
    if (count($allOpportunities) === 0) {
        return [
            "debug" => "No opportunities found",
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
        ];
    }

    // ==============================
    // 3. PROCESS DATA (UNCHANGED)
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

    $startOfWeek = strtotime("monday this week");
    $endOfWeek = strtotime("sunday this week 23:59:59");

    $startOfLastWeek = strtotime("monday last week");
    $endOfLastWeek = strtotime("sunday last week 23:59:59");

    foreach ($allOpportunities as $opp) {

        $createdAt = strtotime($opp['createdAt'] ?? '');
        $value = floatval($opp['monetaryValue'] ?? 0);

        $stageId = $opp['pipelineStageId'] ?? null;
        $stageName = $stageMap[$stageId] ?? 'Unknown';

        $totalValue += $value;

        if (!isset($stages[$stageName])) $stages[$stageName] = 0;
        $stages[$stageName]++;

        if (stripos($stageName, 'closed') !== false) {
            $closedSales++;
            $revenue += $value;
        }

        if (stripos($stageName, 'quote') !== false) {
            $quotesIssued++;
        }

        if ($createdAt >= $startOfWeek && $createdAt <= $endOfWeek) {
            $weeklyLeads++;
        }

        if ($createdAt >= $startOfLastWeek && $createdAt <= $endOfLastWeek) {
            $lastWeekLeads++;
        }

        $label = date('M d', $createdAt);
        if (!isset($weeklyTrend[$label])) $weeklyTrend[$label] = 0;
        $weeklyTrend[$label]++;

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

    return [
        "weekly_leads" => $weeklyLeads,
        "new_leads" => $stages["New Lead"] ?? 0,
        "pipeline_value" => $totalValue,
        "closed_sales" => $closedSales,
        "revenue" => $revenue,
        "quotes_issued" => $quotesIssued,
        "stages" => $stages,
        "weekly_trend" => array_values($weeklyTrend),
        "weekly_labels" => array_keys($weeklyTrend),
        "lead_sources" => $leadSources,
        "last_week_leads" => $lastWeekLeads,
        "total_opportunities" => count($allOpportunities) // ✅ DEBUG
    ];
}

// ==============================
// 🌐 CEO VIEW (ALL) — UNCHANGED
// ==============================
if ($selectedAccount === 'all') {

    $final = [
        "weekly_leads" => 0,
        "new_leads" => 0,
        "pipeline_value" => 0,
        "closed_sales" => 0,
        "revenue" => 0,
        "quotes_issued" => 0,
        "stages" => [],
        "weekly_trend" => [],
        "weekly_labels" => [],
        "lead_sources" => [],
        "last_week_leads" => 0,
        "accounts" => []
    ];

    foreach ($accounts as $key => $acc) {

        $apiKey = $acc['GHL']['API_KEY'];
        $locationId = $acc['GHL']['LOCATION_ID'];

        $data = processAccount($apiKey, $locationId);

        $final['weekly_leads'] += $data['weekly_leads'];
        $final['new_leads'] += $data['new_leads'] ?? 0;
        $final['pipeline_value'] += $data['pipeline_value'];
        $final['closed_sales'] += $data['closed_sales'];
        $final['revenue'] += $data['revenue'];
        $final['quotes_issued'] += $data['quotes_issued'];
        $final['last_week_leads'] += $data['last_week_leads'];

        $final['accounts'][$key] = $data;
    }

    echo json_encode($final);
    exit;
}

// ==============================
// 🎯 SINGLE ACCOUNT — UNCHANGED
// ==============================
if (!isset($accounts[$selectedAccount])) {
    echo json_encode(["error" => "Invalid account"]);
    exit;
}

$apiKey = $accounts[$selectedAccount]['GHL']['API_KEY'];
$locationId = $accounts[$selectedAccount]['GHL']['LOCATION_ID'];

$data = processAccount($apiKey, $locationId);

echo json_encode($data);