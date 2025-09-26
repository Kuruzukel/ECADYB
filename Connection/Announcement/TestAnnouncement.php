<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;
use MongoDB\BSON\UTCDateTime;

echo "<h1>Announcement System Test</h1>";

try {
    echo "<h2>Testing MongoDB Connection</h2>";

    $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
    $client = new Client($mongoUrl);
    $collection = $client->Announcement->Calendar;

    echo "<p style='color: green;'>✓ MongoDB connection successful</p>";

    echo "<h2>Testing Announcement Insertion</h2>";

    $testAnnouncement = [
        'title' => 'Test Announcement',
        'message' => 'This is a test announcement to verify the system is working.',
        'date' => date('Y-m-d'),
        'time' => date('H:i:s'),
        'created_at' => new UTCDateTime((int)(microtime(true) * 1000)),
        'status' => 'active',
        'type' => 'announcement'
    ];

    $result = $collection->insertOne($testAnnouncement);
    if ($result->getInsertedCount() > 0) {
        echo "<p style='color: green;'>✓ Test announcement inserted successfully</p>";
        $insertedId = $result->getInsertedId();
    } else {
        echo "<p style='color: red;'>✗ Failed to insert test announcement</p>";
    }

    echo "<h2>Testing Announcement Fetching</h2>";
    $cursor = $collection->find(['status' => 'active']);
    $count = $collection->countDocuments(['status' => 'active']);
    echo "<p style='color: green;'>✓ Found {$count} active announcements</p>";

    echo "<h2>Current Announcements</h2>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Title</th><th>Message</th><th>Date</th><th>Time</th><th>Created</th></tr>";

    foreach ($cursor as $doc) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($doc['title']) . "</td>";
        echo "<td>" . htmlspecialchars($doc['message']) . "</td>";
        echo "<td>" . htmlspecialchars($doc['date']) . "</td>";
        echo "<td>" . htmlspecialchars($doc['time'] ?? '') . "</td>";
        echo "<td>" . $doc['created_at']->toDateTime()->format('Y-m-d H:i:s') . "</td>";
        echo "</tr>";
    }

    echo "</table>";

    // Clean up test announcement
    if (isset($insertedId)) {
        $collection->deleteOne(['_id' => $insertedId]);
        echo "<p style='color: blue;'>✓ Test announcement cleaned up</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

echo "<h2>Test Complete</h2>";
echo "<p>If you see green checkmarks above, the announcement system is working correctly.</p>";