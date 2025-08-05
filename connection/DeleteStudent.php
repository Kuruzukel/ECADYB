<?php
require __DIR__ . '/../../vendor/autoload.php'; 

use MongoDB\Client;

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['student_id']) || !isset($input['collection'])) {
    echo json_encode(["success" => false, "message" => "Missing required parameters"]);
    exit;
}

$studentId = $input['student_id'];
$collectionName = $input['collection'];

try {
    // Get MongoDB connection string from environment variable
    $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://localhost:27017';
    
    // Connect to MongoDB
    $client = new Client($mongoUrl);
    $db = $client->Departments;
    
    // Get the collection
    $collection = $db->$collectionName;
    
    // Delete the student by student ID
    $result = $collection->deleteOne(['student id' => $studentId]);
    
    if ($result->getDeletedCount() > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Student deleted successfully"
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Student not found"
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?> 