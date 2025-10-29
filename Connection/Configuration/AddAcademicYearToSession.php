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
    echo "ADD ACADEMIC YEAR TO ACTIVE SESSION\n";
    echo "=================================================\n\n";

    $studentId = '2024-000000';
    $academicYear = '2024-2025';

    echo "Student ID: $studentId\n";
    echo "Academic Year to add: $academicYear\n\n";

    $sessionsCollection = $db->active_sessions;

    $existingSession = $sessionsCollection->findOne(['student_id' => $studentId]);

    if (!$existingSession) {
        echo "⚠ No active session found for student $studentId\n";
        echo "This could mean the student is not currently logged in.\n";
        exit(0);
    }

    echo "✓ Found active session:\n";
    echo "  Session ID: " . $existingSession['_id'] . "\n";
    echo "  Name: " . ($existingSession['name'] ?? 'NOT SET') . "\n";
    echo "  Department: " . ($existingSession['department'] ?? 'NOT SET') . "\n";
    echo "  Current academic_year: " . ($existingSession['academic_year'] ?? '❌ NOT SET') . "\n\n";

    // Update the session with academic year
    $result = $sessionsCollection->updateMany(
        ['student_id' => $studentId],
        ['$set' => ['academic_year' => $academicYear]]
    );

    echo "Update Result:\n";
    echo "  Matched: " . $result->getMatchedCount() . " session(s)\n";
    echo "  Modified: " . $result->getModifiedCount() . " session(s)\n\n";

    if ($result->getModifiedCount() > 0) {
        echo "✓ SUCCESS! Academic year has been added to the active session.\n\n";

        // Verify the update
        $updatedSession = $sessionsCollection->findOne(['student_id' => $studentId]);

        echo "Updated Session Data:\n";
        echo "  _id: " . $updatedSession['_id'] . "\n";
        echo "  student_id: " . $updatedSession['student_id'] . "\n";
        echo "  name: " . ($updatedSession['name'] ?? 'NOT SET') . "\n";
        echo "  department: " . ($updatedSession['department'] ?? 'NOT SET') . "\n";
        echo "  academic_year: ✓ " . $updatedSession['academic_year'] . "\n";
        echo "  role: " . ($updatedSession['role'] ?? 'NOT SET') . "\n";
        echo "  created_at: " . $updatedSession['created_at'] . "\n";
        echo "  last_activity: " . $updatedSession['last_activity'] . "\n";

        echo "\n=================================================\n";
        echo "⚠ IMPORTANT NEXT STEP:\n";
        echo "If the student is currently viewing the yearbook,\n";
        echo "they need to REFRESH the page for changes to take effect.\n";
        echo "=================================================\n";
    } else {
        echo "⚠ No changes made. The session might already have this academic year.\n";

        // Show current value
        $currentSession = $sessionsCollection->findOne(['student_id' => $studentId]);
        if (isset($currentSession['academic_year'])) {
            echo "Current academic_year value: " . $currentSession['academic_year'] . "\n";
        }
    }

    echo "\n=================================================\n";
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
