<?php
while (ob_get_level()) {
    ob_end_clean();
}

ob_start();

ini_set('display_errors', 0);
error_reporting(E_ALL);

ini_set('memory_limit', '512M');
ini_set('max_execution_time', '120');
set_time_limit(120);

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
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load required files: ' . $e->getMessage()
    ]);
    exit;
}

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        while (ob_get_level()) {
            ob_end_clean();
        }

        error_log("FetchStudentData Fatal Error: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line']);

        $response = [
            'success' => false,
            'message' => 'Server error occurred while fetching student data',
            'error_details' => $error['message']
        ];

        $jsonOutput = json_encode($response);
        header('Content-Type: application/json');
        header('Content-Length: ' . strlen($jsonOutput));
        echo $jsonOutput;
        exit;
    }
});

function respond($success, $message = '', $data = [])
{
    while (ob_get_level()) {
        ob_end_clean();
    }

    $response = array_merge([
        'success' => $success,
        'message' => $message
    ], $data);

    $jsonOutput = json_encode($response, JSON_PARTIAL_OUTPUT_ON_ERROR);
    if ($jsonOutput === false) {
        $errorResponse = [
            'success' => false,
            'message' => 'Failed to encode response: ' . json_last_error_msg(),
            'data' => []
        ];
        $jsonOutput = json_encode($errorResponse);
    }

    error_log("FetchStudentData Response: " . substr($jsonOutput, 0, 200) . "... (total length: " . strlen($jsonOutput) . " bytes)");

    header('Content-Type: application/json; charset=utf-8');
    header('Content-Length: ' . strlen($jsonOutput));
    header('Connection: close');
    echo $jsonOutput;
    flush();
    exit;
}

