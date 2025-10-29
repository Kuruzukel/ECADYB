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
    echo "GET STUDENT ACADEMIC YEAR FROM COLLECTION\n";
    echo "=================================================\n\n";

    $studentId = '2024-000000';
    $studentName = 'Kel D. Cruz';
    $department = 'BS Nursing';

    echo "Looking up student:\n";
    echo "  Student ID: $studentId\n";
    echo "  Name: $studentName\n";
    echo "  Department: $department\n\n";

    $departmentMapping = [
        'BS Nursing' => 'bsn',
        'BS Marine Engineering' => 'bsme',
        'BS Marine Transportation' => 'bsmt',
        'BS Criminal Justice Education' => 'bscje',
        'BS Tourism Management' => 'bstm',
        'BS Technical-Vocational Teacher Education' => 'btvted',
        'BS Early Childhood Education' => 'beced',
        'BS Information System' => 'bsis',
        'BS Management Accounting' => 'bsma',
        'BS Entrepreneurship' => 'bse'
    ];

    $collectionName = $departmentMapping[$department] ?? null;

    if (!$collectionName) {
        die("ERROR: Unknown department '$department'\n");
    }

    echo "Searching in collection: $collectionName\n\n";

    $collection = $db->$collectionName;

    $nameParts = explode(' ', $studentName);
    $firstName = $nameParts[0] ?? '';
    $lastName = end($nameParts);

    echo "Parsed name:\n";
    echo "  First name: $firstName\n";
    echo "  Last name: $lastName\n\n";

    $student = $collection->findOne([
        '$or' => [
            ['student id' => $studentId],
            ['student_id' => $studentId]
        ]
    ]);

    if (!$student) {
        echo "⚠ Student not found by ID, trying by name...\n\n";

        $student = $collection->findOne([
            '$and' => [
                [
                    '$or' => [
                        ['first name' => ['$regex' => $firstName, '$options' => 'i']],
                        ['first_name' => ['$regex' => $firstName, '$options' => 'i']]
                    ]
                ],
                [
                    '$or' => [
                        ['last name' => ['$regex' => $lastName, '$options' => 'i']],
                        ['last_name' => ['$regex' => $lastName, '$options' => 'i']]
                    ]
                ]
            ]
        ]);
    }

    if ($student) {
        echo "✓ STUDENT FOUND!\n";
        echo "=================================================\n\n";

        echo "Student Details:\n";
        echo "  _id: " . $student['_id'] . "\n";
        echo "  student id: " . ($student['student id'] ?? $student['student_id'] ?? 'NOT SET') . "\n";
        echo "  first name: " . ($student['first name'] ?? $student['first_name'] ?? 'NOT SET') . "\n";
        echo "  middle name: " . ($student['middle name'] ?? $student['middle_name'] ?? 'NOT SET') . "\n";
        echo "  last name: " . ($student['last name'] ?? $student['last_name'] ?? 'NOT SET') . "\n";
        echo "  email: " . ($student['email'] ?? 'NOT SET') . "\n";
        echo "  department: " . ($student['department'] ?? 'NOT SET') . "\n";
        echo "  program: " . ($student['program'] ?? 'NOT SET') . "\n";
        echo "  section: " . ($student['section'] ?? 'NOT SET') . "\n";
        echo "  department section: " . ($student['department section'] ?? 'NOT SET') . "\n";
        echo "\n";

        $academicYear = $student['academic year'] ?? $student['academic_year'] ?? null;

        echo "🎓 ACADEMIC YEAR:\n";
        if ($academicYear) {
            echo "  ✓ " . $academicYear . "\n";
            echo "\n";
            echo "This value should be added to the active session!\n";
        } else {
            echo "  ❌ NOT SET IN DATABASE\n";
            echo "\n";
            echo "⚠ WARNING: This student record doesn't have an academic year!\n";
            echo "You need to update the student record first.\n";
        }

        echo "\n=================================================\n";

        // Show all fields for debugging
        echo "\nAll fields in student record:\n";
        foreach ($student as $key => $value) {
            if ($key !== '_id' && !is_object($value)) {
                echo "  - $key: " . (is_string($value) || is_numeric($value) ? $value : gettype($value)) . "\n";
            }
        }
    } else {
        echo "✗ STUDENT NOT FOUND!\n";
        echo "=================================================\n\n";
        echo "The student does not exist in the '$collectionName' collection.\n";
        echo "This could mean:\n";
        echo "  1. The student ID is incorrect\n";
        echo "  2. The student is in a different department\n";
        echo "  3. The student record hasn't been created yet\n\n";

        // Try searching in all collections
        echo "Searching in all collections...\n\n";

        foreach ($departmentMapping as $deptName => $collName) {
            $coll = $db->$collName;
            $count = $coll->countDocuments([
                '$or' => [
                    ['student id' => $studentId],
                    ['student_id' => $studentId]
                ]
            ]);

            if ($count > 0) {
                echo "✓ Found in: $deptName ($collName) - Count: $count\n";

                $foundStudent = $coll->findOne([
                    '$or' => [
                        ['student id' => $studentId],
                        ['student_id' => $studentId]
                    ]
                ]);

                if ($foundStudent) {
                    $foundAcademicYear = $foundStudent['academic year'] ?? $foundStudent['academic_year'] ?? 'NOT SET';
                    echo "  Academic Year: $foundAcademicYear\n";
                }
            }
        }
    }

    echo "\n=================================================\n";
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
