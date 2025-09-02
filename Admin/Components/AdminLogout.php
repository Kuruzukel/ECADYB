<?php
// Ensure no output before headers
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
$_SESSION = array();

// Delete the session cookie
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

// Destroy the session
session_destroy();

// Clear any existing output buffer
if (ob_get_length()) {
    ob_clean();
}

// Determine base URL based on environment
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
           (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
           (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on');
$protocol = $isHttps ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];

// Get the base path from the current request
$basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));

// Construct the login URL - adjust the path as needed based on your directory structure
$loginPath = '/Public/Components/Login.php';
$loginUrl = $protocol . $host . $basePath . $loginPath;

// Ensure URL is properly formatted
$loginUrl = str_replace('//', '/', $loginUrl);
$loginUrl = str_replace(':/', '://', $loginUrl);

// Redirect to login page
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Location: " . $loginUrl);
exit();
?>