try {
    error_log("FetchStudentData Request: " . json_encode($_GET));

    $template = isset($_GET['template']) ? (int)$_GET['template'] : 1;
    $department = isset($_GET['department']) ? strtoupper($_GET['department']) : null;
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 50;
    $batchYear = isset($_GET['batch_year']) ? trim($_GET['batch_year']) : null;

    error_log("Parsed parameters - Template: $template, Department: $department, Page: $page, Limit: $limit, BatchYear: $batchYear");

    $debugMode = isset($_GET['debug']) && $_GET['debug'] === '1';

    if ($debugMode) {
        error_log("=== DEBUG MODE ENABLED ===");
        error_log("Request parameters: " . json_encode($_GET));
    }

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

    error_log("Department mapping for $department: " . json_encode($departmentCollections[$department] ?? 'NOT FOUND'));

    $department = strtoupper($department);
    if ($department === 'BSME' || $department === 'MARITIME') {
        $department = 'BSME';
    }

    if (!isset($departmentCollections[$department])) {
        respond(false, 'Invalid department code: ' . $department . '. Valid codes are: ' . implode(', ', array_keys($departmentCollections)));
    }

    $template = max(1, min(3, $template));
    $mongoDbName = "ECADYB";

    error_log("=== FETCH STUDENT DATA REQUEST ===");
    error_log("Department: $department, Template: $template, Page: $page, Limit: $limit, Batch Year: $batchYear");
    error_log("Connecting to MongoDB database: " . $mongoDbName . " for department: " . $department);

    $mongoUrl = getenv('MONGO_URL') ?: getenv('MONGODB_URI') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';

    $mongoClient = new MongoDB\Client($mongoUrl, [
        'serverSelectionTimeoutMS' => 15000,
        'connectTimeoutMS' => 15000,
        'socketTimeoutMS' => 60000,
        'maxIdleTimeMS' => 60000
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
    error_log("Available collections in ECADYB database: " . implode(', ', iterator_to_array($db->listCollectionNames())));

    $academicYearFilter = [];
    if ($batchYear) {
        $academicYear = str_replace('Batch Year ', '', $batchYear);
        $academicYearFilter = ['academic year' => $academicYear];
        error_log("Filtering students by academic year: $academicYear");
        error_log("Academic year filter: " . json_encode($academicYearFilter));
        error_log("Looking for field 'academic year' with value: $academicYear");
    } else {
        error_log("No batch year provided - fetching all students");
    }

    $totalStudentsCount = 0;
    foreach ($collections as $collectionName) {
        try {
            $collectionNames = iterator_to_array($db->listCollectionNames());
            $collectionExists = in_array($collectionName, $collectionNames);

            if ($collectionExists) {
                $collection = $db->$collectionName;

                $sampleDoc = $collection->findOne([]);
                if ($sampleDoc) {
                    error_log("Sample document from $collectionName: " . json_encode($sampleDoc));
                }

                $studentCount = $collection->countDocuments($academicYearFilter);
                $totalStudentsCount += $studentCount;
                error_log("Found $studentCount students in collection $collectionName" . ($academicYearFilter ? " with filter: " . json_encode($academicYearFilter) : ""));
            }
        } catch (Exception $e) {
            error_log("Error counting collection $collectionName: " . $e->getMessage());
        }
    }

    error_log("Total students across all collections: $totalStudentsCount");

    if ($totalStudentsCount === 0 && !empty($academicYearFilter)) {
        error_log("No students found with academic year filter - keeping filter active to prevent showing students from other batch years");
    }

    $totalPages = ceil($totalStudentsCount / $limit);
    $skip = ($page - 1) * $limit;

    error_log("Pagination: Page $page of $totalPages, Skip: $skip, Limit: $limit");

    $allStudents = [];
    $studentsProcessed = 0;
    $targetSkip = $skip;
    $targetLimit = $limit;

    error_log("Pagination calculation - Skip: $skip, Limit: $limit, Target Skip: $targetSkip, Target Limit: $targetLimit");

    foreach ($collections as $collectionName) {
        if (count($allStudents) >= $targetLimit) {
            error_log("Already collected enough students, breaking loop");
            break;
        }

        try {
            error_log("Processing collection: $collectionName");
            $collectionNames = iterator_to_array($db->listCollectionNames());
            $collectionExists = in_array($collectionName, $collectionNames);

            error_log("Collection $collectionName exists: " . ($collectionExists ? 'YES' : 'NO'));

            if ($collectionExists) {
                $collection = $db->$collectionName;
                $collectionCount = $collection->countDocuments($academicYearFilter);
                error_log("Collection $collectionName: Found $collectionCount students with filter: " . json_encode($academicYearFilter));

                $studentsNeeded = $targetLimit - count($allStudents);
                error_log("Students needed: $studentsNeeded");

                if ($studentsNeeded > 0 && $collectionCount > 0) {
                    $collectionSkip = max(0, $targetSkip - $studentsProcessed);
                    $collectionLimit = min($studentsNeeded, $collectionCount - $collectionSkip);

                    error_log("Collection $collectionName: Skip $collectionSkip, Limit $collectionLimit (students needed: $studentsNeeded, collection count: $collectionCount)");

                    if ($collectionLimit > 0) {
                        try {
                            error_log("Executing MongoDB query for collection $collectionName with skip=$collectionSkip, limit=$collectionLimit");
                            $queryStartTime = microtime(true);

                            $students = $collection->find($academicYearFilter, [
                                'sort' => ['department section' => 1, 'last name' => 1],
                                'skip' => $collectionSkip,
                                'limit' => $collectionLimit,
                                'maxTimeMS' => 45000,
                                'noCursorTimeout' => true
                            ]);
                            $processedCount = 0;

                            $queryEndTime = microtime(true);
                            $queryDuration = round(($queryEndTime - $queryStartTime) * 1000, 2);
                            error_log("MongoDB query executed in {$queryDuration}ms for collection $collectionName");

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

                                $sanitize = function ($value) {
                                    if (is_string($value)) {
                                        $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                                        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
                                        return trim($value);
                                    }
                                    return $value;
                                };

                                $allStudents[] = [
                                    'id' => (string)$student['_id'],
                                    'student_id' => $sanitize($student['student id'] ?? $student['student_id'] ?? ''),
                                    'name' => $sanitize($fullName),
                                    'program' => $sanitize($student['program'] ?? $student['department section'] ?? ''),
                                    'year' => $sanitize($student['academic year'] ?? $student['year'] ?? ''),
                                    'section' => $sanitize($student['section'] ?? ''),
                                    'motto' => $sanitize($student['motto'] ?? 'No motto provided'),
                                    'milestones' => array_map($sanitize, $milestones),
                                    'honors' => $sanitize($student['honors'] ?? ''),
                                    'photo_url' => $sanitize($student['photo_url'] ?? ''),
                                    'status' => $sanitize($student['status'] ?? 'pending'),
                                    'collection' => $collectionName
                                ];

                                if (count($allStudents) >= $targetLimit) {
                                    error_log("Reached target limit of $targetLimit students, breaking collection loop");
                                    break;
                                }
                            }

                            error_log("Processed $processedCount students from collection $collectionName, total students now: " . count($allStudents));
                            $studentsProcessed += $processedCount;
                        } catch (MongoDB\Driver\Exception\ExecutionTimeoutException $e) {
                            error_log("MongoDB query timeout for collection $collectionName: " . $e->getMessage());
                            $studentsProcessed += $collectionCount;
                        } catch (Exception $e) {
                            error_log("Error during MongoDB find for collection $collectionName: " . $e->getMessage());
                            $studentsProcessed += $collectionCount;
                        }
                    } else {
                        error_log("No students to fetch from collection $collectionName (collectionLimit <= 0)");
                        $studentsProcessed += $collectionCount;
                    }
                } else {
                    error_log("No students needed or collection is empty for $collectionName");
                    $studentsProcessed += $collectionCount;
                }
            } else {
                error_log("Collection $collectionName does not exist in database $mongoDbName");
            }
        } catch (Exception $e) {
            error_log("Error fetching from collection $collectionName: " . $e->getMessage());
        }
    }

    error_log("Finished processing all collections. Total students collected: " . count($allStudents));

    error_log("Returning " . count($allStudents) . " students for page $page");
    error_log("Page $page details - Skip: $skip, Limit: $limit, Total students in DB: $totalStudentsCount");

    $studentsPerPage = 4;
    $currentPageStudents = count($allStudents);
    $totalStudents = $totalStudentsCount;

    $yearbookPagesNeeded = ceil($totalStudents / $studentsPerPage);

    error_log("Calculated pagination - Total students: $totalStudents, Students per page: $studentsPerPage, Yearbook pages needed: $yearbookPagesNeeded");

    if ($totalStudents === 0) {
        $message = $batchYear ?
            "No students found for department $department and academic year " . str_replace('Batch Year ', '', $batchYear) . ". Please check if student data has been uploaded for this batch year." :
            "No students found for department $department. Please check if student data has been uploaded.";

        error_log("No students found - sending empty response with message: $message");

        respond(true, $message, [
            'data' => [
                'students' => [],
                'total_students' => 0,
                'current_page' => $page,
                'total_pages' => 0,
                'yearbook_pages' => 0,
                'students_per_page' => $studentsPerPage,
                'limit_per_request' => $limit,
                'students_returned' => 0,
                'has_next_page' => false,
                'has_previous_page' => $page > 1,
                'department' => $department,
                'template' => $template,
                'collections_searched' => $collections,
                'database' => $mongoDbName,
                'academic_year_filter' => $academicYearFilter
            ]
        ]);
    }

    $responseData = [
        'data' => [
            'students' => $allStudents,
            'total_students' => $totalStudents,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'yearbook_pages' => $yearbookPagesNeeded,
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

    try {
        json_encode($responseData);
    } catch (Exception $jsonError) {
        error_log("JSON encoding error: " . $jsonError->getMessage());
        respond(false, 'Failed to encode response data');
    }

    respond(true, 'Student data retrieved successfully', $responseData);
} catch (MongoDB\Driver\Exception\Exception $mongoError) {
    error_log("MongoDB exception in FetchStudentData.php: " . $mongoError->getMessage());
    error_log("MongoDB exception trace: " . $mongoError->getTraceAsString());
    respond(false, 'Database error: Connection timeout or query failed. Please try again.');
} catch (Exception $e) {
    error_log("FetchStudentData.php exception: " . $e->getMessage());
    error_log("Exception trace: " . $e->getTraceAsString());
    respond(false, 'Server error: ' . $e->getMessage());
}

if (!headers_sent()) {
    respond(false, 'Unknown error occurred');
}