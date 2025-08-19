<?php
require __DIR__ . '/../../vendor/autoload.php'; 

use MongoDB\Client;

// Connect to MongoDB
// Get MongoDB connection string from environment variable
$mongoUrl = getenv('MONGO_URL') ?: 'mongodb://localhost:27017';
$client = new Client($mongoUrl);
$db = $client->Departments;

// Define all program collections
$collections = [
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

$allStudents = [];

// Filter to show only Maritime Education students by default
$maritimeCollections = [
    "bsme" => "BS Marine Engineering"
];

// Password generator function
function generatePassword($length = 8) {
    $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lower = 'abcdefghijklmnopqrstuvwxyz';
    $digits = '0123456789';
    $special = '!@#$%^&*()_+-={}[]|:;<>,.?';
    
    $password = '';
    // Ensure at least one uppercase and one special character
    $password .= $upper[random_int(0, strlen($upper) - 1)];
    $password .= $special[random_int(0, strlen($special) - 1)];
    
    $all = $upper . $lower . $digits . $special;
    for ($i = 2; $i < $length; $i++) {
        $password .= $all[random_int(0, strlen($all) - 1)];
    }
    // Shuffle to randomize position
    return str_shuffle($password);
}

// Fetch students from Maritime Education collections only
foreach ($maritimeCollections as $collectionKey => $programName) {
    try {
        $collection = $db->$collectionKey;
        $cursor = $collection->find();
        
        foreach ($cursor as $student) {
            // Check if password exists
            if (empty($student['password'])) {
                $password = generatePassword(8);
                // Update the student document in MongoDB
                $collection->updateOne(
                    ['_id' => $student['_id']],
                    ['$set' => ['password' => $password]]
                );
            } else {
                $password = $student['password'];
            }

            $allStudents[] = [
                'id' => $student['id'] ?? '',
                'student_id' => $student['student id'] ?? '',
                'first_name' => $student['first name'] ?? '',
                'middle_name' => $student['middle name'] ?? '',
                'last_name' => $student['last name'] ?? '',
                'department_section' => $student['department section'] ?? $programName,
                'academic_year' => $student['academic year'] ?? '',
                'status' => $student['status'] ?? 'Pending', // Get status from database or default to Pending
                'collection' => $collectionKey,
                'password' => $password // Use the stored/generated password
            ];
        }
    } catch (Exception $e) {
        // Skip collections that don't exist
        continue;
    }
}

// Sort students by ID
usort($allStudents, function($a, $b) {
    return $a['id'] - $b['id'];
});

// Limit to 10 students by default
$allStudents = array_slice($allStudents, 0, 10);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../Assets/css/StudentList.css">
</head>

<body>

    <div class="container">
        <div class="header-container" style="width: 100%;">
            <h1>Student List</h1>
        </div>
        <div class="form-content" style="width: 100%, ">
            <div class="card">
                <div class="card-header">
                    <div class="filter-bar">
                        <label for="entries-count" class="filter-label">
                            <select id="entries-count" class="filter-select">
                                <option value="" disabled selected>Show Users</option>
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </label>
                        <label for="department-filter" class="filter-label">
                            <select id="department-filter" class="filter-select">
                                <option value="" disabled selected>Select Department</option>
                                <option value="maritime">Marine Engineering</option>
                                <option value="maritime">Marine Transfortation</option>
                                <option value="criminology">Criminology</option>
                                <option value="tourism">Tourism Management</option>
                                <option value="education">Technical-Vocational Teacher Education</option>
                                <option value="education">Early Childhood Education</option>
                                <option value="nursing">Nursing</option>
                                <option value="information">Information System</option>
                                <option value="business">Entrepreneurship</option>
                                <option value="maritime">Management Accouting</option>
                                <!-- Add more departments as needed -->
                            </select>
                        </label>
                        <label for="status-filter" class="filter-label">
                            <select id="status-filter" class="filter-select">
                                <option value="" disabled selected>Select Status</option>
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                            </select>
                        </label>
                    </div>
                </div>

                <!-- Student Data Section -->
                <div class="card-datatable">
                    <table style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr class="student-row header">
                                <th class="student-name-header student-name">STUDENT</th>
                                <th class="student-id-header student-id">ID NUMBER</th>
                                <th class="student-dept-header student-dept">DEPARTMENT</th>
                                <th class="student-year-header student-year">ACADEMIC YEAR</th>
                                <th class="student-status-header student-status">STATUS</th>
                                <th class="student-password-header student-password">PASSWORD</th>
                                <th class="student-actions-header student-actions">ACTIONS</th>
                                <th class="student-checkbox-header">
                                    <input type="checkbox" id="select-all-header" title="Select All">
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($allStudents)): ?>
                            <tr class="no-students">
                                <td colspan="8" style="text-align:center; padding:40px; color:#fff; font-style:italic;">
                                    No students found in the database.</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($allStudents as $student): ?>
                            <tr class="student-row">
                                <td class="student-name">
                                    <?php 
                                    // Get the first letter of middle name as initial
                                    $middleInitial = !empty($student['middle_name']) ? substr($student['middle_name'], 0, 1) . '.' : '';
                                    echo htmlspecialchars($student['last_name'] . ', ' . $student['first_name'] . ' ' . $middleInitial); 
                                    ?>
                                </td>
                                <td class="student-id"><?php echo htmlspecialchars($student['student_id']); ?></td>
                                <td class="student-dept"><?php echo htmlspecialchars($student['department_section']); ?>
                                </td>
                                <td class="student-year"><?php echo htmlspecialchars($student['academic_year']); ?></td>
                                <td
                                    class="student-status <?php echo ($student['status'] === 'Active') ? 'status-active' : 'status-pending'; ?>">
                                    <?php echo htmlspecialchars($student['status']); ?></td>
                                <td class="student-password">
                                    <span class="password-text"
                                        data-password="<?php echo htmlspecialchars($student['password']); ?>">********</span>
                                </td>
                                <td class="student-actions">
                                    <input type="checkbox" class="student-checkbox"
                                        data-student-id="<?php echo htmlspecialchars($student['student_id']); ?>"
                                        data-collection="<?php echo htmlspecialchars($student['collection']); ?>"
                                        <?php echo ($student['status'] === 'Active') ? 'checked' : ''; ?>>
                                    <div class="eyeIcon close eyeIcon-list"
                                        style="margin-right:0.5em;display:flex;align-items:center;cursor:pointer;"
                                        onclick="togglePass(this)">
                                        <!-- SVG for closed eye -->
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                            style="height: 1.2em; vertical-align: middle;">
                                            <g fill="none" fill-rule="evenodd">
                                                <path
                                                    d="M24 0v24H0V0zM12.593 23.258l-.011.002l-.071.035l-.02.004l-.014-.004l-.071-.035q-.016-.005-.024.005l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427q-.004-.016-.017-.018m.265-.113l-.013.002l-.185.093l-.01.01l-.003.011l.018.43l.005.012l.008.007l.201.093q.019.005.029-.008l.004-.014l-.034-.614q-.005-.019-.02-.022m-.715.002a.02.02 0 0 0-.027.006l-.006.014l-.034.614q.001.018.017.024l.015-.002l.201-.093l.01-.008l.004-.011l.017-.43l-.003-.012l-.01-.01z" />
                                                <path fill="#000000"
                                                    d="M2.5 9a1.5 1.5 0 0 1 2.945-.404c1.947 6.502 11.158 6.503 13.109.005a1.5 1.5 0 1 1 2.877.85a10.1 10.1 0 0 1-1.623 3.236l.96.96a1.5 1.5 0 1 1-2.122 2.12l-1.01-1.01a9.6 9.6 0 0 1-1.67.915l.243.906a1.5 1.5 0 0 1-2.897.776l-.251-.935c-.705.073-1.417.073-2.122 0l-.25.935a1.5 1.5 0 0 1-2.898-.776l.242-.907a9.6 9.6 0 0 1-1.669-.914l-1.01 1.01a1.5 1.5 0 1 1-2.122-2.12l.96-.96a10.1 10.1 0 0 1-1.62-3.23A1.5 1.5 0 0 1 2.5 9" />
                                            </g>
                                        </svg>
                                    </div>
                                    <div class="eyeIcon open eyeIcon-list"
                                        style="margin-right:0.5em;display:none;align-items:center;cursor:pointer;"
                                        onclick="togglePass(this)">
                                        <!-- SVG for open eye -->
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                            style="height: 1.2em; vertical-align: middle;">
                                            <g fill="none">
                                                <path
                                                    d="M24 0v24H0V0zM12.593 23.258l-.011.002l-.071.035l-.02.004l-.014-.004l-.071-.035q-.016-.005-.024.005l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427q-.004-.016-.017-.018m.265-.113l-.013.002l-.185.093l-.01.01l-.003.011l.018.43l.005.012l.008.007l.201.093q.019.005.029-.008l.004-.014l-.034-.614q-.005-.019-.02-.022m-.715.002a.02.02 0 0 0-.027.006l-.006.014l-.034.614q.001.018.017.024l.015-.002l.201-.093l.01-.008l.004-.011l.017-.43l-.003-.012l-.01-.01z" />
                                                <path fill="#000000"
                                                    d="M12 5c3.679 0 8.162 2.417 9.73 5.901c.146.328.27.71.27 1.099c0 .388-.123.771-.27 1.099C20.161 16.583 15.678 19 12 19s-8.162-2.417-9.73-5.901C2.124 12.77 2 12.389 2 12c0-.388.123-.771.27-1.099C3.839 7.417 8.322 5 12 5m0 3a4 4 0 1 0 0 8a4 4 0 0 0 0-8m0 2a2 2 0 1 1 0 4a2 2 0 0 1 0-4" />
                                            </g>
                                        </svg>
                                    </div>
                                    <button class="action-btn edit-btn"
                                        onclick="editStudent('<?php echo htmlspecialchars($student['student_id']); ?>', '<?php echo htmlspecialchars($student['collection']); ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn delete-btn"
                                        onclick="deleteStudent('<?php echo htmlspecialchars($student['student_id']); ?>', '<?php echo htmlspecialchars($student['collection']); ?>')">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                </div>

            </div>
        </div>
    </div>
    </div>
</body>
</div>
<script src="../Assets/js/StudentList.js"></script>

</html>