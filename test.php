<?php
require 'vendor/autoload.php';

try {
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $db = $client->selectDatabase("test");
    echo "MongoDB extension and library are working! Connected to database: " . $db->getDatabaseName();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}