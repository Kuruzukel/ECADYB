<?php
// Manual cleanup script to remove orphaned top management photos
// Run this if you want to immediately clean up photos without matching CSV entries

require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

try {
    $template = isset($_GET['template']) ? (int)$_GET['template'] : 2; // Default to template 2 based on your screenshot
    $mongoDbName = "BatchTemplate" . $template;
    $mongoUrl = getenv('MONGODB_URI') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';

    $mongoClient = new MongoDB\Client($mongoUrl);
    
    // Get all names from messages collection
    $messageCollection = $mongoClient->$mongoDbName->top_management_message;
    $messages = $messageCollection->find([], ['projection' => ['name' => 1]]);
    
    $validNames = [];
    foreach ($messages as $message) {
        if (isset($message['name'])) {
            $validNames[] = $message['name'];
        }
    }
    
    echo "Valid names in CSV: " . implode(', ', $validNames) . "<br><br>";
    
    // Find orphaned photos
    $photosCollection = $mongoClient->$mongoDbName->top_management_photos;
    $orphanedPhotos = $photosCollection->find([
        'name' => ['$nin' => $validNames]
    ]);
    
    $orphanedCount = 0;
    echo "Orphaned photos found:<br>";
    foreach ($orphanedPhotos as $photo) {
        echo "- " . ($photo['name'] ?? 'Unknown') . " (Position: " . ($photo['position'] ?? 'Unknown') . ")<br>";
        $orphanedCount++;
    }
    
    if ($orphanedCount > 0) {
        // Delete orphaned photos
        $deleteResult = $photosCollection->deleteMany([
            'name' => ['$nin' => $validNames]
        ]);
        
        echo "<br><strong>Deleted " . $deleteResult->getDeletedCount() . " orphaned photos!</strong><br>";
    } else {
        echo "<br><strong>No orphaned photos found.</strong><br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
