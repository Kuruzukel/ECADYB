<?php
// Prevent direct access to this file
if (!defined('ADMIN_DASHBOARD_INCLUDED')) {
    // If accessed directly, redirect to the proper route
    header('Location: ../');
    exit;
}

// Check if this is being included in AdminDashboard
$isIncludedInDashboard = defined('ADMIN_DASHBOARD_INCLUDED');
$outputFullHtml = !$isIncludedInDashboard;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    while (ob_get_level()) {
        ob_end_clean();
    }

    ob_start();
    error_reporting(0);
    ini_set('display_errors', 0);

    header('Content-Type: application/json; charset=UTF-8');

    require_once __DIR__ . '/../../vendor/autoload.php';

    $upper = 'ABCDEFGHIJKLMNPQRSTUVWXYZ';
    $lower = 'abcdefghijkmnopqrstuvwxyz';
    $digits = '123456789';
    $special = '!@#_$';

    function generateRandomPassword($length = 8)
    {
        global $upper, $lower, $digits, $special;

        $characters = $upper . $lower . $digits . $special;
        $password = '';
        $charactersLength = strlen($characters);

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, $charactersLength - 1)];
        }

        return $password;
    }

    $programMap = [
        "bsme" => "BS Marine Engineering",
        "bsmt" => "BS Marine Transportation",
        "bscje" => "BS Criminal Justice Education",
        "bstm" => "BS Tourism Management",
        "btvted" => "BS Technical-Vocational Teacher Education",
        "beced" => "BS Early Childhood Education",
        "bsn" => "BS Nursing",
        "bsis" => "BS Information System",
        "bsma" => "BS Management Accounting",
        "bse" => "BS Entrepreneurship"
    ];

    $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';

    $selectedTemplate = isset($_POST['batch_template']) ? (int)$_POST['batch_template'] : 1;
    if ($selectedTemplate < 1 || $selectedTemplate > 3) {
        $selectedTemplate = 1;
    }
    $dbName = "BatchTemplate" . $selectedTemplate;

    try {
        $client = new MongoDB\Client($mongoUrl);
        $db = $client->$dbName;
    } catch (Exception $e) {
        echo json_encode([
            "success" => false,
            "message" => "MongoDB connection failed: " . $e->getMessage()
        ]);
        die();
    }

    $programKey = trim($_POST["program"] ?? '');
    $programName = $programMap[$programKey] ?? 'Unknown';
    $section = trim($_POST["section"] ?? '');

    $requiredFields = ['first_name', 'last_name', 'email', 'academic_year', 'student_id'];
    foreach ($requiredFields as $field) {
        if (empty(trim($_POST[$field] ?? ''))) {
            echo json_encode([
                "success" => false,
                "message" => ucfirst(str_replace("_", " ", $field)) . " is required."
            ]);
            die();
        }
    }

    if (!$programKey) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid program selected."
        ]);
        die();
    }

    $student = [
        "first name" => trim($_POST["first_name"]),
        "middle name" => trim($_POST["middle_name"] ?? ''),
        "last name" => trim($_POST["last_name"]),
        "email" => trim($_POST["email"]),
        "academic year" => trim($_POST["academic_year"]),
        "student id" => trim($_POST["student_id"]),
        "program" => $programName,
        "section" => $section,
        "department section" => strtoupper($programKey) . ' - ' . strtoupper($section),
        "password" => generateRandomPassword(8),
        "status" => "Pending"
    ];

    $studentName = $student["first name"] . ' ' . $student["last name"];
    error_log("Generated password for new student: " . $studentName . " - Password: " . $student["password"]);

    $optionalFields = ["motto", "honors", "milestone"];
    foreach ($optionalFields as $field) {
        if (!empty(trim($_POST[$field] ?? ''))) {
            $student[$field] = trim($_POST[$field]);
        }
    }

    try {
        $collection = $db->$programKey;

        $student["id"] = $collection->countDocuments() + 1;

        $insertResult = $collection->insertOne($student);

        if ($insertResult->getInsertedCount() > 0) {
            ob_end_clean();
            die(json_encode([
                "success" => true,
                "message" => "Student added successfully!"
            ]));
        } else {
            ob_end_clean();
            die(json_encode([
                "success" => false,
                "message" => "Failed to add student."
            ]));
        }
    } catch (Exception $e) {
        ob_end_clean();
        die(json_encode([
            "success" => false,
            "message" => "Error inserting student: " . $e->getMessage()
        ]));
    }

    ob_end_clean();
    die();
}

