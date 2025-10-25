<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

try {
    require_once __DIR__ . '/EnvLoader.php';
    $mongoUrl = getMongoUrl();

    $client = new Client($mongoUrl);

    $collection = $client->mydb->users;

    $cursor = $collection->find();

    foreach ($cursor as $document) {
        echo "Name: " . $document['name'] . "<br>";
        echo "Age: " . $document['age'] . "<hr>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
