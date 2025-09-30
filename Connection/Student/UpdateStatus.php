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
    $db     = $client->Departments;
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

    $studentId  = trim($input['student_id'] ?? '');
    $collection = trim($input['collection'] ?? '');
    $status     = trim($input['status'] ?? '');

    if (!$studentId || !$collection || !$status) {
        echo json_encode([
            "success" => false,
            "message" => "Missing required data",
            "received" => $input
        ]);
        exit;
    }

    try {
        $coll = $db->$collection;

        // Use exact match for better reliability
        $filter = [
            '$or' => [
                ['student_id' => $studentId],
                ['student id' => $studentId]
            ]
        ];

        $update = ['$set' => ['status' => $status]];

        $result = $coll->updateOne($filter, $update);

        if ($result->getMatchedCount() > 0) {
            echo json_encode([
                "success"    => true,
                "message"    => "Status updated successfully",
                "lookup_id"  => $studentId,
                "collection" => $collection,
                "matched"    => $result->getMatchedCount(),
                "modified"   => $result->getModifiedCount()
            ]);
        } else {
            echo json_encode([
                "success"    => false,
                "message"    => "No matching student found",
                "lookup_id"  => $studentId,
                "collection" => $collection
            ]);
        }
    } catch (Exception $e) {
        error_log("UpdateStatus Error: " . $e->getMessage());
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