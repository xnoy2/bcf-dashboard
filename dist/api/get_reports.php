<?php

session_start();
header("Content-Type: application/json");

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


$apiKey = $config['TRELLO']['API_KEY'];
$token = $config['TRELLO']['TOKEN'];
$boardIds = $config['TRELLO']['BOARD_IDS'];

$reports = [];

foreach ($boardIds as $boardId) {

    // ================= GET MEMBERS =================
    $membersUrl = "https://api.trello.com/1/boards/$boardId/members?key=$apiKey&token=$token";
    $membersResponse = file_get_contents($membersUrl);
    $membersData = json_decode($membersResponse, true);

    $memberMap = [];

    foreach ($membersData as $member) {
        $memberMap[$member['id']] = $member['fullName'];
    }

    // ================= GET CARDS =================
    $url = "https://api.trello.com/1/boards/$boardId/cards?key=$apiKey&token=$token";
    $response = file_get_contents($url);
    $cards = json_decode($response, true);

    foreach ($cards as $card) {

        // ================= STATUS =================
        $status = "Pending";

        if (!empty($card['dueComplete']) && $card['dueComplete']) {
            $status = "Completed";
        } elseif (!empty($card['due']) && strtotime($card['due']) < time()) {
            $status = "Overdue";
        }

        // ================= OWNER =================
        $ownerNames = [];

        if (!empty($card['idMembers'])) {
            foreach ($card['idMembers'] as $memberId) {
                if (isset($memberMap[$memberId])) {
                    $ownerNames[] = $memberMap[$memberId];
                }
            }
        }

        $owner = count($ownerNames) ? implode(", ", $ownerNames) : "Unassigned";

        // ================= CATEGORY (LABELS) =================
        $categories = [];

        if (!empty($card['labels'])) {
            foreach ($card['labels'] as $label) {
                if (!empty($label['name'])) {
                    $categories[] = $label['name'];
                }
            }
        }

        $category = count($categories) ? implode(", ", $categories) : "Uncategorized";

        // ================= OUTPUT =================
        $reports[] = [
            "name" => $card['name'],
            "date" => date("Y-m-d", strtotime($card['dateLastActivity'])),
            "owner" => $owner,
            "status" => $status,
            "category" => $category
        ];
    }
}

echo json_encode($reports);