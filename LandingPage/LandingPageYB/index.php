<?php
// Router for LandingPageYB content
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Remove the base path
$path = str_replace('/LandingPage/LandingPageYB/', '', $path);
$path = str_replace('/LandingPageYB/', '', $path);

// If no specific file is requested, serve index.html
if (empty($path) || $path === 'index.php') {
    $filePath = __DIR__ . '/index.html';
} else {
    $filePath = __DIR__ . '/' . $path;
}

// Security check - only allow files within this directory
$realPath = realpath($filePath);
$allowedDir = realpath(__DIR__);

if ($realPath === false || strpos($realPath, $allowedDir) !== 0) {
    http_response_code(404);
    echo "File not found";
    exit;
}

// Check if file exists
if (!file_exists($filePath)) {
    http_response_code(404);
    echo "File not found: " . $path;
    exit;
}

// Get file extension to set correct content type
$extension = pathinfo($filePath, PATHINFO_EXTENSION);
$contentTypes = [
    'html' => 'text/html',
    'css' => 'text/css',
    'js' => 'application/javascript',
    'json' => 'application/json',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'svg' => 'image/svg+xml'
];

if (isset($contentTypes[$extension])) {
    header('Content-Type: ' . $contentTypes[$extension]);
}

// Serve the file
readfile($filePath);
?> 