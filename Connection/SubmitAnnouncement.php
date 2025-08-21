<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('Asia/Manila');

require __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;
use MongoDB\BSON\UTCDateTime;

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // MongoDB connection
        $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://localhost:27017';
        $client = new Client($mongoUrl);
        $collection = $client->Announcement->Calendar;

        // Get POST data
        $title = trim($_POST['title'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $date = $_POST['date'] ?? '';
        $time = $_POST['time'] ?? '';

        // Validate required fields
        if (empty($title) || empty($message)) {
            throw new Exception("Title and message are required");
        }

        // Use current date if not provided
        $selectedDate = $date ?: date('Y-m-d');
        $selectedTime = $time ?: date('H:i:s');

        // Check max 5 announcements per day
        $existingCount = $collection->countDocuments([
            'date' => $selectedDate,
            'status' => 'active'
        ]);

        if ($existingCount >= 5) {
            throw new Exception("Cannot post announcement. Maximum of 5 announcements per day allowed. This date already has {$existingCount} announcements.");
        }

        // Create announcement
        $announcement = [
            'title' => $title,
            'message' => $message,
            'date' => $selectedDate,
            'time' => $selectedTime,
            'created_at' => new \MongoDB\BSON\UTCDateTime((int)(microtime(true) * 1000)), // fully qualified name
            'status' => 'active',
            'type' => 'announcement'
        ];

        // Log for debugging
        error_log("Saving announcement: " . json_encode($announcement));

        // Insert into database
        $result = $collection->insertOne($announcement);

        if ($result->getInsertedCount() > 0) {
            $response = [
                'success' => true,
                'message' => 'Announcement posted successfully!',
                'id' => (string)$result->getInsertedId(),
                'announcement' => $announcement
            ];
        } else {
            throw new Exception("Failed to insert announcement");
        }

    } catch (Exception $e) {
        error_log("Announcement submission error: " . $e->getMessage());
        $response = [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
    }

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Redirect if not POST
header('Location: ../Admin/Components/CreateAnnouncement.php');
exit;