<?php

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

// Department to collection mapping
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
    'BS Entrepreneurship' => 'bse',
    // Add variations
    'Nursing' => 'bsn',
    'Marine Engineering' => 'bsme',
    'Marine Transportation' => 'bsmt',
    'Criminal Justice Education' => 'bscje',
    'Criminology' => 'bscje',
    'Tourism Management' => 'bstm',
    'Tourism' => 'bstm',
    'Technical-Vocational Teacher Education' => 'btvted',
    'Early Childhood Education' => 'beced',
    'Information System' => 'bsis',
    'Management Accounting' => 'bsma',
    'Entrepreneurship' => 'bse'
];

try {
    $client = new Client($mongoUrl);
    $db = $client->ECADYB;
    $sessionsCollection = $db->active_sessions;

    echo "=================================================\n";
    echo "UPDATE ALL ACTIVE SESSIONS WITH ACADEMIC YEAR\n";
    echo "=================================================\n\n";

    // Get all active sessions
    $allSessions = $sessionsCollection->find();
    $sessionCount = 0;
    $updatedCount = 0;
    $skippedCount = 0;
    $errorCount = 0;

    $sessions = iterator_to_array($allSessions);
    $totalSessions = count($sessions);

    echo "Found $totalSessions active session(s)\n\n";

    if ($totalSessions === 0) {
        echo "No active sessions to update.\n";
        exit(0);
    }

    echo "=================================================\n";
    echo "PROCESSING SESSIONS...\n";
    echo "=================================================\n\n";

    foreach ($sessions as $session) {
        $sessionCount++;
        $sessionId = (string)$session['_id'];
        $studentId = $session['student_id'] ?? 'UNKNOWN';
        $studentName = $session['name'] ?? 'UNKNOWN';
        $department = $session['department'] ?? null;
        $currentAcademicYear = $session['academic_year'] ?? null;

        echo "[$sessionCount/$totalSessions] Processing: $studentName ($studentId)\n";
        echo "  Department: " . ($department ?? 'NOT SET') . "\n";
        echo "  Current academic_year: " . ($currentAcademicYear ? "✓ $currentAcademicYear" : "❌ NOT SET") . "\n";

        // Skip admin sessions
        if (isset($session['role']) && $session['role'] === 'admin') {
            echo "  ⊝ Skipped (Admin session)\n\n";
            $skippedCount++;
            continue;
        }

        // Skip if already has academic year
        if (!empty($currentAcademicYear)) {
            echo "  ⊝ Skipped (Already has academic_year)\n\n";
            $skippedCount++;
            continue;
        }

        // Get collection name from department
        if (!$department || !isset($departmentMapping[$department])) {
            echo "  ✗ Error: Cannot map department to collection\n";
            echo "    Available mappings: " . implode(', ', array_keys($departmentMapping)) . "\n\n";
            $errorCount++;
            continue;
        }

        $collectionName = $departmentMapping[$department];
        echo "  → Collection: $collectionName\n";

        try {
            // Look up student in their department collection
            $studentCollection = $db->$collectionName;
            $studentRecord = $studentCollection->findOne([
                '$or' => [
                    ['student id' => $studentId],
                    ['student_id' => $studentId]
                ]
            ]);

            if (!$studentRecord) {
                echo "  ✗ Error: Student not found in $collectionName collection\n\n";
                $errorCount++;
                continue;
            }

            // Get academic year from student record
            $academicYear = $studentRecord['academic year'] ?? $studentRecord['academic_year'] ?? null;

            if (!$academicYear) {
                echo "  ⚠ Warning: Student record doesn't have academic_year field\n\n";
                $errorCount++;
                continue;
            }

            echo "  → Found academic_year: $academicYear\n";

            // Update the active session
            $updateResult = $sessionsCollection->updateOne(
                ['_id' => $session['_id']],
                ['$set' => ['academic_year' => $academicYear]]
            );

            if ($updateResult->getModifiedCount() > 0) {
                echo "  ✓ Updated successfully!\n\n";
                $updatedCount++;
            } else {
                echo "  ⚠ Update failed (no changes made)\n\n";
                $errorCount++;
            }
        } catch (Exception $e) {
            echo "  ✗ Error: " . $e->getMessage() . "\n\n";
            $errorCount++;
        }
    }

    echo "=================================================\n";
    echo "SUMMARY\n";
    echo "=================================================\n\n";
    echo "Total sessions processed: $sessionCount\n";
    echo "✓ Successfully updated: $updatedCount\n";
    echo "⊝ Skipped: $skippedCount\n";
    echo "✗ Errors: $errorCount\n";
    echo "\n=================================================\n";

    if ($updatedCount > 0) {
        echo "\n⚠ IMPORTANT:\n";
        echo "Students who are currently viewing the yearbook need to\n";
        echo "REFRESH their browser for changes to take effect.\n";
        echo "=================================================\n";
    }
} catch (Exception $e) {
    echo "\n✗ FATAL ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
