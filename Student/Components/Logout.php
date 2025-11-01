<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../Connection/Configuration/config.php';

if (isset($_SESSION['student_id'])) {
    try {
        require_once __DIR__ . '/../../vendor/autoload.php';
        require_once __DIR__ . '/../../Connection/Configuration/EnvLoader.php';
        require_once __DIR__ . '/../../Connection/Configuration/JWTConfig.php';

        $mongoUrl = getMongoUrl();
        $client = new MongoDB\Client($mongoUrl);

        removeActiveSession($client, $_SESSION['student_id']);
    } catch (Exception $e) {
        error_log("Logout session cleanup error: " . $e->getMessage());
    }
}

$_SESSION = array();

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

session_destroy();

header('Location: ' . BASE_URL . 'Login');
exit();
