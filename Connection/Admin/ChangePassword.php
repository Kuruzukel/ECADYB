<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

ob_start();

session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access. Please log in as admin.'
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit();
}

$currentPassword = $_POST['currentPassword'] ?? '';
$newPassword = $_POST['newPassword'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'All fields are required.'
    ]);
    exit();
}

if ($newPassword !== $confirmPassword) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'New password and confirm password do not match.'
    ]);
    exit();
}

if (strlen($newPassword) !== 8) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Password must be exactly 8 characters.'
    ]);
    exit();
}

// Check if new password is the same as current password
if ($currentPassword === $newPassword) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'New password must be different from current password.'
    ]);
    exit();
}

try {
    // Connect to MongoDB
    require_once __DIR__ . '/../../vendor/autoload.php';
    require_once __DIR__ . '/../Configuration/MongoConnect.php';

    // Get current admin username from session
    $username = $_SESSION['username'];

    // Verify current password
    $admin = $adminCollection->findOne([
        'username' => $username
    ]);

    if (!$admin || !isset($admin['password']) || $admin['password'] !== $currentPassword) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Current password is incorrect.'
        ]);
        exit();
    }

    // Update password
    $updateResult = $adminCollection->updateOne(
        ['username' => $username],
        ['$set' => ['password' => $newPassword]]
    );

    if ($updateResult->getModifiedCount() > 0) {
        // Password changed successfully
        // Destroy session
        session_unset();
        session_destroy();

        // Clear any buffered output before sending JSON
        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Password changed successfully. Redirecting to login...'
        ]);
    } else {
        // Clear any buffered output before sending JSON
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update password. Please try again.'
        ]);
    }
} catch (Exception $e) {
    error_log("ChangePassword error: " . $e->getMessage());
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
} catch (Error $e) {
    error_log("ChangePassword fatal error: " . $e->getMessage());
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Fatal error occurred'
    ]);
}

// End output buffering and flush
ob_end_flush();
