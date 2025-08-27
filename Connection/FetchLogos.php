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

    $cursor = $collection->find(['type' => 'logo_container']);
    $items = [];
    foreach ($cursor as $doc) {
        $items[] = [
            'slot' => (int)($doc['slot'] ?? 0),
            'url'  => (string)($doc['url'] ?? ''),
        ];
    }

    respond(true, 'OK', ['items' => $items]);
} catch (Exception $e) {
    respond(false, 'Failed to fetch logos: ' . $e->getMessage());
}

