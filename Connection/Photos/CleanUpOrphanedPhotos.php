<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

ob_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

try {
    $mongoDbName = "ECADYB";
    $mongoUrl = getenv('MONGODB_URI') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';

    $mongoClient = new MongoDB\Client($mongoUrl);

    $messageCollection = $mongoClient->$mongoDbName->Top_Management_Messages;
    $messages = $messageCollection->find([], ['projection' => ['name' => 1]]);

    $validNames = [];
    foreach ($messages as $message) {
        if (isset($message['name'])) {
            $validNames[] = $message['name'];
        }
    }

    $photosCollection = $mongoClient->$mongoDbName->Top_Management_Photos;
    $orphanedPhotos = $photosCollection->find([
        'name' => ['$nin' => $validNames]
    ]);

    $orphanedCount = 0;
    $orphanedList = [];
    foreach ($orphanedPhotos as $photo) {
        $orphanedList[] = [
            'name' => $photo['name'] ?? 'Unknown',
            'position' => $photo['position'] ?? 'Unknown'
        ];
        $orphanedCount++;
    }

    $deletedCount = 0;
    if ($orphanedCount > 0) {
        $deleteResult = $photosCollection->deleteMany([
            'name' => ['$nin' => $validNames]
        ]);
        $deletedCount = $deleteResult->getDeletedCount();
    }

    // Clean output buffer and return JSON
    while (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => $orphanedCount > 0 ? "Deleted {$deletedCount} orphaned photos" : "No orphaned photos found",
        'data' => [
            'orphaned_count' => $orphanedCount,
            'deleted_count' => $deletedCount,
            'orphaned_photos' => $orphanedList,
            'valid_names' => $validNames
        ]
    ]);
    exit;
} catch (Exception $e) {
    while (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
}
