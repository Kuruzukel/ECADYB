<?php
require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

$mongoUrl = getenv('MONGO_URL') ?: getenv('MONGODB_URI') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
$client   = new Client($mongoUrl);

$GLOBALS['mongoClient'] = $client;

// All data now goes to ECADYB database
$ecadybDB = $client->ECADYB;

$GLOBALS['database'] = $ecadybDB;

// Department collections (program codes as collection names)
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

// Announcement collection
$calendarCollection = $ecadybDB->Announcement;

// Top Management collections
$messageCollection = $ecadybDB->Top_Management_Messages;
$topManagementPhotosCollection = $ecadybDB->Top_Management_Photos;

// Student collection
$studentPhotosCollection = $ecadybDB->Student_Photos;

// Yearbook collection
$yearbookCoversCollection = $ecadybDB->Yearbook_Covers;

// Admin collection (legacy support)
$adminCollection = $ecadybDB->Admin;

$adminDB              = $client->admin;
$adminSampleCollection = $adminDB->AdminSample;
