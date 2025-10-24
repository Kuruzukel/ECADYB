<?php
session_start();

header('Content-Type: application/json');

// Check if user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access. Please log in as admin.'
    ]);
    exit();
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit();
}

// Get POST data
$currentPassword = $_POST['currentPassword'] ?? '';
$newPassword = $_POST['newPassword'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

// Validate inputs
if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    echo json_encode([
        'success' => false,
        'message' => 'All fields are required.'
    ]);
    exit();
}

// Check if new password and confirm password match
if ($newPassword !== $confirmPassword) {
    echo json_encode([
        'success' => false,
        'message' => 'New password and confirm password do not match.'
    ]);
    exit();
}

// Check password length (must be exactly 8 characters)
if (strlen($newPassword) !== 8) {
    echo json_encode([
        'success' => false,
        'message' => 'Password must be exactly 8 characters.'
    ]);
    exit();
}

// Check if new password is the same as current password
if ($currentPassword === $newPassword) {
    echo json_encode([
        'success' => false,
        'message' => 'New password must be different from current password.'
    ]);
    exit();
}

try {
    // Connect to MongoDB
    require __DIR__ . '/../Configuration/MongoConnect.php';
    
    // Get current admin username from session
    $username = $_SESSION['username'];
    
    // Verify current password
    $admin = $adminCollection->findOne([
        'username' => $username,
        'password' => $currentPassword
    ]);
    
    if (!$admin) {
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
        
        echo json_encode([
            'success' => true,
            'message' => 'Password changed successfully. Redirecting to login...'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update password. Please try again.'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}

