<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

try {
    $department = isset($_GET['department']) ? strtoupper($_GET['department']) : 'BSME';
    $batchYear = isset($_GET['batch_year']) ? trim($_GET['batch_year']) : null;

    echo "Testing student data fetch...\n";
    echo "Department: $department\n";
    echo "Batch Year: $batchYear\n";

    require_once __DIR__ . '/../Configuration/EnvLoader.php';
    $mongoUrl = getMongoUrl();
    $client = new Client($mongoUrl);
    $db = $client->ECADYB;

    echo "Connected to ECADYB database\n";

    $collections = iterator_to_array($db->listCollectionNames());
    echo "Available collections: " . implode(', ', $collections) . "\n";

    $bsmeCollections = ['bsme', 'bsmt'];

    foreach ($bsmeCollections as $collectionName) {
        if (in_array($collectionName, $collections)) {
            $collection = $db->$collectionName;
            $totalCount = $collection->countDocuments([]);
            echo "Collection $collectionName: $totalCount total documents\n";

            $sample = $collection->findOne([]);
            if ($sample) {
                echo "Sample document from $collectionName:\n";
                echo json_encode($sample, JSON_PRETTY_PRINT) . "\n";

                if ($batchYear) {
                    $academicYear = str_replace('Batch Year ', '', $batchYear);
                    $filteredCount = $collection->countDocuments(['academic year' => $academicYear]);
                    echo "Documents with academic year '$academicYear': $filteredCount\n";

                    if ($filteredCount > 0) {
                        $sampleFiltered = $collection->findOne(['academic year' => $academicYear]);
                        echo "Sample filtered document:\n";
                        echo json_encode($sampleFiltered, JSON_PRETTY_PRINT) . "\n";
                    }
                }
            }
        } else {
            echo "Collection $collectionName: NOT FOUND\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
