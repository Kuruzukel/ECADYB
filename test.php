<?php
require 'vendor/autoload.php';

$client = new MongoDB\Client("mongodb://localhost:27017");
$dbs = $client->listDatabases();

foreach ($dbs as $db) {
    echo $db->getName(), "<br>";
}
?>