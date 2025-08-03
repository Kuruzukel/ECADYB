<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../vendor/autoload.php';

use MongoDB\Client;

try {
    // Connect to MongoDB
    $client = new Client("mongodb://localhost:27017");

    // Correct database and collection
    $collection = $client->mydb->users;

    // Query all documents
    $cursor = $collection->find();

    foreach ($cursor as $document) {
        echo "Name: " . $document['name'] . "<br>";
        echo "Age: " . $document['age'] . "<hr>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}