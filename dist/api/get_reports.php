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
$config = require __DIR__ . '/../auth/config.php';
$accounts = $config['ACCOUNTS'] ?? [];

$selectedAccount = $_GET['account'] ?? 'bcf';

// ==============================
// ⚡ CACHE (VERY IMPORTANT)
// ==============================
$cacheFile = __DIR__ . "/cache_reports_{$selectedAccount}.json";
$cacheTime = 60;

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    echo file_get_contents($cacheFile);
    exit;
}

// ==============================
// 🚀 PARALLEL FETCH FUNCTION
// ==============================
function fetchTrelloReportsParallel($apiKey, $token, $boardIds)
{
    $multi = curl_multi_init();
    $handles = [];

    // 🔥 PREPARE REQUESTS
    foreach ($boardIds as $boardId) {

        $urls = [
            "members" => "https://api.trello.com/1/boards/$boardId/members?key=$apiKey&token=$token",
            "cards"   => "https://api.trello.com/1/boards/$boardId/cards?key=$apiKey&token=$token"
        ];

        foreach ($urls as $type => $url) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
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

        $membersData = json_decode(curl_multi_getcontent($types['members']), true) ?? [];
        $cardsData   = json_decode(curl_multi_getcontent($types['cards']), true) ?? [];

        $memberMap = [];

        foreach ($membersData as $m) {
            $memberMap[$m['id']] = $m['fullName'];
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
                "name" => $card['name'] ?? '',
                "date" => isset($card['dateLastActivity'])
                    ? date("Y-m-d", strtotime($card['dateLastActivity']))
                    : '',
                "owner" => $owner,
                "status" => $status,
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

        $data = fetchTrelloReportsParallel(
            $trello['API_KEY'],
            $trello['TOKEN'],
            $trello['BOARD_IDS']
        );

        foreach ($data as &$item) {
            $item['account'] = $key;
        }

        $allReports = array_merge($allReports, $data);
    }

    file_put_contents($cacheFile, json_encode($allReports));
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

// cache
file_put_contents($cacheFile, json_encode($data));

echo json_encode($data);