<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

date_default_timezone_set('Asia/Manila');

require __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;

header('Content-Type: application/json');

try {
    $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';

    $client = new Client($mongoUrl);
    $collection = $client->Announcement->Calendar;

    // Get all active announcements, sorted by creation date (newest first)
    $cursor = $collection->find(
        ['status' => 'active'],
        ['sort' => ['created_at' => -1]]
    );

    $announcements = [];

    foreach ($cursor as $document) {
        // Debug: Log the date being retrieved
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

    echo json_encode([
        'success' => true,
        'announcements' => $announcements
    ]);
} catch (Exception $e) {
    // Log error for debugging
    error_log("Fetch announcements error: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Error fetching announcements: ' . $e->getMessage()
    ]);
}
