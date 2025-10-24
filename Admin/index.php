<?php
session_start();
require __DIR__ . '/../Connection/Configuration/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'Public/Components/Login.php');
    exit();
}

require __DIR__ . '/../Connection/Configuration/HeadersConfig.php';

require __DIR__ . '/Components/AdminDashboard.php';