<?php

session_start();
header("Content-Type: application/json");

// ==============================
// 🔐 SECURITY
// ==============================
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
// ⚙️ CONFIG
// ==============================
$config   = require __DIR__ . '/../auth/config.php';
$accounts = $config['ACCOUNTS'] ?? [];

$selectedAccount = $_GET['account'] ?? 'bcf';

// ==============================
// ⚡ CACHE
// Use /tmp so it works on both local AND Render
// (Render has a read-only filesystem — only /tmp is writable)
// ==============================
$cacheFile = sys_get_temp_dir() . "/cache_reports_{$selectedAccount}.json";
$cacheTime = 300; // 5 minutes

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    $cached = file_get_contents($cacheFile);
    // Only serve cache if it contains real data (not empty array)
    if ($cached && $cached !== '[]' && $cached !== 'null') {
        echo $cached;
        exit;
    }
}

// ==============================
// 🚀 PARALLEL FETCH FUNCTION
// ==============================
function fetchTrelloReportsParallel($apiKey, $token, $boardIds)
{
    // Guard: if keys are empty, return empty immediately
    if (empty($apiKey) || empty($token) || empty($boardIds)) {
        return [];
    }

    $multi   = curl_multi_init();
    $handles = [];

    // 🔥 PREPARE REQUESTS
    foreach ($boardIds as $boardId) {
        $boardId = trim($boardId);
        if (empty($boardId)) continue;

        $urls = [
            "members" => "https://api.trello.com/1/boards/$boardId/members?key=$apiKey&token=$token",
            "cards"   => "https://api.trello.com/1/boards/$boardId/cards?filter=all&fields=name,due,dueComplete,idMembers,labels,dateLastActivity&key=$apiKey&token=$token"
        ];

        foreach ($urls as $type => $url) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_multi_add_handle($multi, $ch);
            $handles[$boardId][$type] = $ch;
        }
    }

    // 🔥 EXECUTE PARALLEL
    do {
        curl_multi_exec($multi, $running);
        curl_multi_select($multi);
    } while ($running > 0);

    $reports = [];

    // ================= PROCESS RESULTS =================
    foreach ($handles as $boardId => $types) {

        $membersRaw = curl_multi_getcontent($types['members']);
        $cardsRaw   = curl_multi_getcontent($types['cards']);

        $membersData = json_decode($membersRaw, true);
        $cardsData   = json_decode($cardsRaw, true);

        // Skip this board if Trello returned an error (HTML or error object)
        if (!is_array($membersData) || !is_array($cardsData)) continue;

        $memberMap = [];
        foreach ($membersData as $m) {
            if (isset($m['id'], $m['fullName'])) {
                $memberMap[$m['id']] = $m['fullName'];
            }
        }

        foreach ($cardsData as $card) {

            // STATUS
            $status = "Pending";
            if (!empty($card['dueComplete'])) {
                $status = "Completed";
            } elseif (!empty($card['due']) && strtotime($card['due']) < time()) {
                $status = "Overdue";
            }

            // OWNER
            $ownerNames = [];
            if (!empty($card['idMembers'])) {
                foreach ($card['idMembers'] as $id) {
                    if (isset($memberMap[$id])) {
                        $ownerNames[] = $memberMap[$id];
                    }
                }
            }
            $owner = count($ownerNames) ? implode(", ", $ownerNames) : "Unassigned";

            // CATEGORY
            $categories = [];
            if (!empty($card['labels'])) {
                foreach ($card['labels'] as $label) {
                    if (!empty($label['name'])) {
                        $categories[] = $label['name'];
                    }
                }
            }
            $category = count($categories) ? implode(", ", $categories) : "Uncategorized";

            $reports[] = [
                "name"     => $card['name'] ?? '',
                "date"     => isset($card['dateLastActivity'])
                    ? date("Y-m-d", strtotime($card['dateLastActivity']))
                    : '',
                "owner"    => $owner,
                "status"   => $status,
                "category" => $category
            ];
        }

        // cleanup
        foreach ($types as $ch) {
            curl_multi_remove_handle($multi, $ch);
            curl_close($ch);
        }
    }

    curl_multi_close($multi);

    return $reports;
}

// ==============================
// 🌐 ALL ACCOUNTS
// ==============================
if ($selectedAccount === 'all') {

    $allReports = [];

    foreach ($accounts as $key => $acc) {
        $trello = $acc['TRELLO'];
        $data   = fetchTrelloReportsParallel(
            $trello['API_KEY'],
            $trello['TOKEN'],
            $trello['BOARD_IDS']
        );

        foreach ($data as &$item) {
            $item['account'] = $key;
        }

        $allReports = array_merge($allReports, $data);
    }

    // Only cache if we got real data
    if (!empty($allReports)) {
        file_put_contents($cacheFile, json_encode($allReports));
    }

    echo json_encode($allReports);
    exit;
}

// ==============================
// 🎯 SINGLE ACCOUNT
// ==============================
if (!isset($accounts[$selectedAccount])) {
    echo json_encode(["error" => "Invalid account"]);
    exit;
}

$trello = $accounts[$selectedAccount]['TRELLO'];

$data = fetchTrelloReportsParallel(
    $trello['API_KEY'],
    $trello['TOKEN'],
    $trello['BOARD_IDS']
);

// Only cache if we got real data
if (!empty($data)) {
    file_put_contents($cacheFile, json_encode($data));
}

echo json_encode($data);