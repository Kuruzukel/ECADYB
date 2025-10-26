<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    require_once __DIR__ . '/../../vendor/autoload.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to load dependencies']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['email']) || empty($input['email'])) {
        echo json_encode(['success' => false, 'message' => 'Email is required']);
        exit;
    }

    $email = trim($input['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }

    require_once __DIR__ . '/../Configuration/MongoConnect.php';

    $database = $GLOBALS['database'] ?? null;
    if (!$database) {
        echo json_encode(['success' => false, 'message' => 'Database connection not available']);
        exit;
    }

    $found = false;

    $queryOptions = [
        'maxTimeMS' => 2000
    ];

    try {
        $mongoClient = $GLOBALS['mongoClient'] ?? null;
        if (!$mongoClient) {
            require_once __DIR__ . '/../../vendor/autoload.php';
            require_once __DIR__ . '/../Configuration/EnvLoader.php';
            $mongoClient = new \MongoDB\Client(getMongoUrl());
        }
        $adminDB = $mongoClient->admin;
        $adminCollection = $adminDB->accounts;

        $adminAccount = $adminCollection->findOne(['email' => $email], $queryOptions);

        if ($adminAccount) {
            $found = true;
        }
    } catch (Exception $e) {
        error_log("Admin check error: " . $e->getMessage());
    }

    if (!$found) {
        $departmentCollections = ['bsn', 'bsme', 'bscje', 'bstm', 'bse', 'bsis', 'beced', 'bsma', 'bsmt', 'btvted'];

        foreach ($departmentCollections as $collectionName) {
            try {
                $collection = $database->selectCollection($collectionName);
                $student = $collection->findOne(['email' => $email], $queryOptions);

                if ($student) {
                    $found = true;
                    break;
                }
            } catch (Exception $e) {
                error_log("Collection $collectionName search error: " . $e->getMessage());
                continue;
            }
        }
    }

    if ($found) {
        echo json_encode(['success' => true, 'exists' => true]);
    } else {
        echo json_encode(['success' => true, 'exists' => false]);
    }
} catch (Exception $e) {
    error_log("CheckEmail error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred', 'error' => $e->getMessage()]);
} catch (Error $e) {
    error_log("CheckEmail fatal error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Fatal error occurred', 'error' => $e->getMessage()]);
}
