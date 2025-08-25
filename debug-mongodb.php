<?php
require __DIR__ . '/vendor/autoload.php';
use MongoDB\Client;

// MongoDB connection
$mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';

try {
    $client = new Client($mongoUrl);
    $db = $client->Departments;
    
    echo "<h2>MongoDB Collections:</h2>";
    $collections = $db->listCollections();
    foreach ($collections as $collection) {
        echo "- " . $collection->getName() . "<br>";
    }
    
    echo "<h2>Sample Documents from bsme collection:</h2>";
    $bsmeCollection = $db->bsme;
    $documents = $bsmeCollection->find()->limit(5);
    
    foreach ($documents as $doc) {
        echo "<h3>Document ID: " . $doc['_id'] . "</h3>";
        echo "<pre>";
        print_r($doc);
        echo "</pre>";
        echo "<hr>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
