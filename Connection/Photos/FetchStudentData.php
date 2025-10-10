<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../Configuration/MongoConnect.php';

function respond($success, $message = '', $data = [])
{
    if (ob_get_level()) {
        ob_clean();
    }

    $response = array_merge([
        'success' => $success,
        'message' => $message
    ], $data);

    error_log("FetchStudentData Response: " . substr(json_encode($response), 0, 200) . "...");

    header('Content-Type: application/json');
    header('Content-Length: ' . strlen(json_encode($response)));
    echo json_encode($response);
    exit;
}

try {
    error_log("FetchStudentData Request: " . json_encode($_GET));

    $template = isset($_GET['template']) ? (int)$_GET['template'] : 1;
    $department = isset($_GET['department']) ? strtoupper($_GET['department']) : null;
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 50; // Max 100 students per request

    error_log("Parsed parameters - Template: $template, Department: $department, Page: $page, Limit: $limit");

    if ($template < 1 || $template > 3) {
        respond(false, 'Invalid template parameter. Must be 1, 2, or 3.');
    }

    if (!$department) {
        respond(false, 'Department parameter is required.');
    }

    $departmentCollections = [
        'BSBA' => ['bsma', 'bse'],
        'BSCJ' => ['bscje'],
        'BSE' => ['btvted', 'beced'],
        'BSIS' => ['bsis'],
        'BSME' => ['bsme', 'bsmt'],
        'MARITIME' => ['bsme', 'bsmt'],
        'BSN' => ['bsn'],
        'BSTM' => ['bstm']
    ];

    $department = strtoupper($department);
    if ($department === 'BSME' || $department === 'MARITIME') {
        $department = 'BSME';
    }

    if (!isset($departmentCollections[$department])) {
        respond(false, 'Invalid department code: ' . $department . '. Valid codes are: ' . implode(', ', array_keys($departmentCollections)));
    }

    $template = max(1, min(3, $template));
    $mongoDbName = "BatchTemplate" . $template;

    error_log("Connecting to MongoDB database: " . $mongoDbName . " for department: " . $department);
    $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';

    $mongoClient = new MongoDB\Client($mongoUrl, [
        'serverSelectionTimeoutMS' => 5000,
        'connectTimeoutMS' => 5000,
        'socketTimeoutMS' => 5000
    ]);

    try {
        $db = $mongoClient->selectDatabase($mongoDbName);
        $db->command(['ping' => 1]);
        error_log("MongoDB connection successful to database: " . $mongoDbName);

        $allCollections = iterator_to_array($db->listCollectionNames());
        error_log("Available collections in database $mongoDbName: " . implode(', ', $allCollections));
    } catch (Exception $e) {
        error_log("MongoDB connection error: " . $e->getMessage());
        respond(false, 'Database connection error: ' . $e->getMessage());
    }

    $collections = $departmentCollections[$department];
    error_log("Processing collections for department $department: " . implode(', ', $collections));

    // First, get total count across all collections
    $totalStudentsCount = 0;
    foreach ($collections as $collectionName) {
        try {
            $collectionNames = iterator_to_array($db->listCollectionNames());
            $collectionExists = in_array($collectionName, $collectionNames);

            if ($collectionExists) {
                $collection = $db->$collectionName;
                $studentCount = $collection->countDocuments();
                $totalStudentsCount += $studentCount;
                error_log("Found $studentCount students in collection $collectionName");
            }
        } catch (Exception $e) {
            error_log("Error counting collection $collectionName: " . $e->getMessage());
        }
    }

    error_log("Total students across all collections: $totalStudentsCount");

    // Calculate pagination
    $totalPages = ceil($totalStudentsCount / $limit);
    $skip = ($page - 1) * $limit;

    error_log("Pagination: Page $page of $totalPages, Skip: $skip, Limit: $limit");

    $allStudents = [];
    $studentsProcessed = 0;
    $targetSkip = $skip;
    $targetLimit = $limit;

    // Process collections in order, skipping students until we reach our target page
    foreach ($collections as $collectionName) {
        if ($studentsProcessed >= $targetSkip + $targetLimit) {
            break; // We have enough students
        }

        try {
            error_log("Processing collection: $collectionName");
            $collectionNames = iterator_to_array($db->listCollectionNames());
            $collectionExists = in_array($collectionName, $collectionNames);

            error_log("Collection $collectionName exists: " . ($collectionExists ? 'YES' : 'NO'));

            if ($collectionExists) {
                $collection = $db->$collectionName;
                $collectionCount = $collection->countDocuments();

                // Calculate how many students to skip and take from this collection
                $collectionSkip = max(0, $targetSkip - $studentsProcessed);
                $collectionLimit = min($targetLimit - count($allStudents), $collectionCount - $collectionSkip);

                if ($collectionLimit > 0) {
                    error_log("Collection $collectionName: Skip $collectionSkip, Limit $collectionLimit");

                    $students = $collection->find([], [
                        'sort' => ['department section' => 1, 'last name' => 1],
                        'skip' => $collectionSkip,
                        'limit' => $collectionLimit
                    ]);
                    $processedCount = 0;

                    foreach ($students as $student) {
                        $processedCount++;
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

                        $milestones = [];
                        if (isset($student['milestone'])) {
                            if (is_array($student['milestone'])) {
                                $milestones = $student['milestone'];
                            } elseif (!empty($student['milestone'])) {
                                $milestones = [$student['milestone']];
                            }
                        } elseif (isset($student['milestones'])) {
                            if (is_array($student['milestones'])) {
                                $milestones = $student['milestones'];
                            } elseif (!empty($student['milestones'])) {
                                $milestones = [$student['milestones']];
                            }
                        }

                        $honors = $student['honors'] ?? '';
                        if (!empty($honors) && !is_array($milestones)) {
                            $milestones[] = $honors;
                        } elseif (!empty($honors) && is_array($milestones)) {
                            array_unshift($milestones, $honors);
                        }

                        $allStudents[] = [
                            'id' => (string)$student['_id'],
                            'student_id' => $student['student id'] ?? $student['student_id'] ?? '',
                            'name' => $fullName,
                            'program' => $student['program'] ?? $student['department section'] ?? '',
                            'year' => $student['academic year'] ?? $student['year'] ?? '',
                            'section' => $student['section'] ?? '',
                            'motto' => $student['motto'] ?? 'No motto provided',
                            'milestones' => $milestones,
                            'photo_url' => $student['photo_url'] ?? '',
                            'collection' => $collectionName
                        ];
                    }

                    error_log("Processed $processedCount students from collection $collectionName");
                    $studentsProcessed += $collectionCount; // Add total count for pagination calculation
                } else {
                    $studentsProcessed += $collectionCount; // Skip this collection, add to processed count
                }
            } else {
                error_log("Collection $collectionName does not exist in database $mongoDbName");
            }
        } catch (Exception $e) {
            error_log("Error fetching from collection $collectionName: " . $e->getMessage());
        }
    }

    error_log("Returning " . count($allStudents) . " students for page $page");

    $studentsPerPage = 6;
    $currentPageStudents = count($allStudents);
    $totalStudents = $totalStudentsCount; // Use the total count across all collections

    // Calculate actual yearbook pages needed (not API pagination pages)
    $yearbookPagesNeeded = ceil($totalStudents / $studentsPerPage);

    $response = [
        'success' => true,
        'message' => 'Student data retrieved successfully',
        'data' => [
            'students' => $allStudents,
            'total_students' => $totalStudents,
            'current_page' => $page,
            'total_pages' => $totalPages, // API pagination pages
            'yearbook_pages' => $yearbookPagesNeeded, // Actual yearbook pages needed
            'students_per_page' => $studentsPerPage,
            'limit_per_request' => $limit,
            'students_returned' => $currentPageStudents,
            'has_next_page' => $page < $totalPages,
            'has_previous_page' => $page > 1,
            'department' => $department,
            'template' => $template,
            'collections_searched' => $collections,
            'database' => $mongoDbName
        ]
    ];

    error_log("Sending paginated response: Page $page of $totalPages, " . count($allStudents) . " students returned");
    respond(true, 'Student data retrieved successfully', $response);
} catch (Exception $e) {
    error_log("FetchStudentData.php exception: " . $e->getMessage());
    respond(false, 'Server error: ' . $e->getMessage());
}
