<?php
// Set headers first to allow CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Turn off error display for production
ini_set('display_errors', 0);
error_reporting(E_ALL);

date_default_timezone_set('Asia/Manila');

require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;
use MongoDB\BSON\UTCDateTime;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
        $client = new Client($mongoUrl);
        $collection = $client->Announcement->Calendar;

        $title = trim($_POST['title'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $date = $_POST['date'] ?? '';
        $time = $_POST['time'] ?? '';

        if (empty($title) || empty($message)) {
            throw new Exception("Title and message are required");
        }

        $selectedDate = $date ?: date('Y-m-d');
        $selectedTime = $time ?: date('H:i:s');

        $existingCount = $collection->countDocuments([
            'date' => $selectedDate,
            'status' => 'active'
        ]);

        if ($existingCount >= 5) {
            throw new Exception("Cannot post announcement. Maximum of 5 announcements per day allowed. This date already has {$existingCount} announcements.");
        }

        $announcement = [
            'title' => $title,
            'message' => $message,
            'date' => $selectedDate,
            'time' => $selectedTime,
            'created_at' => new \MongoDB\BSON\UTCDateTime((int)(microtime(true) * 1000)),
            'status' => 'active',
            'type' => 'announcement'
        ];

        error_log("Saving announcement: " . json_encode($announcement));

        $result = $collection->insertOne($announcement);

        if ($result->getInsertedCount() > 0) {
            http_response_code(200);
            $response = [
                'success' => true,
                'message' => 'Announcement posted successfully!',
                'id' => (string)$result->getInsertedId(),
                'announcement' => $announcement
            ];
        } else {
            throw new Exception("Failed to insert announcement");
        }
    } catch (Exception $e) {
        error_log("Announcement submission error: " . $e->getMessage());
        http_response_code(400);
        $response = [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }

    echo json_encode($response);
    exit;
}

http_response_code(405);
echo json_encode([
    'success' => false,
    'message' => 'Method not allowed'
]);
exit;