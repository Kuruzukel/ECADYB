<?php

/**
 * Fix Current Session - Add academic_year manually
 */

require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

// Load environment variables
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
    echo "FIX ACTIVE SESSION - ADD ACADEMIC YEAR\n";
    echo "=================================================\n\n";

    $studentId = '2024-000000';

    // Get student's academic year from database
    $db = $client->ECADYB;
    $collection = $db->bsn;

    $student = $collection->findOne([
        '$or' => [
            ['student id' => $studentId],
            ['student_id' => $studentId]
        ]
    ]);

    if (!$student) {
        die("Student not found!\n");
    }

    $academicYear = $student['academic year'] ?? '';

    echo "Student: {$student['first name']} {$student['last name']}\n";
    echo "Student ID: $studentId\n";
    echo "Academic Year in DB: $academicYear\n\n";

    if (empty($academicYear)) {
        die("ERROR: Student record doesn't have academic year!\n");
    }

    // Update active session
    $sessionsCollection = $client->ECADYB->Active_Sessions;

    $result = $sessionsCollection->updateMany(
        ['student_id' => $studentId],
        ['$set' => ['academic_year' => $academicYear]]
    );

    echo "Active sessions updated: " . $result->getModifiedCount() . "\n\n";

    if ($result->getModifiedCount() > 0) {
        echo "✓ SUCCESS: Added academic_year to active session(s)\n";
        echo "  Academic Year: $academicYear\n\n";
        echo "⚠ IMPORTANT: Refresh the Yearbook page in your browser!\n";
        echo "   The session data will be reloaded from MongoDB.\n";
    } else {
        echo "⚠ No sessions were modified.\n";
    }

    echo "\n=================================================\n";
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
