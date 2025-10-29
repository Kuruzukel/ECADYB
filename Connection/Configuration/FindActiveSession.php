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

try {
    $client = new Client($mongoUrl);
    $db = $client->ECADYB;

    echo "=================================================\n";
    echo "FIND ACTIVE SESSION - ALL COLLECTION NAMES\n";
    echo "=================================================\n\n";

    $studentId = '2024-000000';

    echo "Searching for student ID: $studentId\n\n";

    // List all collections in the database
    echo "Available collections in ECADYB database:\n";
    $collections = $db->listCollections();

    $sessionCollections = [];
    foreach ($collections as $collection) {
        $collName = $collection->getName();
        echo "  - $collName\n";

        // Check if it's a session-related collection
        if (stripos($collName, 'session') !== false || stripos($collName, 'active') !== false) {
            $sessionCollections[] = $collName;
        }
    }

    echo "\nSession-related collections found:\n";
    foreach ($sessionCollections as $collName) {
        echo "  ✓ $collName\n";
    }

    echo "\n=================================================\n";
    echo "SEARCHING FOR STUDENT SESSION...\n";
    echo "=================================================\n\n";

    // Try different possible collection names
    $possibleNames = [
        'Active_Sessions',
        'active_sessions',
        'ActiveSessions',
        'activeSessions',
        'sessions',
        'Sessions'
    ];

    $foundCollection = null;
    $foundSession = null;

    foreach ($possibleNames as $collName) {
        try {
            $collection = $db->$collName;
            $session = $collection->findOne(['student_id' => $studentId]);

            if ($session) {
                echo "✓ FOUND in collection: '$collName'\n\n";
                $foundCollection = $collName;
                $foundSession = $session;
                break;
            } else {
                echo "  Checked '$collName' - not found\n";
            }
        } catch (Exception $e) {
            echo "  Checked '$collName' - collection doesn't exist\n";
        }
    }

    if ($foundSession) {
        echo "=================================================\n";
        echo "SESSION DETAILS:\n";
        echo "=================================================\n\n";
        echo "Collection: $foundCollection\n";
        echo "_id: " . $foundSession['_id'] . "\n";
        echo "student_id: " . ($foundSession['student_id'] ?? 'NOT SET') . "\n";
        echo "name: " . ($foundSession['name'] ?? 'NOT SET') . "\n";
        echo "department: " . ($foundSession['department'] ?? 'NOT SET') . "\n";
        echo "academic_year: " . ($foundSession['academic_year'] ?? '❌ NOT SET') . "\n";
        echo "role: " . ($foundSession['role'] ?? 'NOT SET') . "\n";
        echo "created_at: " . ($foundSession['created_at'] ?? 'NOT SET') . "\n";
        echo "last_activity: " . ($foundSession['last_activity'] ?? 'NOT SET') . "\n";

        echo "\n=================================================\n";
        echo "All fields:\n";
        foreach ($foundSession as $key => $value) {
            if (!is_object($value)) {
                echo "  $key: " . (is_string($value) || is_numeric($value) ? $value : gettype($value)) . "\n";
            } else {
                echo "  $key: " . get_class($value) . "\n";
            }
        }
    } else {
        echo "\n=================================================\n";
        echo "✗ SESSION NOT FOUND\n";
        echo "=================================================\n\n";
        echo "No active session found for student ID: $studentId\n";
        echo "The student may need to log in again to create a new session.\n";
    }

    echo "\n=================================================\n";
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