error_reporting(0);
ini_set('display_errors', 0);

require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

$programMap = [
    "bsme" => "BS Marine Engineering",
    "bsmt" => "BS Marine Transportation",
    "bscje" => "BS Criminal Justice Education",
    "bstm" => "BS Tourism Management",
    "btvted" => "BS Technical-Vocational Teacher Education",
    "beced" => "BS Early Childhood Education",
    "bsn" => "BS Nursing",
    "bsis" => "BS Information System",
    "bsma" => "BS Management Accounting",
    "bse" => "BS Entrepreneurship"
];

if ($outputFullHtml):
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add New Student</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="/Admin/assets/css/AddNewStudent.css">
</head>

<body>
<?php endif; ?>
    <div class="container" style="font-family: Arial, sans-serif;">
        <div class="header-container" style="width: 100%;">
            <h1><i class="fas fa-home"></i> <span class="chevron"><i class="fas fa-chevron-right"></i></span> Add New
                Student</h1>
        </div>

        <form id="addStudentForm">
            <div class="form-content" style="width: 100%;">
                <div class="form-group">
                    <div class="section">
                        <div class="section-header">Personal Information</div>
                        <label for="first-name">First Name:</label>
                        <input type="text" id="first-name" name="first_name" placeholder="First Name">

                        <label for="middle-name">Middle Name:</label>
                        <input type="text" id="middle-name" name="middle_name" placeholder="Middle Name">

                        <label for="last-name">Last Name:</label>
                        <input type="text" id="last-name" name="last_name" placeholder="Last Name">

                        <label for="email">Email:</label>
                        <input type="text" id="email" name="email" placeholder="Email">
                    </div>

                    <div class="section">
                        <div class="section-header">Academic Information</div>
                        <label for="academic-year">Academic Year:</label>
                        <input type="text" id="academic-year" name="academic_year" placeholder="0000-0000" maxlength="9"
                            oninput="formatAcademicYear(this)">

                        <label for="program">Program:</label>
                        <select id="program" name="program">
                            <option value="" disabled selected>Select a program</option>
                            <?php foreach ($programMap as $key => $name) : ?>
                                <option value="<?= $key ?>"><?= $name ?></option>
                            <?php endforeach; ?>
                        </select>


                        <label for="section">Section:</label>
                        <input type="text" id="section" name="section" placeholder="Section">

                        <label for="student-id">Student ID:</label>
                        <input type="text" id="student-id" name="student_id" placeholder="0000-000000" maxlength="11"
                            oninput="formatStudentID(this)">
                    </div>

                    <div class="section">
                        <div class="section-header">Additional Information / Optional</div>
                        <label for="motto">Personal Philosophy:</label>
                        <input type="text" id="motto" name="motto" placeholder="Personal Philosophy">

                        <label for="honors">Latin Awards:</label>
                        <input type="text" id="honors" name="honors" placeholder="Latin Awards">

                        <label for="milestone">Career Highlights:</label>
                        <input type="text" id="milestone" name="milestone" placeholder="Career Highlights">

                        <button type="button" class="submit-btn" id="add-student-btn">
                            <i class="fas fa-user-plus"></i> Add Student
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <div id="notification-container"></div>

        <div class="modal-overlay" id="modal-overlay" style="display: none;">
            <div class="modal" style="font-family: Arial, sans-serif;">
                <div class="modal-header">
                    <i class="fas fa-question-circle modal-icon"></i>
                    <h2>Confirm Student Addition</h2>
                </div>
                <div class="modal-content">
                    <p>Are you sure you want to add this student?</p>
                </div>
                <div class="modal-buttons">
                    <button class="modal-btn confirm" id="confirm-btn">
                        <i class="fas fa-check"></i> Yes, Add
                    </button>
                    <button class="modal-btn cancel" id="cancel-btn">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="/Admin/assets/js/AddNewStudent.js"></script>
<?php if ($outputFullHtml): ?>
</body>

</html>
<?php endif; ?>