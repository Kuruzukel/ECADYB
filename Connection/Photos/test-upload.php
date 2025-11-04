<?php
/**
 * Simple test script to diagnose 502 errors
 */

ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Test 1: Basic response
error_log("TEST: test-upload.php accessed");

// Test 2: Check PHP configuration
$phpInfo = [
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_input_time' => ini_get('max_input_time'),
];

// Test 3: Check if POST data received
$postSize = strlen(file_get_contents('php://input'));

// Test 4: Check files
$filesReceived = isset($_FILES) && !empty($_FILES);
$fileCount = $filesReceived ? count($_FILES) : 0;

$response = [
    'success' => true,
    'message' => 'Test endpoint is working',
    'php_config' => $phpInfo,
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set',
    'content_length' => $_SERVER['CONTENT_LENGTH'] ?? 'not set',
    'post_size_bytes' => $postSize,
    'files_received' => $filesReceived,
    'file_count' => $fileCount,
    'timestamp' => date('Y-m-d H:i:s')
];

error_log("TEST: Sending response: " . json_encode($response));

echo json_encode($response);
exit;
?>

