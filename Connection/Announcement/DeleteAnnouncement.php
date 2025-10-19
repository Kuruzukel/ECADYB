<?php
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

require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;
use MongoDB\BSON\ObjectId;

date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST.'
    ]);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    error_log("Delete announcement input: " . json_encode($input));

    if (empty($input['id'])) {
        throw new Exception('Missing required parameter: id');
    }

    $eventId = $input['id'];
    $eventDate = $input['date'] ?? null;

    error_log("Attempting to delete announcement with ID: $eventId, Date: " . ($eventDate ?? 'N/A'));

    if (!preg_match('/^[a-f\d]{24}$/i', $eventId)) {
        throw new Exception('Invalid ObjectId format: ' . $eventId);
    }

    $objectId = new ObjectId($eventId);

    $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
    $client = new Client($mongoUrl);
    $collection = $client->Announcement->Calendar;

    $deleteFilter = ['_id' => $objectId];
    if ($eventDate !== null) {
        $deleteFilter['date'] = $eventDate;
    }

    error_log("Delete filter: " . json_encode($deleteFilter));

    $result = $collection->deleteOne($deleteFilter);
    error_log("Delete result - deleted count: " . $result->getDeletedCount());

    if ($result->getDeletedCount() > 0) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Announcement deleted successfully'
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Announcement not found or already deleted'
        ]);
    }
} catch (Exception $e) {
    error_log("Delete announcement error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error deleting announcement: ' . $e->getMessage()
    ]);
}
