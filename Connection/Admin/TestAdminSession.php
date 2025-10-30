<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../Configuration/EnvLoader.php';
require_once __DIR__ . '/../Configuration/JWTConfig.php';

use MongoDB\Client;

echo "<h2>Admin Session Test</h2>";

echo "<h3>Current Session Data:</h3>";
echo "<pre>";
echo "Username: " . ($_SESSION['username'] ?? 'NOT SET') . "\n";
echo "Role: " . ($_SESSION['role'] ?? 'NOT SET') . "\n";
echo "JWT Token: " . (isset($_SESSION['jwt_token']) ? 'SET' : 'NOT SET') . "\n";
echo "</pre>";

if (!isset($_SESSION['username']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<p style='color: red;'>❌ You must be logged in as admin to run this test!</p>";
    exit;
}

try {
    $client = new Client(getMongoUrl());
    $db = $client->ECADYB;
    $sessionsCollection = $db->active_sessions;

    $adminId = 'admin_' . $_SESSION['username'];

    echo "<h3>Checking Database:</h3>";

    // Check if session exists
    $existingSession = $sessionsCollection->findOne(['student_id' => $adminId]);

    if ($existingSession) {
        echo "<p style='color: green;'>✓ Admin session EXISTS in database!</p>";
        echo "<pre>";
        print_r($existingSession);
        echo "</pre>";
    } else {
        echo "<p style='color: orange;'>⚠ Admin session NOT FOUND in database. Creating now...</p>";

        // Get admin data
        $adminDB = $client->selectDatabase('admin');
        $adminCollection = $adminDB->selectCollection('accounts');
        $admin = $adminCollection->findOne(['username' => $_SESSION['username']]);

        // Create session data
        $sessionData = [
            'student_id' => $adminId,
            'username' => $_SESSION['username'],
            'name' => $admin['name'] ?? $_SESSION['username'],
            'role' => 'admin'
        ];

        // Store session
        $result = storeActiveSession($client, $sessionData);

        if ($result) {
            echo "<p style='color: green;'>✓ Admin session created successfully!</p>";

            // Verify it was created
            $newSession = $sessionsCollection->findOne(['student_id' => $adminId]);
            if ($newSession) {
                echo "<pre>";
                print_r($newSession);
                echo "</pre>";
            }
        } else {
            echo "<p style='color: red;'>❌ Failed to create admin session!</p>";
        }
    }

    // Show all active sessions
    echo "<h3>All Active Sessions:</h3>";
    $allSessions = $sessionsCollection->find([]);
    $count = 0;
    foreach ($allSessions as $session) {
        $count++;
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
        echo "<strong>Session #$count:</strong><br>";
        echo "ID: " . ($session['student_id'] ?? 'N/A') . "<br>";
        echo "Name: " . ($session['name'] ?? 'N/A') . "<br>";
        echo "Role: " . ($session['role'] ?? 'N/A') . "<br>";
        echo "Department: " . ($session['department'] ?? 'N/A') . "<br>";
        echo "Last Activity: " . (isset($session['last_activity']) ? date('Y-m-d H:i:s', $session['last_activity']->toDateTime()->getTimestamp()) : 'N/A') . "<br>";
        echo "</div>";
    }

    if ($count === 0) {
        echo "<p>No active sessions found.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    error_log("TestAdminSession error: " . $e->getMessage());
}
