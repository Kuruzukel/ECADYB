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
    die("ERROR: MongoDB URL not configured.\n");
}

try {
    $client = new Client($mongoUrl);
    $db = $client->ECADYB;

    echo "=================================================\n";
    echo "CHECKING STUDENT RECORD\n";
    echo "=================================================\n\n";

    $studentId = '2024-000000';

    // Check in BSN collection (BS Nursing)
    $collection = $db->bsn;

    echo "Looking for student ID: $studentId in BSN collection...\n\n";

    $student = $collection->findOne([
        '$or' => [
            ['student id' => $studentId],
            ['student_id' => $studentId]
        ]
    ]);

    if ($student) {
        echo "✓ STUDENT FOUND!\n\n";
        echo "Fields in student record:\n";
        echo "  student id: " . ($student['student id'] ?? $student['student_id'] ?? 'NOT SET') . "\n";
        echo "  first name: " . ($student['first name'] ?? 'NOT SET') . "\n";
        echo "  last name: " . ($student['last name'] ?? 'NOT SET') . "\n";
        echo "  email: " . ($student['email'] ?? 'NOT SET') . "\n";
        echo "  department: " . ($student['department'] ?? 'NOT SET') . "\n";
        echo "  program: " . ($student['program'] ?? 'NOT SET') . "\n";
        echo "  section: " . ($student['section'] ?? 'NOT SET') . "\n";
        echo "  department section: " . ($student['department section'] ?? 'NOT SET') . "\n";
        echo "  academic year: " . ($student['academic year'] ?? '❌ NOT SET') . "\n";

        if (!isset($student['academic year']) || empty($student['academic year'])) {
            echo "\n⚠ WARNING: 'academic year' field is MISSING or EMPTY!\n";
            echo "This is why the completion date feature isn't working.\n\n";
            echo "SOLUTION: Add 'academic year' to this student's record.\n";
            echo "Example: '2024-2025'\n";
        }
    } else {
        echo "✗ STUDENT NOT FOUND in BSN collection\n";
        echo "Checking other collections...\n\n";

        $collections = ['bsme', 'bsmt', 'bscje', 'bstm', 'btvted', 'beced', 'bsis', 'bsma', 'bse'];

        foreach ($collections as $collName) {
            $coll = $db->$collName;
            $student = $coll->findOne([
                '$or' => [
                    ['student id' => $studentId],
                    ['student_id' => $studentId]
                ]
            ]);

            if ($student) {
                echo "✓ Found in collection: $collName\n";
                echo "  academic year: " . ($student['academic year'] ?? '❌ NOT SET') . "\n";
                break;
            }
        }
    }

    echo "\n=================================================\n";
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
