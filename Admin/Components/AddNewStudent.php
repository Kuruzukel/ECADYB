<?php
require __DIR__ . '/../../vendor/autoload.php'; 

use MongoDB\Client;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header('Content-Type: application/json');

    // Get MongoDB connection string from environment variable
$mongoUrl = getenv('MONGO_URL') ?: 'mongodb://localhost:27017';
$client = new Client($mongoUrl);
    $db = $client->Departments;

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

    $programKey = $_POST["program"] ?? '';
    $programName = $programMap[$programKey] ?? 'Unknown';

    $section = trim($_POST["section"] ?? '');

    $student = [
        "first name" => trim($_POST["first_name"] ?? ''),
        "middle name" => trim($_POST["middle_name"] ?? ''),
        "last name" => trim($_POST["last_name"] ?? ''),
        "email" => trim($_POST["email"] ?? ''),
        "academic year" => trim($_POST["academic_year"] ?? ''),
        "student id" => trim($_POST["student_id"] ?? ''),
        "program" => $programName,
        "section" => $section,
        "department section" => $programName . ' - ' . $section,
        "motto" => trim($_POST["motto"] ?? ''),
        "honors" => trim($_POST["honors"] ?? ''),
        "milestone" => trim($_POST["milestone"] ?? ''),
        "batch name" => trim($_POST["batch_name"] ?? '')
    ];

    $collection = $db->$programKey;

    $studentCount = $collection->countDocuments();
    $student["id"] = $studentCount + 1;

    $collection->insertOne($student);

    echo json_encode([
        "success" => true,
        "message" => "Student added successfully to '$programKey' collection with ID {$student['id']}."
    ]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add New Student</title>
    <link rel="stylesheet" href="../Assets/css/AddNewStudent.css">
</head>

<body>
    <div class="container" style="font-family: Arial, sans-serif;">
        <div class="header-container" style="width: 100%;">
            <h1>Add Student Details</h1>
        </div>

        <form id="addStudentForm">
            <div class="form-content" style="width: 100%;">
                <div class="form-group">
                    <div class="section">
                        <div class="section-header">Personal Information</div>

                        <label for="first-name">First Name:</label>
                        <input type="text" id="first-name" name="first_name" oninput="allowOnlyLetters(this)"
                            onkeypress="return /[a-zA-Z\s]/.test(event.key)" placeholder="First Name">

                        <label for="middle-name">Middle Name:</label>
                        <input type="text" id="middle-name" name="middle_name"
                            oninput="allowOnlyLetters(this);removeSpaces(this)"
                            onkeypress="return /[a-zA-Z\s]/.test(event.key)" placeholder="Middle Name">

                        <label for="last-name">Last Name:</label>
                        <input type="text" id="last-name" name="last_name"
                            oninput="allowOnlyLetters(this);removeSpaces(this)"
                            onkeypress="return /[a-zA-Z\s]/.test(event.key)" placeholder="Last Name">

                        <label for="email">Email:</label>
                        <input type="text" id="email" name="email" oninput="removeSpaces(this)" placeholder="Email">
                    </div>

                    <div class="section">
                        <div class="section-header">Academic Information</div>

                        <label for="academic-year">Academic Year:</label>
                        <input type="text" id="academic-year" name="academic_year" placeholder="0000-0000" maxlength="9"
                            oninput="formatAcademicYear(this)">

                        <label for="program">Program:</label>
                        <select id="program" name="program">
                            <option value="" disabled selected>Select a program</option>
                            <option value="bsme">BS Marine Engineering</option>
                            <option value="bsmt">BS Marine Transportation</option>
                            <option value="bscje">BS Criminal Justice Education</option>
                            <option value="bstm">BS Tourism Management</option>
                            <option value="btvted">BS Technical-Vocational Teacher Education</option>
                            <option value="beced">BS Early Childhood Education</option>
                            <option value="bsn">BS Nursing</option>
                            <option value="bsis">BS Information System</option>
                            <option value="bsma">BS Management Accounting</option>
                            <option value="bse">BS Entrepreneurship</option>
                        </select>

                        <label for="section">Section:</label>
                        <input type="text" id="section" name="section" placeholder="Section"
                            oninput="allowOnlyLetters(this)">

                        <label for="student-id">Student ID:</label>
                        <input type="text" id="student-id" name="student_id" placeholder="0000-000000" maxlength="11"
                            oninput="formatStudentID(this)">
                    </div>

                    <div class="section">
                        <div class="section-header">Additional Information</div>

                        <label for="motto">Personal Philosophy:</label>
                        <input type="text" id="motto" name="motto" placeholder="Personal Philosophy">

                        <label for="honors">Latin Awards:</label>
                        <input type="text" id="honors" name="honors" placeholder="Latin Awards">

                        <label for="milestone">Career Highlights:</label>
                        <input type="text" id="milestone" name="milestone" placeholder="Career Highlights">

                        <label for="bacth-name">Batch Name:</label>
                        <input type="text" id="batch-name" name="batch_name" placeholder="Batch Name"
                            oninput="allowOnlyLetters(this)">

                    </div>
                </div>
                <button type="button" class="submit-btn" id="add-student-btn">Add Student</button>

                <p id="responseMessage" class="success" style="text-align: center; margin-top: 10px;"></p>


            </div>
        </form>

        <!-- Modal -->
        <div class="modal-overlay" id="modal-overlay" style="display: none;">
            <div class="modal" style="font-family: Arial, sans-serif;">
                <h2>Are you sure you want to add this student?</h2>
                <div class="modal-buttons">
                    <button class="modal-btn confirm" id="confirm-btn">Yes, Add</button>
                    <button class="modal-btn cancel" id="cancel-btn">Cancel</button>
                </div>
            </div>
        </div>

    </div>

    <script src="../Assets/js/AddNewStudent.js"></script>
</body>

</html>