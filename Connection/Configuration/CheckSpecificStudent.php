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
    echo "CHECKING SPECIFIC STUDENT\n";
    echo "=================================================\n\n";

    // Check Ysabelle Villanueva
    $studentId = '2022-667900';
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
        echo "Student Details:\n";
        echo "  _id: " . $student['_id'] . "\n";
        echo "  student id: " . ($student['student id'] ?? $student['student_id'] ?? 'NOT SET') . "\n";
        echo "  first name: " . ($student['first name'] ?? 'NOT SET') . "\n";
        echo "  last name: " . ($student['last name'] ?? 'NOT SET') . "\n";
        echo "  email: " . ($student['email'] ?? 'NOT SET') . "\n";
        echo "  password: " . ($student['password'] ?? 'NOT SET') . "\n";
        echo "  academic year: " . ($student['academic year'] ?? 'NOT SET') . "\n";
        echo "  status: " . ($student['status'] ?? 'NOT SET') . "\n\n";

        // Check if password is hashed
        $password = $student['password'] ?? '';
        if (strpos($password, '$2y$') === 0) {
            echo "❌ PASSWORD IS STILL HASHED!\n";
            echo "   This password needs to be reset.\n\n";

            // Generate new plain password
            $newPassword = bin2hex(random_bytes(4)); // Simple 8-char password

            echo "Would you like to reset this password? (This is just a check, no changes made)\n";
            echo "New password would be: $newPassword\n";
        } else {
            echo "✅ PASSWORD IS PLAIN TEXT\n";
            echo "   Current password: $password\n";
        }
    } else {
        echo "❌ Student not found!\n";
    }

    echo "\n=================================================\n";
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
