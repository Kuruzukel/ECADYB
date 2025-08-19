<?php
require 'vendor/autoload.php'; // Composer autoloader

$manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");
var_dump($manager);