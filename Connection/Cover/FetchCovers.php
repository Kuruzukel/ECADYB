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
require_once __DIR__ . '/../Configuration/DateTimeHelper.php';

use MongoDB\Client;

try {
    $batchYear = isset($_GET['batch_year']) ? trim($_GET['batch_year']) : '';
    $template = isset($_GET['template']) ? (int)$_GET['template'] : 1;

    error_log("FetchCovers.php received parameters: batch_year='$batchYear', template=$template");

    require_once __DIR__ . '/../Configuration/EnvLoader.php';
    $mongoUrl = getMongoUrl();
    error_log("FetchCovers.php using MongoDB URL: $mongoUrl");

    $client = new Client($mongoUrl, [
        'serverSelectionTimeoutMS' => 10000,
        'connectTimeoutMS' => 10000,
        'socketTimeoutMS' => 20000,
        'retryReads' => true
    ]);

    $dbName = "ECADYB";
    $db = $client->$dbName;
    $collection = $db->Yearbook_Covers;

    error_log("FetchCovers.php using database: $dbName, collection: Covers");

    $filter = ['template' => $template];
    if (!empty($batchYear)) {
        $filter['batch_year'] = $batchYear;
    }

    error_log("FetchCovers.php query filter: " . json_encode($filter));

    $cursor = $collection->find(
        $filter,
        [
            'projection' => [
                'slot' => 1,
                'front_url' => 1,
                'front_filename' => 1,
                'front_original_name' => 1,
                'front_side' => 1,
                'back_url' => 1,
                'back_filename' => 1,
                'back_original_name' => 1,
                'back_side' => 1,
                'background_url' => 1,
                'background_filename' => 1,
                'background_original_name' => 1,
                'background_side' => 1,
                'batch_year' => 1,
                'template' => 1,
                'completion_date' => 1,
                'upload_time' => 1
            ]
        ]
    );

    $items = [];

    foreach ($cursor as $doc) {
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

        $batchYear = isset($doc['batch_year']) ? (string)$doc['batch_year'] : '';
        $template = isset($doc['template']) ? (int)$doc['template'] : 1;
        $completionDate = null;
        $uploadTime = null;

        if (isset($doc['completion_date'])) {
            $completionDate = convertToPhilippineTimeCustom($doc['completion_date']);
        }

        if (isset($doc['upload_time'])) {
            $uploadTime = convertToPhilippineTimeCustom($doc['upload_time']);
        }

        if ($slot >= 1 && $slot <= 7) {
            $front = isset($doc['front_url']) ? (string)$doc['front_url'] : '';
            $back  = isset($doc['back_url']) ? (string)$doc['back_url'] : '';

            $frontFilename = isset($doc['front_filename']) ? (string)$doc['front_filename'] : '';
            $frontOriginalName = isset($doc['front_original_name']) ? (string)$doc['front_original_name'] : '';
            $frontSide = isset($doc['front_side']) ? (string)$doc['front_side'] : '';

            $backFilename = isset($doc['back_filename']) ? (string)$doc['back_filename'] : '';
            $backOriginalName = isset($doc['back_original_name']) ? (string)$doc['back_original_name'] : '';
            $backSide = isset($doc['back_side']) ? (string)$doc['back_side'] : '';

            $items[] = [
                'slot' => $slot,
                'front_url' => $front ? ($front . $version) : '',
                'front_filename' => $frontFilename,
                'front_original_name' => $frontOriginalName,
                'front_side' => $frontSide,
                'back_url' => $back ? ($back . $version) : '',
                'back_filename' => $backFilename,
                'back_original_name' => $backOriginalName,
                'back_side' => $backSide,
                'batch_year' => $batchYear,
                'template' => $template,
                'completion_date' => $completionDate,
                'upload_time' => $uploadTime
            ];
        } elseif ($slot === 8) {
            $backgroundUrl = isset($doc['background_url']) ? (string)$doc['background_url'] : '';
            $backgroundWithV = $backgroundUrl ? ($backgroundUrl . $version) : '';

            $backgroundFilename = isset($doc['background_filename']) ? (string)$doc['background_filename'] : '';
            $backgroundOriginalName = isset($doc['background_original_name']) ? (string)$doc['background_original_name'] : '';
            $backgroundSide = isset($doc['background_side']) ? (string)$doc['background_side'] : '';

            $items[] = [
                'slot' => 8,
                'front_url' => $backgroundWithV,
                'background_url' => $backgroundWithV,
                'background_filename' => $backgroundFilename,
                'background_original_name' => $backgroundOriginalName,
                'background_side' => $backgroundSide,
                'batch_year' => $batchYear,
                'template' => $template,
                'completion_date' => $completionDate,
                'upload_time' => $uploadTime
            ];
        }
    }

    error_log("FetchCovers.php found " . count($items) . " items with Philippine time converted");

    respond(true, 'Covers fetched', ['items' => array_values($items)]);
} catch (Exception $e) {
    error_log("FetchCovers.php error: " . $e->getMessage());
    respond(false, 'Failed to fetch: ' . $e->getMessage());
}
