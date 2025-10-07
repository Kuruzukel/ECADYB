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

    $studentId  = trim($input['student_id'] ?? '');
    $collection = trim($input['collection'] ?? '');
    $status     = trim($input['status'] ?? '');
    $template   = trim($input['template'] ?? '1');

    if (!$studentId || !$collection || !$status) {
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

        $filter = [
            '$or' => [
                ['student_id' => $studentId],
                ['student id' => $studentId]
            ]
        ];

        $update = ['$set' => ['status' => $status]];

        $result = $coll->updateOne($filter, $update);

        error_log("UpdateStatus - Database: " . $dbName . ", Collection: " . $collection . ", Student ID: " . $studentId . ", Status: " . $status);
        error_log("UpdateStatus - Matched: " . $result->getMatchedCount() . ", Modified: " . $result->getModifiedCount());

        if ($result->getMatchedCount() > 0) {
            echo json_encode([
                "success"    => true,
                "message"    => "Status updated successfully",
                "lookup_id"  => $studentId,
                "collection" => $collection,
                "database"   => $dbName,
                "matched"    => $result->getMatchedCount(),
                "modified"   => $result->getModifiedCount()
            ]);
        } else {
            // Let's debug what documents exist in this collection
            $allStudents = $coll->find([], [
                'projection' => ['student id' => 1, 'student_id' => 1, 'status' => 1],
                'limit' => 5
            ]);

            $ids = [];
            foreach ($allStudents as $s) {
                if (isset($s['student id'])) {
                    $ids[] = "[space] " . $s['student id'] . " (status: " . ($s['status'] ?? 'N/A') . ")";
                }
                if (isset($s['student_id'])) {
                    $ids[] = "[underscore] " . $s['student_id'] . " (status: " . ($s['status'] ?? 'N/A') . ")";
                }
            }

            error_log("UpdateStatus - No matching student found. Available IDs in $collection: " . implode(', ', $ids));

            echo json_encode([
                "success"    => false,
                "message"    => "No matching student found",
                "lookup_id"  => $studentId,
                "collection" => $collection,
                "database"   => $dbName,
                "debug_ids"  => $ids
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
