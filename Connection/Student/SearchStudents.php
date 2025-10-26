<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    require __DIR__ . '/../../vendor/autoload.php';
    require_once __DIR__ . '/../Configuration/MongoConnect.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load required files: ' . $e->getMessage()
    ]);
    exit;
}

use MongoDB\BSON\Regex;

try {
    $query = isset($_GET['query']) ? trim($_GET['query']) : '';
    $limit = isset($_GET['limit']) ? max(1, min(20, (int)$_GET['limit'])) : 10;

    if (empty($query)) {
        echo json_encode([
            'success' => true,
            'results' => []
        ]);
        exit;
    }

    $collectionNames = ['bsme', 'bsmt', 'bscje', 'bstm', 'btvted', 'beced', 'bsn', 'bsis', 'bsma', 'bse'];

    $allResults = [];
    $mongoDbName = "ECADYB";
    $db = $GLOBALS['mongoClient']->selectDatabase($mongoDbName);

    foreach ($collectionNames as $collectionName) {
        if (count($allResults) >= $limit) {
            break;
        }

        try {
            $collection = $db->$collectionName;

            $regexPattern = new Regex($query, 'i');

            $searchFilter = [
                '$or' => [
                    ['student id' => $regexPattern],
                    ['student_id' => $regexPattern],
                    ['name' => $regexPattern],
                    ['first name' => $regexPattern],
                    ['last name' => $regexPattern]
                ]
            ];

            $students = $collection->find($searchFilter, [
                'limit' => $limit - count($allResults),
                'sort' => ['last name' => 1]
            ]);

            foreach ($students as $student) {
                $fullName = '';
                if (isset($student['name']) && !empty($student['name'])) {
                    $fullName = $student['name'];
                } else {
                    $firstName = $student['first name'] ?? '';
                    $middleName = $student['middle name'] ?? '';
                    $lastName = $student['last name'] ?? '';

                    if (!empty($firstName) || !empty($lastName)) {
                        $parts = [];
                        if (!empty($firstName)) {
                            $parts[] = $firstName;
                        }
                        if (!empty($middleName)) {
                            if (strlen($middleName) <= 2) {
                                $parts[] = rtrim($middleName, '.') . '.';
                            } else {
                                $parts[] = substr($middleName, 0, 1) . '.';
                            }
                        }
                        if (!empty($lastName)) {
                            $parts[] = $lastName;
                        }
                        $fullName = implode(' ', $parts);
                    } else {
                        $fullName = 'Unknown Student';
                    }
                }

                $allResults[] = [
                    'id' => (string)$student['_id'],
                    'student_id' => $student['student id'] ?? $student['student_id'] ?? '',
                    'name' => $fullName,
                    'department_section' => $student['department section'] ?? $student['program'] ?? '',
                    'academic_year' => $student['academic year'] ?? $student['year'] ?? '',
                    'program' => $student['program'] ?? $student['department section'] ?? '',
                    'year' => $student['academic year'] ?? $student['year'] ?? '',
                    'status' => $student['status'] ?? 'pending',
                    'collection' => $collectionName
                ];

                if (count($allResults) >= $limit) {
                    break;
                }
            }
        } catch (Exception $e) {
            error_log("Error searching in collection $collectionName: " . $e->getMessage());
            continue;
        }
    }

    echo json_encode([
        'success' => true,
        'results' => $allResults,
        'total' => count($allResults)
    ]);
} catch (Exception $e) {
    error_log("SearchStudents.php exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Search error: ' . $e->getMessage()
    ]);
} catch (Error $e) {
    error_log("SearchStudents.php fatal error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Fatal error occurred'
    ]);
}
