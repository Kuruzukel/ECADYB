<?php
require __DIR__ . '/../../vendor/autoload.php'; // Adjust path if needed

use MongoDB\Client;

// ----------------------
// MongoDB Connection
// ----------------------
$mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX:56957/';
$client = new Client($mongoUrl);

// ----------------------
// Departments Database
// ----------------------
$departmentsDB = $client->Departments;
$adminCollection = $departmentsDB->Admin; // Admin collection in Departments DB

// Department collections
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
$announcementDB = $client->Announcement;
$calendarCollection = $announcementDB->Calendar;

// ----------------------
// Top Management Database
// ----------------------
$topManagementDB = $client->Top_Management;
$messageCollection = $topManagementDB->message;

// ----------------------
// Admin Database
// ----------------------
$adminDB = $client->admin;
$adminSampleCollection = $adminDB->AdminSample;