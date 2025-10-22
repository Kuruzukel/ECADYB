<?php
session_start();

// Check if user is logged in as student
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    // Redirect to login page if not logged in or not a student
    header('Location: /ECADYB/Public/Components/Login.php');
    exit();
}

// Redirect to Student Dashboard
header('Location: /ECADYB/Student/Components/StudentDashboard.php');
exit();
?>

