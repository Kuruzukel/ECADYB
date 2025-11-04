<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

ini_set('display_errors', 0);
error_reporting(E_ALL);

date_default_timezone_set('Asia/Manila');

require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../Configuration/DateTimeHelper.php';

use MongoDB\Client;

try {
    require_once __DIR__ . '/../Configuration/EnvLoader.php';
    $mongoUrl = getMongoUrl();

    $client = new Client($mongoUrl);
    $db = $client->ECADYB;
    $collection = $db->Announcement;

    $cursor = $collection->find(
        ['status' => 'active'],
        ['sort' => ['created_at' => -1]]
    );

    $announcements = [];

    foreach ($cursor as $document) {
        error_log("Retrieved announcement date: " . $document['date']);

        $announcements[] = [
            'id' => (string)$document['_id'],
            'title' => $document['title'],
            'message' => $document['message'],
            'date' => $document['date'],
            'time' => $document['time'] ?? '',
            'created_at' => isset($document['created_at']) ? convertToPhilippineTimeCustom($document['created_at']) : null,
            'type' => $document['type'] ?? 'announcement'
        ];
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'announcements' => $announcements
    ]);
} catch (Exception $e) {
    error_log("Fetch announcements error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching announcements: ' . $e->getMessage()
    ]);
}
