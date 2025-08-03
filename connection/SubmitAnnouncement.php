<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone to ensure consistent date handling
date_default_timezone_set('Asia/Manila'); // Adjust this to your local timezone

require __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Connect to MongoDB
        $client = new Client("mongodb://localhost:27017");
        $collection = $client->Announcement->Calendar;

        // Get form data
        $title = trim($_POST['title'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $date = $_POST['date'] ?? '';
        $time = $_POST['time'] ?? '';

        // Validate required fields
        if (empty($title) || empty($message)) {
            throw new Exception("Title and message are required");
        }

        // Check if there are already 5 announcements for the selected date
        $selectedDate = $date ?: date('Y-m-d');
        $existingCount = $collection->countDocuments([
            'date' => $selectedDate,
            'status' => 'active'
        ]);

        if ($existingCount >= 5) {
            throw new Exception("Cannot post announcement. Maximum of 5 announcements per day allowed. This date already has {$existingCount} announcements.");
        }

        // Create announcement document
        $announcement = [
            'title' => $title,
            'message' => $message,
            'date' => $date ?: date('Y-m-d'), // Use current date if no date provided
            'time' => $time,
            'created_at' => new MongoDB\BSON\UTCDateTime(),
            'status' => 'active',
            'type' => 'announcement'
        ];

        // Debug: Log the date being saved
        error_log("Saving announcement with date: " . $announcement['date']);

        // Insert into database
        $result = $collection->insertOne($announcement);

        if ($result->getInsertedCount() > 0) {
            // Success response
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
        // Log error for debugging
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

// If not POST request, redirect to create announcement page
header('Location: ../Admin/Components/CreateAnnouncement.php');
exit;
?>