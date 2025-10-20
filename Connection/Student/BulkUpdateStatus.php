<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

ini_set('display_errors', 0);
error_reporting(E_ALL);

require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

$mongoUrl = getenv('MONGO_URL') ?: getenv('MONGODB_URI') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
try {
    $client = new Client($mongoUrl);
} catch (Exception $e) {
    http_response_code(500);
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
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Invalid JSON input",
            "raw_input" => $inputRaw
        ]);
        exit;
    }

    $collection = trim($input['collection'] ?? '');
    $status     = trim($input['status'] ?? '');
    $academicYear = trim($input['academic_year'] ?? '');
    $statusFilter = $input['status_filter'] ?? null;

    if (!$collection || !$status) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Missing required data",
            "received" => $input
        ]);
        exit;
    }

    // Use ECADYB database instead of BatchTemplate databases
    $dbName = "ECADYB";
    $db = $client->$dbName;

    try {
        $coll = $db->$collection;

        $filter = [];

        // Add academic year filter if provided
        if (!empty($academicYear)) {
            $filter['academic year'] = $academicYear;
        }

        if ($statusFilter && $statusFilter !== '') {
            if ($statusFilter === 'active') {
                $filter['status'] = 'Active';
            } else if ($statusFilter === 'pending') {
                $filter['status'] = 'Pending';
            }
        }

        $update = ['$set' => ['status' => $status]];

        $result = $coll->updateMany($filter, $update);

        error_log("BulkUpdateStatus - Database: " . $dbName . ", Collection: " . $collection . ", Status: " . $status);
        error_log("BulkUpdateStatus - Matched: " . $result->getMatchedCount() . ", Modified: " . $result->getModifiedCount());

        http_response_code(200);
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
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Database error: " . $e->getMessage()
        ]);
    }

    exit;
}

http_response_code(405);
echo json_encode([
    "success" => false,
    "message" => "Invalid request method"
]);
exit;
