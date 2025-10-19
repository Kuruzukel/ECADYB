<?php

ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function respond($success, $message = '', $data = [])
{
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    respond(false, 'Invalid request method');
}

require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

try {
    $mongoUrl = getenv('MONGODB_URI') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
    error_log("FetchCovers.php using MongoDB URL: $mongoUrl");

    $client = new Client($mongoUrl, [
        'serverSelectionTimeoutMS' => 1000,
        'connectTimeoutMS' => 1000,
        'socketTimeoutMS' => 2000,
        'retryReads' => true
    ]);

    $dbName = "Yearbook";
    $db = $client->$dbName;
    $collection = $db->Covers;

    error_log("FetchCovers.php using database: $dbName, collection: Covers");

    $cursor = $collection->find(
        [],
        [
            'projection' => [
                'slot' => 1,
                'front_url' => 1,
                'back_url' => 1,
                'background_url' => 1,
                'upload_time' => 1
            ],
            'limit' => 8
        ]
    );

    $items = [];

    foreach ($cursor as $doc) {
        error_log("FetchCovers.php found document: " . json_encode($doc));
        $slot = (int)($doc['slot'] ?? 0);

        $version = '';
        if (isset($doc['upload_time'])) {
            try {
                $uploadMs = (int) ((string) $doc['upload_time']->toDateTime()->format('Uu'));
                $version = $uploadMs > 0 ? ('?v=' . $uploadMs) : '';
            } catch (Exception $e) {
                $version = '';
            }
        }

        if ($slot >= 1 && $slot <= 7) {
            $front = isset($doc['front_url']) ? (string)$doc['front_url'] : '';
            $back  = isset($doc['back_url']) ? (string)$doc['back_url'] : '';
            $items[] = [
                'slot' => $slot,
                'front_url' => $front ? ($front . $version) : '',
                'back_url' => $back ? ($back . $version) : ''
            ];
        } elseif ($slot === 8) {
            $backgroundUrl = isset($doc['background_url']) ? (string)$doc['background_url'] : '';
            $backgroundWithV = $backgroundUrl ? ($backgroundUrl . $version) : '';
            $items[] = [
                'slot' => 8,
                'front_url' => $backgroundWithV,
                'background_url' => $backgroundWithV
            ];
        }
    }

    error_log("FetchCovers.php found " . count($items) . " items");

    respond(true, 'Covers fetched', ['items' => array_values($items)]);
} catch (Exception $e) {
    error_log("FetchCovers.php error: " . $e->getMessage());
    respond(false, 'Failed to fetch: ' . $e->getMessage());
}