<?php

/**
 * View All Active Sessions
 */

require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

$dotenvPath = __DIR__ . '/../../';
if (file_exists($dotenvPath . '.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable($dotenvPath);
    $dotenv->load();
}

$mongoUrl = getenv('MONGO_URL') ?: $_ENV['MONGO_URL'] ?? getenv('MONGODB_URI') ?? $_ENV['MONGODB_URI'] ?? null;

try {
    $client = new Client($mongoUrl);
    $sessionsCollection = $client->ECADYB->active_sessions;

    echo "=================================================\n";
    echo "ALL ACTIVE SESSIONS\n";
    echo "=================================================\n\n";

    $sessions = $sessionsCollection->find(
        ['student_id' => '2024-000000'],
        ['sort' => ['created_at' => -1]]
    );

    $count = 0;
    foreach ($sessions as $session) {
        $count++;
        echo "Session #$count:\n";
        echo "  _id: " . $session['_id'] . "\n";
        echo "  student_id: " . ($session['student_id'] ?? 'NOT SET') . "\n";
        echo "  name: " . ($session['name'] ?? 'NOT SET') . "\n";
        echo "  department: " . ($session['department'] ?? 'NOT SET') . "\n";
        echo "  academic_year: " . ($session['academic_year'] ?? '❌ NOT SET') . "\n";
        echo "  role: " . ($session['role'] ?? 'NOT SET') . "\n";
        echo "  created_at: " . ($session['created_at'] ?? 'NOT SET') . "\n";
        echo "\n";

        echo "  ALL FIELDS:\n";
        foreach ($session as $key => $value) {
            if ($key !== '_id') {
                echo "    - $key\n";
            }
        }
        echo "\n---\n\n";
    }

    if ($count === 0) {
        echo "No active sessions found for student 2024-000000\n";
    }

    echo "=================================================\n";
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
}
