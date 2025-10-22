<?php
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'PHP is working!',
    'php_version' => phpversion(),
    'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
]);

