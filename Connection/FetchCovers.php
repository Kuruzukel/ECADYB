<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;

function respond($success, $message = '', $data = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

$template = isset($_GET['template']) ? (int)$_GET['template'] : 1;

$mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';

try {
    $client = new Client($mongoUrl);
    $db = $client->Departments;
    $collection = $db->YearbookCovers;

    $cursor = $collection->find(['template' => $template]);
    $items = [];

    foreach ($cursor as $doc) {
        $items[] = [
            'template'   => (int)($doc['template'] ?? 1),
            'slot'       => (int)($doc['slot'] ?? 0),
            'front_url'  => isset($doc['front_url']) ? (string)$doc['front_url'] : '',
            'back_url'   => isset($doc['back_url']) ? (string)$doc['back_url'] : '',
        ];
    }

    respond(true, 'OK', ['items' => $items]);
} catch (Exception $e) {
    // Important: always return JSON, never raw HTML
    respond(false, 'Failed to fetch covers: ' . $e->getMessage());
}