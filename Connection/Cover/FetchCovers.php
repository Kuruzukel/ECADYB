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

session_start();

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
    $template = isset($_GET['template']) ? (int)$_GET['template'] : 1;

    error_log("FetchCovers.php received template parameter: $template");

    if ($template < 1 || $template > 3) {
        respond(false, 'Invalid template parameter. Must be 1, 2, or 3.');
    }

    $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';

    $client = new Client($mongoUrl, [
        'serverSelectionTimeoutMS' => 5000,
        'connectTimeoutMS' => 5000,
        'socketTimeoutMS' => 5000
    ]);

    $dbName = "BatchTemplate" . $template;
    $db = $client->$dbName;
    $collection = $db->YearbookCovers;

    error_log("FetchCovers.php using database: $dbName, collection: YearbookCovers");

    try {
        $databases = $client->listDatabases();
        $dbExists = false;
        foreach ($databases as $database) {
            if ($database->getName() === $dbName) {
                $dbExists = true;
                break;
            }
        }
        error_log("FetchCovers.php database $dbName exists: " . ($dbExists ? "true" : "false"));

        if ($dbExists) {
            $collections = $db->listCollections();
            $collectionNames = [];
            foreach ($collections as $collectionInfo) {
                $collectionNames[] = $collectionInfo->getName();
            }
            error_log("FetchCovers.php collections in $dbName: " . json_encode($collectionNames));
        }
    } catch (Exception $e) {
        error_log("FetchCovers.php error checking databases: " . $e->getMessage());
    }

    for ($slot = 1; $slot <= 7; $slot++) {
        $result = $collection->updateOne(
            ['template' => $template, 'slot' => $slot],
            [
                '$setOnInsert' => [
                    'template'        => $template,
                    'slot'            => $slot,
                    'front_url'       => '',
                    'back_url'        => '',
                    'front_thumb_url' => '',
                    'back_thumb_url'  => '',
                    'created_at'      => new MongoDB\BSON\UTCDateTime()
                ]
            ],
            ['upsert' => true]
        );

        error_log("FetchCovers.php slot $slot upsert result: matched=" . $result->getMatchedCount() . ", modified=" . $result->getModifiedCount() . ", upserted=" . $result->getUpsertedCount());
    }

    $result8 = $collection->updateOne(
        ['template' => $template, 'slot' => 8],
        [
            '$setOnInsert' => [
                'template'             => $template,
                'slot'                 => 8,
                'background_url'       => '',
                'background_thumb_url' => '',
                'created_at'           => new MongoDB\BSON\UTCDateTime()
            ]
        ],
        ['upsert' => true]
    );

    error_log("FetchCovers.php slot 8 upsert result: matched=" . $result8->getMatchedCount() . ", modified=" . $result8->getModifiedCount() . ", upserted=" . $result8->getUpsertedCount());

    $cursor = $collection->find(['template' => $template]);
    $items = [];

    foreach ($cursor as $doc) {
        $slot = (int)($doc['slot'] ?? 0);

        if ($slot >= 1 && $slot <= 7) {
            $items[] = [
                'template'        => (int)($doc['template'] ?? 1),
                'slot'            => $slot,
                'front_url'       => isset($doc['front_url']) ? (string)$doc['front_url'] : '',
                'back_url'        => isset($doc['back_url']) ? (string)$doc['back_url'] : '',
                'front_thumb_url' => isset($doc['front_thumb_url']) ? (string)$doc['front_thumb_url'] : '',
                'back_thumb_url'  => isset($doc['back_thumb_url']) ? (string)$doc['back_thumb_url'] : ''
            ];
        } elseif ($slot === 8) {
            $backgroundUrl = isset($doc['background_url']) ? (string)$doc['background_url'] : '';
            $backgroundThumb = isset($doc['background_thumb_url']) ? (string)$doc['background_thumb_url'] : '';

            $items[] = [
                'template'             => (int)($doc['template'] ?? 1),
                'slot'                 => 8,
                'front_url'            => $backgroundUrl,
                'front_thumb_url'      => $backgroundThumb,
                'background_url'       => $backgroundUrl,
                'background_thumb_url' => $backgroundThumb
            ];
        }
    }

    // Debug: Log the number of items found
    error_log("FetchCovers.php found " . count($items) . " items for template $template");

    // Debug: Log the items found
    error_log("FetchCovers.php items: " . json_encode($items));

    respond(true, 'Covers fetched successfully', ['items' => $items]);
} catch (Exception $e) {
    respond(false, 'Failed to fetch covers: ' . $e->getMessage());
}
