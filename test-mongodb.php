<?php
// Test MongoDB connection
require __DIR__ . '/vendor/autoload.php';

use MongoDB\Client;

echo "Testing MongoDB Connection...\n";

try {
    // Get MongoDB connection string from environment variable
    $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://localhost:27017';
    echo "Using connection string: " . (strpos($mongoUrl, 'mongodb://localhost') === 0 ? 'localhost' : 'Railway MongoDB') . "\n";
    
    // Connect to MongoDB
    $client = new Client($mongoUrl);
    
    // Test the connection
    $client->listDatabases();
    echo "✓ MongoDB connection successful!\n";
    
    // Test database operations
    $db = $client->test;
    $collection = $db->test_collection;
    
    // Insert a test document
    $result = $collection->insertOne(['test' => 'connection', 'timestamp' => new MongoDB\BSON\UTCDateTime()]);
    echo "✓ Test document inserted with ID: " . $result->getInsertedId() . "\n";
    
    // Find the test document
    $doc = $collection->findOne(['_id' => $result->getInsertedId()]);
    echo "✓ Test document retrieved successfully\n";
    
    // Clean up
    $collection->deleteOne(['_id' => $result->getInsertedId()]);
    echo "✓ Test document cleaned up\n";
    
    echo "\n🎉 All tests passed! MongoDB is working correctly.\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting tips:\n";
    echo "1. Make sure MongoDB is running locally or MONGO_URL is set\n";
    echo "2. Check if the MongoDB PHP extension is installed\n";
    echo "3. Verify the connection string format\n";
}
?> 