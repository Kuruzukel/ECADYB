<?php
require __DIR__ . '/../vendor/autoload.php'; // Composer autoload

use MongoDB\Client;

// ----------------------
// MongoDB Connection
// ----------------------
$mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
$client   = new Client($mongoUrl);

// ----------------------
// Departments Database
// ----------------------
$departmentsDB     = $client->Departments;
$adminCollection   = $departmentsDB->Admin;

// Mapping of collection => display name
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

// ----------------------
// Announcement Database
// ----------------------
$announcementDB       = $client->Announcement;
$calendarCollection   = $announcementDB->Calendar;

// ----------------------
// Top Management Database
// ----------------------
$topManagementDB    = $client->Top_Management;
$messageCollection  = $topManagementDB->message;

// ----------------------
// Admin Database (system)
// ----------------------
$adminDB              = $client->admin;
// Example collection (replace/remove as needed)
$adminSampleCollection = $adminDB->AdminSample;

// ----------------------
// Debug/Test (optional)
// ----------------------
try {
    $dbs = $client->listDatabases();
    foreach ($dbs as $db) {
        echo "<strong>Database:</strong> " . $db->getName() . "<br>";
        $dbObj = $client->selectDatabase($db->getName());
        foreach ($dbObj->listCollections() as $col) {
            echo "— Collection: " . $col->getName() . "<br>";
        }
        echo "<br>";
    }
} catch (Exception $e) {
    echo "MongoDB Connection failed: " . $e->getMessage();
}