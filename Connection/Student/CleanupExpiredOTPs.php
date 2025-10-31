<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../Configuration/EnvLoader.php';

use MongoDB\Client;

try {
    $client = new Client(getMongoUrl());
    $otpDB = $client->selectDatabase('ECADYB');
    $otpCollection = $otpDB->selectCollection('otp_codes');

    $currentTime = time();

    $result = $otpCollection->deleteMany([
        'expires' => ['$lt' => $currentTime]
    ]);

    $deletedCount = $result->getDeletedCount();
    echo json_encode([
        'success' => true,
        'message' => "Cleanup completed. Deleted $deletedCount expired OTP(s).",
        'deleted_count' => $deletedCount
    ]);

    error_log("OTP Cleanup: Deleted $deletedCount expired OTP codes");
} catch (Exception $e) {
    error_log("OTP Cleanup Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Cleanup failed: ' . $e->getMessage()
    ]);
}
