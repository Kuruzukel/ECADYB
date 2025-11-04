<?php

session_start();
require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/DateTimeHelper.php';

use MongoDB\Client;

$dotenvPath = __DIR__ . '/../../';
if (file_exists($dotenvPath . '.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable($dotenvPath);
    $dotenv->load();
}

$mongoUrl = getenv('MONGO_URL') ?: $_ENV['MONGO_URL'] ?? getenv('MONGODB_URI') ?? $_ENV['MONGODB_URI'] ?? null;
if (!$mongoUrl) {
    die("ERROR: MongoDB URL not configured.\n");
}

try {
    $client = new Client($mongoUrl);

    echo "=================================================\n";
    echo "DEBUG COMPLETION DATE\n";
    echo "=================================================\n\n";

    echo "SESSION INFO:\n";
    echo "  Student ID: " . ($_SESSION['student_id'] ?? 'NOT SET') . "\n";
    echo "  Academic Year: " . ($_SESSION['academic_year'] ?? 'NOT SET') . "\n";
    echo "  Department: " . ($_SESSION['department'] ?? 'NOT SET') . "\n\n";

    $studentAcademicYear = $_SESSION['academic_year'] ?? '';

    $batchYear = $studentAcademicYear;
    if (!empty($batchYear) && strpos($batchYear, 'Batch Year') === false) {
        $batchYear = 'Batch Year ' . $batchYear;
    }

    echo "DATABASE QUERY:\n";
    echo "  Looking for batch_year: '$batchYear'\n";
    echo "  Template: 1\n\n";

    $coversCollection = $client->ECADYB->Yearbook_Covers;

    $cursor = $coversCollection->find(
        [
            'batch_year' => $batchYear,
            'template' => 1
        ]
    );

    $found = false;
    foreach ($cursor as $doc) {
        $found = true;
        echo "FOUND DOCUMENT:\n";
        echo "  Slot: " . ($doc['slot'] ?? 'N/A') . "\n";
        echo "  Batch Year: " . ($doc['batch_year'] ?? 'N/A') . "\n";
        echo "  Template: " . ($doc['template'] ?? 'N/A') . "\n";

        if (isset($doc['completion_date'])) {
            $completionDateTime = $doc['completion_date']->toDateTime();
            $completionPhilippine = new DateTime();
            $completionPhilippine->setTimestamp($completionDateTime->getTimestamp());
            $completionPhilippine->setTimezone(new DateTimeZone('Asia/Manila'));

            $now = new DateTime('now', new DateTimeZone('Asia/Manila'));

            echo "  ✓ Completion Date (UTC): " . $completionDateTime->format('Y-m-d H:i:s') . "\n";
            echo "  ✓ Completion Date (Philippine Time): " . convertToPhilippineTimeCustom($doc['completion_date']) . "\n";
            echo "  Current Time (Philippine Time): " . $now->format('Y-m-d\Th:i:s A') . "\n";
            echo "  Is Completed? " . ($now >= $completionPhilippine ? 'YES ✓' : 'NO ✗') . "\n";
            echo "  Time Until/Since: ";

            $diff = $now->diff($completionPhilippine);
            if ($now < $completionPhilippine) {
                echo "Will complete in " . $diff->format('%h hours, %i minutes') . "\n";
            } else {
                echo "Completed " . $diff->format('%h hours, %i minutes') . " ago\n";
            }
        } else {
            echo "  ✗ No completion_date field\n";
        }
        echo "\n";
        break;
    }

    if (!$found) {
        echo "⚠ NO DOCUMENTS FOUND!\n\n";
        echo "Available batch years in database:\n";

        $allBatches = $coversCollection->distinct('batch_year');
        foreach ($allBatches as $batch) {
            echo "  - '$batch'\n";
        }
    }

    echo "\n=================================================\n";
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
