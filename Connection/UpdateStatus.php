<?php
require __DIR__ . '/../vendor/autoload.php';
use MongoDB\Client;

header('Content-Type: application/json');

// ----------------------
// MongoDB connection
// ----------------------
$mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
$client   = new Client($mongoUrl);
$db       = $client->Departments;

// ----------------------
// Handle POST request
// ----------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentId  = $_POST['student_id'] ?? null;
    $collection = $_POST['collection'] ?? null;
    $status     = $_POST['status'] ?? null;

    // Debugging help: log raw POST data (remove later in production)
    // file_put_contents(__DIR__ . "/debug.log", print_r($_POST, true), FILE_APPEND);

    if ($studentId && $collection && $status) {
        $coll = $db->$collection;

        // Try both possible field names
        $filter = [
            '$or' => [
                ['student_id' => $studentId],
                ['student id' => $studentId]
            ]
        ];

        $update = ['$set' => ['status' => $status]];

        $result = $coll->updateOne($filter, $update);

        echo json_encode([
            "success"  => true,
            "student_id" => $studentId,
            "collection" => $collection,
            "status"   => $status,
            "matched"  => $result->getMatchedCount(),
            "modified" => $result->getModifiedCount()
        ]);
        exit;
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Missing required data",
            "received" => $_POST // so you can see what PHP got
        ]);
        exit;
    }
}

// ----------------------
// Fallback
// ----------------------
echo json_encode(["success" => false, "message" => "Invalid request method"]);