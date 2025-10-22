<?php
session_start();
require __DIR__ . '/../config.php';

// Check if user is logged in as student
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    // Redirect to login page if not logged in or not a student
    header('Location: ' . BASE_URL . 'Public/Components/Login.php');
    exit();
}

// Redirect to Student Dashboard
header('Location: ' . BASE_URL . 'Student/Components/StudentDashboard.php');
exit();
?>

