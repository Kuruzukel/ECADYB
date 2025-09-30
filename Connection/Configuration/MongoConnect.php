<?php
require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

$mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
$client   = new Client($mongoUrl);

$departmentsDB     = $client->Departments;
$adminCollection   = $departmentsDB->Admin;

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

$announcementDB       = $client->Announcement;
$calendarCollection   = $announcementDB->Calendar;

$topManagementDB    = $client->Top_Management;
$messageCollection  = $topManagementDB->message;

$adminDB              = $client->admin;
$adminSampleCollection = $adminDB->AdminSample;
