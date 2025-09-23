<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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

if (isset($_SERVER['RAILWAY_STATIC_URL'])) {
    $baseUrl = rtrim($_SERVER['RAILWAY_STATIC_URL'], '/');
    $loginUrl = $baseUrl . '/Public/Components/Login.php';
} else {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
        (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on');
    $protocol = $isHttps ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $basePath = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'])));
    $loginUrl = $protocol . $host . $basePath . '/Public/Components/Login.php';
}

$loginUrl = str_replace('//', '/', $loginUrl);
$loginUrl = str_replace(':/', '://', $loginUrl);


header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Location: " . $loginUrl, true, 302);
exit();
