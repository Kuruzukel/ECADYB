<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

ob_start();

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

    $jsonOutput = json_encode($response);
    
    header('Content-Type: application/json');
    header('Content-Length: ' . strlen($jsonOutput));
    echo $jsonOutput;
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

    // Add debug mode
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
    
    // Debug: Log the department mapping
    error_log("Department mapping for $department: " . json_encode($departmentCollections[$department] ?? 'NOT FOUND'));

    $department = strtoupper($department);
    if ($department === 'BSME' || $department === 'MARITIME') {
        $department = 'BSME';
    }

    if (!isset($departmentCollections[$department])) {
        respond(false, 'Invalid department code: ' . $department . '. Valid codes are: ' . implode(', ', array_keys($departmentCollections)));
    }

    $template = max(1, min(3, $template));
    // Use ECADYB database instead of BatchTemplate databases
    $mongoDbName = "ECADYB";

    error_log("Connecting to MongoDB database: " . $mongoDbName . " for department: " . $department);
    $mongoUrl = getenv('MONGO_URL') ?: getenv('MONGODB_URI') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';

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
    error_log("Available collections in ECADYB database: " . implode(', ', iterator_to_array($db->listCollectionNames())));

    // Build filter for academic year if batch year is provided
    $academicYearFilter = [];
    if ($batchYear) {
        // Convert "Batch Year 2024-2025" to "2024-2025"
        $academicYear = str_replace('Batch Year ', '', $batchYear);
        // Try both field name variations
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
                
                // Debug: Get a sample document to see the structure
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

    // If no students found with academic year filter, try without filter as fallback
    if ($totalStudentsCount === 0 && !empty($academicYearFilter)) {
        error_log("No students found with academic year filter, trying without filter as fallback");
        $academicYearFilter = []; // Reset filter
        $totalStudentsCount = 0;
        
        foreach ($collections as $collectionName) {
            try {
                $collectionNames = iterator_to_array($db->listCollectionNames());
                $collectionExists = in_array($collectionName, $collectionNames);

                if ($collectionExists) {
                    $collection = $db->$collectionName;
                    $studentCount = $collection->countDocuments($academicYearFilter);
                    $totalStudentsCount += $studentCount;
                    error_log("Fallback - Found $studentCount students in collection $collectionName without filter");
                }
            } catch (Exception $e) {
                error_log("Error counting collection $collectionName in fallback: " . $e->getMessage());
            }
        }
        error_log("Fallback - Total students across all collections: $totalStudentsCount");
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
        // Check if we've already collected enough students
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

                // Calculate how many students we still need
                $studentsNeeded = $targetLimit - count($allStudents);
                error_log("Students needed: $studentsNeeded");

                if ($studentsNeeded > 0 && $collectionCount > 0) {
                    // Calculate skip and limit for this collection
                    $collectionSkip = max(0, $targetSkip - $studentsProcessed);
                    $collectionLimit = min($studentsNeeded, $collectionCount - $collectionSkip);
                    
                    error_log("Collection $collectionName: Skip $collectionSkip, Limit $collectionLimit (students needed: $studentsNeeded, collection count: $collectionCount)");

                    if ($collectionLimit > 0) {
                        $students = $collection->find($academicYearFilter, [
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
                            
                            // Break if we've collected enough students
                            if (count($allStudents) >= $targetLimit) {
                                error_log("Reached target limit of $targetLimit students, breaking collection loop");
                                break;
                            }
                        }

                        error_log("Processed $processedCount students from collection $collectionName, total students now: " . count($allStudents));
                        $studentsProcessed += $processedCount;
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

    $studentsPerPage = 6;
    $currentPageStudents = count($allStudents);
    $totalStudents = $totalStudentsCount;

    $yearbookPagesNeeded = ceil($totalStudents / $studentsPerPage);
    
    error_log("Calculated pagination - Total students: $totalStudents, Students per page: $studentsPerPage, Yearbook pages needed: $yearbookPagesNeeded");

    // If no students found, provide helpful error message
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
    respond(true, 'Student data retrieved successfully', $responseData);
} catch (Exception $e) {
    error_log("FetchStudentData.php exception: " . $e->getMessage());
    error_log("Exception trace: " . $e->getTraceAsString());
    respond(false, 'Server error: ' . $e->getMessage());
}
