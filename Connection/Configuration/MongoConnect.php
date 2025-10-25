<?php
require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

// Load .env file
$dotenvPath = __DIR__ . '/../../';
if (file_exists($dotenvPath . '.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable($dotenvPath);
    $dotenv->load();
}

$mongoUrl = getenv('MONGO_URL') ?: $_ENV['MONGO_URL'] ?? getenv('MONGODB_URI') ?? null;
if (!$mongoUrl) {
    die('MongoDB URL not configured. Please set MONGO_URL in .env file');
}
$client   = new Client($mongoUrl);

$GLOBALS['mongoClient'] = $client;

$ecadybDB = $client->ECADYB;
$departmentsDB = $client->ECADYB; // Alias for student departments

$GLOBALS['database'] = $ecadybDB;

$collections = [
    "bsme"   => "BS Marine Engineering",
    "bsmt"   => "BS Marine Transportation",
    "bscje"  => "BS Criminal Justice Education",
    "bstm"   => "BS Tourism Management",
    "btvted" => "BS Technical-Vocational Teacher Education",
    "beced"  => "BS Early Childhood Education",
    "bsn"    => "BS Nursing",
    "bsis"   => "BS Information System",
    "bsma"   => "BS Management Accounting",
    "bse"    => "BS Entrepreneurship"
];

$calendarCollection = $ecadybDB->Announcement;

$messageCollection = $ecadybDB->Top_Management_Messages;
$topManagementPhotosCollection = $ecadybDB->Top_Management_Photos;

$studentPhotosCollection = $ecadybDB->Student_Photos;

$yearbookCoversCollection = $ecadybDB->Yearbook_Covers;

// Admin database and collection
$adminDB              = $client->admin;
$adminCollection      = $adminDB->accounts;
$adminSampleCollection = $adminDB->AdminSample;
