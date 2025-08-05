<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;

// Set timezone
date_default_timezone_set('Asia/Manila');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Debug: Log the received input
    error_log("Delete announcement input: " . json_encode($input));
    
    if (!isset($input['id']) || !isset($input['date'])) {
        throw new Exception('Missing required parameters: id and date');
    }

    $eventId = $input['id'];
    $eventDate = $input['date'];
    
    // Debug: Log the parameters
    error_log("Attempting to delete announcement with ID: $eventId, Date: $eventDate");

    // Validate ObjectId format
    if (!preg_match('/^[a-f\d]{24}$/i', $eventId)) {
        throw new Exception('Invalid ObjectId format: ' . $eventId);
    }

    // Get MongoDB connection string from environment variable
    $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://localhost:27017';
    
    // Connect to MongoDB
    $client = new Client($mongoUrl);
    $database = $client->selectDatabase("Announcement");
    $collection = $database->selectCollection("Calendar");

    // Delete the announcement
    try {
        $objectId = new MongoDB\BSON\ObjectId($eventId);
    } catch (Exception $e) {
        throw new Exception('Invalid ObjectId: ' . $e->getMessage());
    }
    
    // First, let's find the document to see what date format is stored
    $document = $collection->findOne(['_id' => $objectId]);
    if ($document) {
        error_log("Found document with date: " . $document['date']);
    } else {
        error_log("No document found with ID: " . $eventId);
    }
    
    $deleteFilter = [
        '_id' => $objectId,
        'date' => $eventDate
    ];
    
    // Debug: Log the delete filter
    error_log("Delete filter: " . json_encode($deleteFilter));
    
    $result = $collection->deleteOne($deleteFilter);
    
    // Debug: Log the result
    error_log("Delete result - deleted count: " . $result->getDeletedCount());

    if ($result->getDeletedCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Announcement deleted successfully'
        ]);
    } else {
        // Try deleting by ID only (without date constraint)
        error_log("Trying delete by ID only...");
        $resultByIdOnly = $collection->deleteOne(['_id' => $objectId]);
        error_log("Delete by ID only result - deleted count: " . $resultByIdOnly->getDeletedCount());
        
        if ($resultByIdOnly->getDeletedCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Announcement deleted successfully (date constraint removed)'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Announcement not found or already deleted'
            ]);
        }
    }

} catch (Exception $e) {
    error_log("Delete announcement error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error deleting announcement: ' . $e->getMessage()
    ]);
}
?>