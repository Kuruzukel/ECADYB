<?php
require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
try {
    $client = new Client($mongoUrl);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to connect to MongoDB: " . $e->getMessage()
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $inputRaw = file_get_contents('php://input');
    $input = json_decode($inputRaw, true);

    if (!is_array($input)) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid JSON input",
            "raw_input" => $inputRaw
        ]);
        exit;
    }

    $collection = trim($input['collection'] ?? '');
    $status     = trim($input['status'] ?? '');
    $template   = trim($input['template'] ?? '1');
    $statusFilter = $input['status_filter'] ?? null;

    if (!$collection || !$status) {
        echo json_encode([
            "success" => false,
            "message" => "Missing required data",
            "received" => $input
        ]);
        exit;
    }

    $dbName = "BatchTemplate" . $template;
    $db = $client->$dbName;

    try {
        $coll = $db->$collection;

        // Build filter for bulk update
        $filter = [];

        // Apply status filter if provided
        if ($statusFilter && $statusFilter !== '') {
            if ($statusFilter === 'active') {
                $filter['status'] = 'Active';
            } else if ($statusFilter === 'pending') {
                $filter['status'] = 'Pending';
            }
        }

        $update = ['$set' => ['status' => $status]];

        // Perform bulk update
        $result = $coll->updateMany($filter, $update);

        error_log("BulkUpdateStatus - Database: " . $dbName . ", Collection: " . $collection . ", Status: " . $status);
        error_log("BulkUpdateStatus - Matched: " . $result->getMatchedCount() . ", Modified: " . $result->getModifiedCount());

        echo json_encode([
            "success"    => true,
            "message"    => "Status updated for " . $result->getModifiedCount() . " students successfully",
            "collection" => $collection,
            "database"   => $dbName,
            "matched"    => $result->getMatchedCount(),
            "modified"   => $result->getModifiedCount()
        ]);
    } catch (Exception $e) {
        error_log("BulkUpdateStatus Error: " . $e->getMessage());
        echo json_encode([
            "success" => false,
            "message" => "Database error: " . $e->getMessage()
        ]);
    }

    exit;
}

echo json_encode([
    "success" => false,
    "message" => "Invalid request method"
]);
exit;
