<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    require_once __DIR__ . '/../../vendor/autoload.php';
    require_once __DIR__ . '/../Configuration/EnvLoader.php';
} catch (Exception $e) {
    error_log("Failed to load dependencies: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server configuration error']);
    exit;
}

use MongoDB\Client;

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['email'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email is required']);
        exit;
    }

    $email = trim($input['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }

    $client = new Client(getMongoUrl());
    $userExists = false;

    try {
        $adminDB = $client->selectDatabase('admin');
        $adminCollection = $adminDB->selectCollection('accounts');
        $user = $adminCollection->findOne(['email' => $email]);

        if ($user) {
            $userExists = true;
        }
    } catch (Exception $e) {
        error_log("Admin check error: " . $e->getMessage());
    }

    // Check student databases if not found in admin
    if (!$userExists) {
        $database = $client->selectDatabase('ECADYB');
        $departmentCollections = ['bsn', 'bsme', 'bscje', 'bstm', 'bse', 'bsis', 'beced', 'bsma', 'bsmt', 'btvted'];

        foreach ($departmentCollections as $collectionName) {
            $collection = $database->selectCollection($collectionName);
            $user = $collection->findOne(['email' => $email]);

            if ($user) {
                $userExists = true;
                break;
            }
        }
    }

    echo json_encode([
        'success' => true,
        'exists' => $userExists
    ]);
} catch (Exception $e) {
    error_log("CheckEmail error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}
