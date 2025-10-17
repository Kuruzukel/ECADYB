<?php
/**
 * Headers Configuration for Railway Deployment
 * This file sets necessary headers to allow iframe embedding and CORS
 */

// Allow iframe embedding
header('X-Frame-Options: SAMEORIGIN');
header('Content-Security-Policy: frame-ancestors \'self\' *;');

// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Additional security headers
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
?>

