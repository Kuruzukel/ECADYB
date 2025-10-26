<?php
header('Content-Type: application/json');

require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

try {
    require_once __DIR__ . '/../Configuration/EnvLoader.php';
    $mongoUrl = getMongoUrl();

    $client = new Client($mongoUrl, [
        'connectTimeoutMS' => 5000,
        'socketTimeoutMS' => 30000,
        'serverSelectionTimeoutMS' => 5000,
        'readPreference' => 'primaryPreferred'
    ]);

    $dbName = "ECADYB";
    $db = $client->$dbName;

    $collections = [
        "bsme",
        "bsmt",
        "bscje",
        "bstm",
        "btvted",
        "beced",
        "bsn",
        "bsis",
        "bsma",
        "bse"
    ];

    $academicYears = [];

    foreach ($collections as $collectionKey) {
        $collection = $db->$collectionKey;
        $distinctYears = $collection->distinct('academic year');
        foreach ($distinctYears as $year) {
            if (!empty($year) && !in_array($year, $academicYears)) {
                $academicYears[] = $year;
            }
        }
    }

    sort($academicYears);

    echo json_encode([
        'success' => true,
        'academicYears' => $academicYears
    ]);
} catch (Exception $e) {
    error_log("Error fetching academic years: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch academic years',
        'error' => $e->getMessage()
    ]);
}
