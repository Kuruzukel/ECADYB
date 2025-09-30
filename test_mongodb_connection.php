<?php
// Test MongoDB connection and data retrieval
require __DIR__ . '/vendor/autoload.php';

use MongoDB\Client;

echo "<h1>MongoDB Connection and Data Test</h1>\n";

try {
    // Test MongoDB connection
    $mongoUrl = getenv('MONGODB_URI') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
    echo "<p>Using MongoDB URL: $mongoUrl</p>\n";
    
    $client = new Client($mongoUrl, [
        'serverSelectionTimeoutMS' => 5000,
        'connectTimeoutMS' => 5000,
        'socketTimeoutMS' => 10000,
        'retryReads' => true
    ]);
    
    echo "<p style='color: green;'>✓ MongoDB client created successfully</p>\n";
    
    // Test each template database
    for ($template = 1; $template <= 3; $template++) {
        $dbName = "BatchTemplate$template";
        echo "<h2>Testing $dbName</h2>\n";
        
        try {
            $db = $client->$dbName;
            $collection = $db->YearbookCovers;
            
            // Count documents
            $count = $collection->countDocuments();
            echo "<p>Document count: $count</p>\n";
            
            // Show sample documents
            $cursor = $collection->find([], ['limit' => 5]);
            echo "<p>Sample documents:</p>\n";
            echo "<ul>\n";
            
            $hasDocuments = false;
            foreach ($cursor as $doc) {
                $hasDocuments = true;
                echo "<li>" . json_encode($doc, JSON_PRETTY_PRINT) . "</li>\n";
            }
            
            if (!$hasDocuments) {
                echo "<li>No documents found</li>\n";
            }
            
            echo "</ul>\n";
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Error accessing $dbName: " . $e->getMessage() . "</p>\n";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ MongoDB connection error: " . $e->getMessage() . "</p>\n";
}

echo "<h2>Environment Variables</h2>\n";
echo "<p>MONGODB_URI: " . (getenv('MONGODB_URI') ? 'Set' : 'Not set') . "</p>\n";
echo "<p>BUNNY_STORAGE_ZONE: " . (getenv('BUNNY_STORAGE_ZONE') ? 'Set' : 'Not set') . "</p>\n";
echo "<p>BUNNY_ACCESS_KEY: " . (getenv('BUNNY_ACCESS_KEY') ? 'Set' : 'Not set') . "</p>\n";
echo "<p>BUNNY_CDN_HOST: " . (getenv('BUNNY_CDN_HOST') ? 'Set' : 'Not set') . "</p>\n";
?>