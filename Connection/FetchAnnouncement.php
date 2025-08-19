<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone to ensure consistent date handling
date_default_timezone_set('Asia/Manila'); // Adjust this to your local timezone

require __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;

header('Content-Type: application/json');

try {
    // Get MongoDB connection string from environment variable
    $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://localhost:27017';
    
    // Connect to MongoDB
    $client = new Client($mongoUrl);
    $collection = $client->Announcement->Calendar;

    // Get all active announcements, sorted by creation date (newest first)
    $cursor = $collection->find(['status' => 'active'], ['sort' => ['created_at' => -1]]);

    $announcements = [];
    foreach ($cursor as $document) {
        // Debug: Log the date being retrieved
        error_log("Retrieved announcement date: " . $document['date']);
        
        $announcements[] = [
            'id' => (string)$document['_id'],
            'title' => $document['title'],
            'message' => $document['message'],
            'date' => $document['date'],
            'time' => $document['time'],
            'created_at' => $document['created_at']->toDateTime()->format('Y-m-d H:i:s'),
            'type' => 'announcement'
        ];
    }

    echo json_encode([
        'success' => true,
        'announcements' => $announcements
    ]);

} catch (Exception $e) {
    // Log error for debugging
    error_log("Fetch announcements error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>