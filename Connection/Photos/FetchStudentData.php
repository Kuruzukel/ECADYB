<?php
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
    header('Content-Type: application/json');
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));
    exit;
}

try {
    $template = isset($_GET['template']) ? (int)$_GET['template'] : 1;
    $department = isset($_GET['department']) ? strtoupper($_GET['department']) : null;

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
        'BSN' => ['bsn'],
        'BSTM' => ['bstm']
    ];

    if (!isset($departmentCollections[$department])) {
        respond(false, 'Invalid department code: ' . $department);
    }

    $mongoDbName = "BatchTemplate" . $template;
    $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';

    $mongoClient = new MongoDB\Client($mongoUrl, [
        'serverSelectionTimeoutMS' => 5000,
        'connectTimeoutMS' => 5000,
        'socketTimeoutMS' => 5000
    ]);

    $db = $mongoClient->$mongoDbName;

    // Get collections for this department
    $collections = $departmentCollections[$department];

    // Fetch all students from relevant collections
    $allStudents = [];

    foreach ($collections as $collectionName) {
        try {
            // Check if collection exists
            $collectionNames = iterator_to_array($db->listCollectionNames());
            $collectionExists = in_array($collectionName, $collectionNames);

            if ($collectionExists) {
                $collection = $db->$collectionName;
                $students = $collection->find([], ['sort' => ['name' => 1]]);

                foreach ($students as $student) {
                    // Construct the full name from individual fields
                    $fullName = '';
                    if (isset($student['name']) && !empty($student['name'])) {
                        // If name field exists, use it
                        $fullName = $student['name'];
                    } else {
                        // Otherwise, construct from first, middle, last name
                        $firstName = $student['first name'] ?? '';
                        $middleName = $student['middle name'] ?? '';
                        $lastName = $student['last name'] ?? '';

                        // Format: First Name Middle Initial Last Name (e.g., "Enric John L. Reyes")
                        if (!empty($firstName) || !empty($lastName)) {
                            $parts = [];
                            if (!empty($firstName)) {
                                $parts[] = $firstName;
                            }
                            // Handle middle name/initial
                            if (!empty($middleName)) {
                                // If middle name is already just an initial (like "L." or "L"), use as is
                                if (strlen($middleName) <= 2) {
                                    $parts[] = rtrim($middleName, '.') . '.';
                                } else {
                                    // Otherwise, take first character and add period
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

                    // Process milestones - handle both string and array formats
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

                    // Process honors
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
            }
        } catch (Exception $e) {
            error_log("Error fetching from collection $collectionName: " . $e->getMessage());
            // Continue with other collections even if one fails
        }
    }

    // Calculate number of pages needed (6 students per page)
    $studentsPerPage = 6;
    $totalStudents = count($allStudents);
    $totalPages = ceil($totalStudents / $studentsPerPage);

    // Prepare response
    $response = [
        'success' => true,
        'message' => 'Student data retrieved successfully',
        'data' => [
            'students' => $allStudents,
            'total_students' => $totalStudents,
            'total_pages' => $totalPages,
            'students_per_page' => $studentsPerPage,
            'department' => $department,
            'template' => $template
        ]
    ];

    respond(true, 'Student data retrieved successfully', $response);
} catch (Exception $e) {
    error_log("FetchStudentData.php exception: " . $e->getMessage());
    respond(false, 'Server error: ' . $e->getMessage());
}
