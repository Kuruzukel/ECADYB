<?php
session_start();
require __DIR__ . '/../Connection/Configuration/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Redirect to login page if not logged in or not an admin
    header('Location: ' . BASE_URL . 'Public/Components/Login.php');
    exit();
}

// Include headers configuration for Railway
require __DIR__ . '/../Connection/Configuration/HeadersConfig.php';

// Include the AdminDashboard component
require __DIR__ . '/Components/AdminDashboard.php';