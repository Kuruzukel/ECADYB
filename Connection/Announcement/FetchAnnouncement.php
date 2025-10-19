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

use MongoDB\Client;

try {
    $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';

    $client = new Client($mongoUrl);
    $collection = $client->Announcement->Calendar;

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
            'created_at' => $document['created_at']->toDateTime()->format('Y-m-d H:i:s'),
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
