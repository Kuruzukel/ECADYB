<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../Connection/Configuration/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ' . BASE_URL . 'Login');
    exit();
}

require __DIR__ . '/../Connection/Configuration/HeadersConfig.php';

require __DIR__ . '/Components/AdminDashboard.php';
