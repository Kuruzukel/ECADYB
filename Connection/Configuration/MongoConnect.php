<?php
require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

$dotenvPath = __DIR__ . '/../../';
if (file_exists($dotenvPath . '.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable($dotenvPath);
    $dotenv->load();
}

$mongoUrl = getenv('MONGO_URL') ?: $_ENV['MONGO_URL'] ?? getenv('MONGODB_URI') ?? $_ENV['MONGODB_URI'] ?? null;
if (!$mongoUrl) {
    if (getenv('RAILWAY_ENVIRONMENT') || getenv('RAILWAY_PUBLIC_URL')) {
        throw new Exception('MongoDB URL not configured. Please set MONGO_URL or MONGODB_URI in Railway dashboard under Variables tab.');
    } else {
        throw new Exception('MongoDB URL not configured. Please set MONGO_URL in .env file or as environment variable.');
    }
}
$client   = new Client($mongoUrl);

$GLOBALS['mongoClient'] = $client;

$ecadybDB = $client->ECADYB;
$departmentsDB = $client->ECADYB;

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

$adminDB              = $client->admin;
$adminCollection      = $adminDB->accounts;
$adminSampleCollection = $adminDB->AdminSample;
