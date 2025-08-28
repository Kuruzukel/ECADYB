<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;

function respond($success, $message = '', $data = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

$mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';

try {
    $client = new Client($mongoUrl);
    $db = $client->Departments;
    $collection = $db->DashboardAssets;

    // Fetch only necessary fields
    $cursor = $collection->find(
        ['type' => 'logo_container'],
        [
            'projection' => ['slot' => 1, 'url' => 1],
            'sort' => ['slot' => 1] // sort logos by slot order
        ]
    );

    $items = [];
    foreach ($cursor as $doc) {
        $items[] = [
            'slot' => isset($doc['slot']) ? (int)$doc['slot'] : 0,
            'url'  => isset($doc['url']) ? (string)$doc['url'] : ''
        ];
    }

    respond(true, 'Logos fetched successfully', ['items' => $items]);
} catch (Exception $e) {
    respond(false, 'Failed to fetch logos', ['error' => $e->getMessage()]);
}