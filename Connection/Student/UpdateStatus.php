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

require_once __DIR__ . '/../Configuration/EnvLoader.php';
$mongoUrl = getMongoUrl();
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

    $studentId  = trim($input['student_id'] ?? '');
    $collection = trim($input['collection'] ?? '');
    $status     = trim($input['status'] ?? '');
    $academicYear = trim($input['academic_year'] ?? '');

    if (!$studentId || !$collection || !$status) {
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

        $filter = [
            '$or' => [
                ['student_id' => $studentId],
                ['student id' => $studentId]
            ]
        ];

        // Add academic year filter if provided
        if (!empty($academicYear)) {
            $filter['academic year'] = $academicYear;
        }

        $update = ['$set' => ['status' => $status]];

        $result = $coll->updateOne($filter, $update);

        error_log("UpdateStatus - Database: " . $dbName . ", Collection: " . $collection . ", Student ID: " . $studentId . ", Status: " . $status);
        error_log("UpdateStatus - Matched: " . $result->getMatchedCount() . ", Modified: " . $result->getModifiedCount());

        if ($result->getMatchedCount() > 0) {
            http_response_code(200);
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

            http_response_code(404);
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
