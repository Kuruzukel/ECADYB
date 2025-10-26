<?php
session_start();
require __DIR__ . '/../Connection/Configuration/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Location: ' . BASE_URL . 'Public/Components/Login.php');
    exit();
}

header('Location: ' . BASE_URL . 'Student/Components/StudentDashboard.php');
exit();
