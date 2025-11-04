<?php
/**
 * Admin Profile Diagnostic Tool
 * This script helps diagnose admin profile image loading issues
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Connection/Configuration/MongoConnect.php';

echo "<h1>Admin Profile Diagnostic</h1>";
echo "<pre>";

// Check session
echo "=== SESSION INFO ===\n";
echo "Session ID: " . session_id() . "\n";
echo "Session Role: " . ($_SESSION['role'] ?? 'Not set') . "\n";
echo "Session Username: " . ($_SESSION['username'] ?? 'Not set') . "\n";
echo "\n";

// Check MongoDB connection
echo "=== MONGODB CONNECTION ===\n";
try {
    $ping = $mongoClient->admin->command(['ping' => 1]);
    echo "✓ MongoDB connection: OK\n";
} catch (Exception $e) {
    echo "✗ MongoDB connection: FAILED - " . $e->getMessage() . "\n";
}
echo "\n";

// Check admin collection
echo "=== ADMIN COLLECTION ===\n";
try {
    $count = $adminCollection->countDocuments([]);
    echo "Total admin accounts: " . $count . "\n";
    echo "\n";
    
    echo "All admin accounts:\n";
    $admins = $adminCollection->find([]);
    foreach ($admins as $admin) {
        echo "  - Username: " . ($admin['username'] ?? 'N/A') . "\n";
        echo "    Name: " . ($admin['name'] ?? 'N/A') . "\n";
        echo "    Email: " . ($admin['email'] ?? 'N/A') . "\n";
        echo "    Profile: " . ($admin['profile'] ?? 'N/A') . "\n";
        echo "    Has Profile Field: " . (isset($admin['profile']) ? 'YES' : 'NO') . "\n";
        echo "\n";
    }
} catch (Exception $e) {
    echo "✗ Failed to query admin collection: " . $e->getMessage() . "\n";
}
echo "\n";

// Try to fetch current admin data
if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
    echo "=== CURRENT ADMIN DATA ===\n";
    echo "Looking for username: " . $_SESSION['username'] . "\n";
    try {
        $adminData = $adminCollection->findOne(['username' => $_SESSION['username']]);
        if ($adminData) {
            echo "✓ Admin found!\n";
            echo "  Name: " . ($adminData['name'] ?? 'N/A') . "\n";
            echo "  Email: " . ($adminData['email'] ?? 'N/A') . "\n";
            echo "  Profile URL: " . ($adminData['profile'] ?? 'N/A') . "\n";
            echo "  Profile field exists: " . (isset($adminData['profile']) ? 'YES' : 'NO') . "\n";
            echo "  Profile is empty: " . (empty($adminData['profile']) ? 'YES' : 'NO') . "\n";
        } else {
            echo "✗ No admin found with username: " . $_SESSION['username'] . "\n";
        }
    } catch (Exception $e) {
        echo "✗ Failed to fetch admin data: " . $e->getMessage() . "\n";
    }
} else {
    echo "=== CURRENT ADMIN DATA ===\n";
    echo "⚠ No admin logged in (no session username)\n";
}

echo "\n";
echo "=== RECOMMENDATIONS ===\n";
echo "1. Make sure you're logged in with the correct username\n";
echo "2. Check that your admin account has a 'profile' field in the database\n";
echo "3. Verify the profile URL is accessible\n";
echo "4. Clear your browser cache if the old image is still showing\n";
echo "</pre>";

echo "<hr>";
echo "<p><a href='./'>← Back to Admin Dashboard</a></p>";
?>

