<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Connection/Configuration/EnvLoader.php';
require_once __DIR__ . '/../../Connection/Configuration/JWTConfig.php';

use MongoDB\Client;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Remove active session from database before destroying session
if (isset($_SESSION['username']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    try {
        $client = new Client(getMongoUrl());
        $adminId = 'admin_' . $_SESSION['username'];
        
        removeActiveSession($client, $adminId);
        error_log("✓ Admin session removed on logout: " . $adminId);
    } catch (Exception $e) {
        error_log("✗ Failed to remove admin session on logout: " . $e->getMessage());
    }
}

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

if (ob_get_length()) {
    ob_clean();
}

require_once __DIR__ . '/../../Connection/Configuration/config.php';

$loginUrl = BASE_URL . 'Login';


header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Location: " . $loginUrl, true, 302);
exit();
