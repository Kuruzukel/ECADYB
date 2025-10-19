<?php
// Only include this file if it's being accessed through AdminDashboard.php
if (!defined('ADMIN_DASHBOARD_INCLUDED')) {
    // If accessed directly, redirect to the proper route
    header('Location: ../');
    exit;
}

// Check if this is being included in AdminDashboard (not AJAX request)
$isIncludedInDashboard = defined('ADMIN_DASHBOARD_INCLUDED');

// Enable error display for localhost debugging
if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

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

use MongoDB\BSON\ObjectId;

// Only start output buffering if not included in dashboard
if (!$isIncludedInDashboard) {
    ob_start();
}

date_default_timezone_set('Asia/Manila');

$mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';

$selectedTemplate = isset($_GET['template']) ? (int)$_GET['template'] : 1;
if ($selectedTemplate < 1 || $selectedTemplate > 3) {
    $selectedTemplate = 1;
}

$dbName = "BatchTemplate" . $selectedTemplate;

$client = new Client($mongoUrl, [
    'connectTimeoutMS' => 5000,
    'socketTimeoutMS' => 30000,
    'serverSelectionTimeoutMS' => 5000,
    'readPreference' => 'primaryPreferred'
]);

$db = $client->$dbName;

$collections = [
    "bsme"   => "BS Marine Engineering",
    "bsmt"   => "BS Marine Transportation",
    "bscje"  => "BS Criminal Justice Education",
    "bstm"   => "BS Tourism Management",
    "btvted" => "BS Technical-Vocational Teacher Education",
    "beced"  => "BS Early Childhood Education",
    "bsn"    => "BS Nursing",
    "bsis"   => "BS Information System",
    "bsma"   => "BS Management Accounting",
    "bse"    => "BS Entrepreneurship"
];

$selectedDepartment = $_GET['department'] ?? "bsme";
if (!array_key_exists($selectedDepartment, $collections)) {
    $selectedDepartment = "bsme";
}

$perPage = 10;
$page = isset($_GET['pageNum']) ? max(1, (int)$_GET['pageNum']) : 1;
$skip = ($page - 1) * $perPage;

$allStudents = [];
$totalStudents = 0;

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] == '1';

// If included in dashboard, don't output full HTML structure
$outputFullHtml = !$isIncludedInDashboard && !$isAjax;

try {
    $collection = $db->$selectedDepartment;


    $totalStudents = $collection->countDocuments();
    $totalPages = ceil($totalStudents / $perPage);

    $cursor = $collection->find(
        [],
        [
            'projection' => [
                '_id' => 1,
                'id' => 1,
                'student id' => 1,
                'first name' => 1,
                'middle name' => 1,
                'last name' => 1,
                'email' => 1,
                'academic year' => 1,
                'program' => 1,
                'section' => 1,
                'department section' => 1,
                'motto' => 1,
                'honors' => 1,
                'milestone' => 1,
                'status' => 1,
                'password' => 1
            ],
            'skip' => $skip,
            'limit' => $perPage,
            'sort' => ['department section' => 1, 'last name' => 1]
        ]
    );

    foreach ($cursor as $student) {
        $studentPassword = $student['password'] ?? '';
        $studentObjectId = $student['_id'] ?? null;

        if (empty($studentPassword) && $studentObjectId) {
            $studentPassword = generateRandomPassword(8);

            try {
                $updateCollection = $db->$selectedDepartment;
                $result = $updateCollection->updateOne(
                    ['_id' => $studentObjectId],
                    ['$set' => ['password' => $studentPassword]]
                );

                if ($result->getModifiedCount() > 0) {
                    error_log("✓ Generated and saved password for student: " . ($student['first name'] ?? '') . ' ' . ($student['last name'] ?? ''));
                } else {
                    error_log("⚠ Password update returned 0 modified documents for student: " . ($student['first name'] ?? '') . ' ' . ($student['last name'] ?? ''));
                }
            } catch (Exception $e) {
                error_log("✗ Error updating password for student: " . $e->getMessage());
            }
        }

        $allStudents[] = [
            'id' => $student['id'] ?? 0,
            'student_id' => $student['student id'] ?? '',
            'first_name' => $student['first name'] ?? '',
            'middle_name' => $student['middle name'] ?? '',
            'last_name' => $student['last name'] ?? '',
            'email' => $student['email'] ?? '',
            'academic_year' => $student['academic year'] ?? '',
            'program' => $student['program'] ?? '',
            'section' => $student['section'] ?? '',
            'department_section' => $student['department section'] ?? $collections[$selectedDepartment],
            'motto' => $student['motto'] ?? '',
            'honors' => $student['honors'] ?? '',
            'milestone' => $student['milestone'] ?? '',
            'status' => $student['status'] ?? 'Pending',
            'collection' => $selectedDepartment,
            'password' => $studentPassword
        ];
    }
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $allStudents = [];
    $totalPages = 1;
    
    // Display error on localhost for debugging
    if ($isIncludedInDashboard && (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false)) {
        echo '<div style="padding: 20px; background: #fee; border: 2px solid #f00; margin: 20px; border-radius: 5px;">';
        echo '<h3 style="color: #f00; margin-top: 0;">Database Connection Error</h3>';
        echo '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><strong>MongoDB URL:</strong> ' . htmlspecialchars($mongoUrl) . '</p>';
        echo '<p><strong>Database:</strong> ' . htmlspecialchars($dbName) . '</p>';
        echo '<p><strong>Collection:</strong> ' . htmlspecialchars($selectedDepartment) . '</p>';
        echo '<p style="color: #666; font-size: 12px; margin-bottom: 0;">This error is only visible on localhost for debugging purposes.</p>';
        echo '</div>';
    }
}

?>

<?php 
// Debug output for localhost
if ($isIncludedInDashboard && (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false)) {
    echo '<!-- DEBUG: StudentList.php loaded successfully -->';
    echo '<!-- DEBUG: Total students: ' . $totalStudents . ' -->';
    echo '<!-- DEBUG: Total pages: ' . $totalPages . ' -->';
    echo '<!-- DEBUG: isIncludedInDashboard: ' . ($isIncludedInDashboard ? 'true' : 'false') . ' -->';
    echo '<!-- DEBUG: outputFullHtml: ' . ($outputFullHtml ? 'true' : 'false') . ' -->';
}
?>

<?php if ($outputFullHtml): ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Student List</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <link rel="stylesheet" href="<?= $basePath ?>/Admin/assets/css/StudentList.css">
    </head>

    <body>
<?php endif; ?>
    <div class="container">
        <div class="header-container">
            <h1><i class="fas fa-home"></i> <span class="chevron"><i class="fas fa-chevron-right"></i></span> Student
                List</h1>
        </div>

        <div class="form-content">
            <div class="card">
                <div class="card-header">
                    <div class="filter-bar">
                        <label for="template-filter" class="filter-label" style="position: relative;">
                            <select id="template-filter" class="filter-select"
                                title="Template is locked. Change template selection in Batch Templates page.">
                                <option value="" disabled>Select Batch Template (Locked)</option>
                                <option value="1" <?php if ($selectedTemplate == 1) echo "selected"; ?>>
                                    Batch Template 1
                                </option>
                                <option value="2" <?php if ($selectedTemplate == 2) echo "selected"; ?>>
                                    Batch Template 2
                                </option>
                                <option value="3" <?php if ($selectedTemplate == 3) echo "selected"; ?>>
                                    Batch Template 3
                                </option>
                            </select>
                            <i class="fas fa-lock"
                                style="position: absolute; right: 30px; top: 50%; transform: translateY(-50%); color: #f39c12; pointer-events: none; font-size: 14px;"
                                title="Locked"></i>
                        </label>

                        <label for="department-filter" class="filter-label">
                            <select id="department-filter" class="filter-select">
                                <option value="" disabled>Select Department</option>
                                <?php foreach ($collections as $key => $name): ?>
                                    <option value="<?php echo $key; ?>"
                                        <?php if ($selectedDepartment == $key) echo "selected"; ?>>
                                        <?php echo $name; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label for="status-filter" class="filter-label">
                            <select id="status-filter" class="filter-select">
                                <option value="" disabled selected>Select Status</option>
                                <option value="active">Active</option>
                                <option value="pending">Pending</option>
                            </select>
                        </label>

                        <input type="hidden" id="current-tab"
                            value="<?php echo htmlspecialchars($_GET['tab'] ?? 'all'); ?>">
                        <div class="pagination-controls"
                            style="margin-top:1em; display:flex; justify-content:center; gap:1em;">

                            <?php if ($page > 1): ?>
                                <button id="prev-btn" onclick="changePage(<?php echo $page - 1; ?>)">Previous</button>
                            <?php else: ?>
                                <button id="prev-btn" disabled>Previous</button>
                            <?php endif; ?>

                            <?php if ($page < $totalPages): ?>
                                <button id="next-btn" onclick="changePage(<?php echo $page + 1; ?>)">Next</button>
                            <?php else: ?>
                                <button id="next-btn" disabled>Next</button>
                            <?php endif; ?>

                            <span>Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                        </div>
                        <input type="hidden" id="total-pages" value="<?php echo $totalPages; ?>">
                    </div>
                </div>

                <div class="card-datatable">
                    <table style="width:100%;">
                        <?php if (!empty($allStudents)): ?>
                            <thead>
                                <tr>
                                    <th>STUDENT</th>
                                    <th>ID NUMBER</th>
                                    <th>DEPARTMENT</th>
                                    <th>ACADEMIC YEAR</th>
                                    <th>STATUS</th>
                                    <th>PASSWORD</th>
                                    <th>ACTIONS <input type="checkbox" id="select-all-header" title="Select All"></th>
                                </tr>
                            </thead>
                        <?php endif; ?>
                        <tbody>
                            <?php if (empty($allStudents)): ?>
                                <tr>
                                    <td colspan="7" class="no-students-message">
                                        <div class="no-students-content">

                                            <p>No students found in this department for Batch Template
                                                <strong><?php echo htmlspecialchars($selectedTemplate); ?></strong>.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($allStudents as $student): ?>
                                    <tr>
                                        <td>
                                            <?php
                                            $middleInitial = !empty($student['middle_name']) ? substr($student['middle_name'], 0, 1) . '.' : '';
                                            echo htmlspecialchars($student['last_name'] . ', ' . $student['first_name'] . ' ' . $middleInitial);
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                        <td><?php echo htmlspecialchars($student['department_section']); ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($student['academic_year']); ?></td>
                                        <td
                                            class="student-status <?php echo (strtolower($student['status'] ?? 'pending') === 'active') ? 'status-active' : 'status-pending'; ?>">
                                            <?php echo htmlspecialchars($student['status']); ?></td>
                                        <td>
                                            <span class="password-text"
                                                data-password="<?php echo htmlspecialchars($student['password']); ?>">********</span>
                                        </td>
                                        <td>
                                            <div class="actions-container">
                                                <input type="checkbox" class="student-checkbox"
                                                    data-student-id="<?php echo htmlspecialchars($student['student_id'] ?? $student['student id'] ?? ''); ?>"
                                                    data-collection="<?php echo htmlspecialchars($student['collection'] ?? ''); ?>"
                                                    data-status="<?php echo strtolower($student['status'] ?? 'pending'); ?>"
                                                    <?php echo (strtolower($student['status'] ?? '') === 'active') ? 'checked' : ''; ?>>

                                                <div class="eyeIcon close eyeIcon-list" onclick="togglePass(this)">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                        <g fill="none" fill-rule="evenodd">
                                                            <path
                                                                d="M24 0v24H0V0zM12.593 23.258l-.011.002l-.071.035l-.02.004l-.014-.004l-.071-.035q-.016-.005-.024.005l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427q-.004-.016-.017-.018m.265-.113l-.013.002l-.185.093l-.01.01l-.003.011l.018.43l.005.012l.008.007l.201.093q.019.005.029-.008l.004-.014l-.034-.614q-.005-.019-.02-.022m-.715.002a.02.02 0 0 0-.027.006l-.006.014l-.034.614q.001.018.017.024l.015-.002l.201-.093l.01-.008l.004-.011l.017-.43l-.003-.012l-.01-.01z" />
                                                            <path fill="#ffffff"
                                                                d="M2.5 9a1.5 1.5 0 0 1 2.945-.404c1.947 6.502 11.158 6.503 13.109.005a1.5 1.5 0 1 1 2.877.85a10.1 10.1 0 0 1-1.623 3.236l.96.96a1.5 1.5 0 1 1-2.122 2.12l-1.01-1.01a9.6 9.6 0 0 1-1.67.915l.243.906a1.5 1.5 0 0 1-2.897.776l-.251-.935c-.705.073-1.417.073-2.122 0l-.25.935a1.5 1.5 0 0 1-2.898-.776l.242-.907a9.6 9.6 0 0 1-1.669-.914l-1.01 1.01a1.5 1.5 0 1 1-2.122-2.12l.96-.96a10.1 10.1 0 0 1-1.62-3.23A1.5 1.5 0 0 1 2.5 9" />
                                                        </g>
                                                    </svg>
                                                </div>
                                                <div class="eyeIcon open eyeIcon-list" onclick="togglePass(this)"
                                                    style="display:none;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                        <g fill="none">
                                                            <path
                                                                d="M24 0v24H0V0zM12.593 23.258l-.011.002l-.071.035l-.02.004l-.014-.004l-.071-.035q-.016-.005-.024.005l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427q-.004-.016-.017-.018m.265-.113l-.013.002l-.185.093l-.01.01l-.003.011l.018.43l.005.012l.008.007l.201.093q.019.005.029-.008l.004-.014l-.034-.614q-.005-.019-.02-.022m-.715.002a.02.02 0 0 0-.027.006l-.006.014l-.034.614q.001.018.017.024l.015-.002l.201-.093l.01-.008l.004-.011l.017-.43l-.003-.012l-.01-.01z" />
                                                            <path fill="#ffffff"
                                                                d="M12 5c3.679 0 8.162 2.417 9.73 5.901c.146.328.27.71.27 1.099c0 .388-.123.771-.27 1.099C20.161 16.583 15.678 19 12 19s-8.162-2.417-9.73-5.901C2.124 12.77 2 12.389 2 12c0-.388.123-.771.27-1.099C3.839 7.417 8.322 5 12 5m0 3a4 4 0 1 0 0 8a4 4 0 0 0 0-8m0 2a2 2 0 1 1 0 4a2 2 0 0 1 0-4" />
                                                        </g>
                                                    </svg>
                                                </div>

                                                <button class="action-btn edit-btn"
                                                    onclick="openModal('editModal_<?php echo $student['student_id']; ?>')">
                                                    <i class="fas fa-edit"></i>
                                                </button>

                                                <button class="action-btn delete-btn"
                                                    onclick="openDeleteModal('<?php echo htmlspecialchars($student['student_id']); ?>', '<?php echo htmlspecialchars($student['collection']); ?>')">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </div>

                                            <div id="editModal_<?php echo $student['student_id']; ?>" class="editStudentModal">
                                                <div class="modal-content" style="background: #34495e;">

                                                    <div class="modal-header">
                                                        <i class="fas fa-user-edit modal-icon"></i>
                                                        <h3>Edit Student</h3>
                                                    </div>

                                                    <div class="modal-body">
                                                        <form id="edit-student-form-<?php echo $student['student_id']; ?>"
                                                            onsubmit="return false;">
                                                            <input type="hidden"
                                                                id="collection-hidden-<?php echo $student['student_id']; ?>"
                                                                value="<?php echo htmlspecialchars($student['collection'] ?? 'students'); ?>">

                                                            <div class="form-group">

                                                                <div class="section">
                                                                    <div class="section-header">Personal Information</div>

                                                                    <label
                                                                        for="first_name<?php echo $student['student_id']; ?>">First
                                                                        Name:</label>
                                                                    <input type="text"
                                                                        id="first_name<?php echo $student['student_id']; ?>"
                                                                        name="first_name"
                                                                        value="<?php echo htmlspecialchars($student['first_name'] ?? ''); ?>"
                                                                        required oninput="allowOnlyLetters(this)"
                                                                        onkeypress="return /[a-zA-Z\s]/.test(event.key)"
                                                                        placeholder="First Name">

                                                                    <label
                                                                        for="middle_name<?php echo $student['student_id']; ?>">Middle
                                                                        Name:</label>
                                                                    <input type="text"
                                                                        id="middle_name<?php echo $student['student_id']; ?>"
                                                                        name="middle_name"
                                                                        value="<?php echo htmlspecialchars($student['middle_name'] ?? ''); ?>"
                                                                        oninput="allowOnlyLetters(this);removeSpaces(this)"
                                                                        onkeypress="return /[a-zA-Z\s]/.test(event.key)"
                                                                        placeholder="Middle Name">

                                                                    <label
                                                                        for="last_name<?php echo $student['student_id']; ?>">Last
                                                                        Name:</label>
                                                                    <input type="text"
                                                                        id="last_name<?php echo $student['student_id']; ?>"
                                                                        name="last_name"
                                                                        value="<?php echo htmlspecialchars($student['last_name'] ?? ''); ?>"
                                                                        required
                                                                        oninput="allowOnlyLetters(this);removeSpaces(this)"
                                                                        onkeypress="return /[a-zA-Z\s]/.test(event.key)"
                                                                        placeholder="Last Name">

                                                                    <label
                                                                        for="email<?php echo $student['student_id']; ?>">Email:</label>
                                                                    <input type="text"
                                                                        id="email<?php echo $student['student_id']; ?>"
                                                                        name="email"
                                                                        value="<?php echo htmlspecialchars($student['email'] ?? ''); ?>"
                                                                        oninput="removeSpaces(this)" placeholder="Email">

                                                                    <label
                                                                        for="password<?php echo $student['student_id']; ?>">Password:</label>
                                                                    <input type="text"
                                                                        id="password<?php echo $student['student_id']; ?>"
                                                                        name="password"
                                                                        value="<?php echo htmlspecialchars($student['password'] ?? ''); ?>"
                                                                        placeholder="Password">
                                                                </div>

                                                                <div class="section">
                                                                    <div class="section-header">Academic Information</div>

                                                                    <label
                                                                        for="academic_year<?php echo $student['student_id']; ?>">Academic
                                                                        Year:</label>
                                                                    <input type="text"
                                                                        id="academic_year<?php echo $student['student_id']; ?>"
                                                                        name="academic_year"
                                                                        value="<?php echo htmlspecialchars($student['academic_year'] ?? ''); ?>"
                                                                        placeholder="0000-0000" maxlength="9"
                                                                        oninput="formatAcademicYear(this)">

                                                                    <label
                                                                        for="program<?php echo $student['student_id']; ?>">Program:</label>
                                                                    <select id="program<?php echo $student['student_id']; ?>"
                                                                        name="program">
                                                                        <option value="" disabled>Select a program</option>
                                                                        <option value="bsme"
                                                                            <?php if (($student['program'] ?? '') == "bsme") echo "selected"; ?>>
                                                                            BS Marine Engineering</option>
                                                                        <option value="bsmt"
                                                                            <?php if (($student['program'] ?? '') == "bsmt") echo "selected"; ?>>
                                                                            BS Marine Transportation</option>
                                                                        <option value="bscje"
                                                                            <?php if (($student['program'] ?? '') == "bscje") echo "selected"; ?>>
                                                                            BS Criminal Justice Education</option>
                                                                        <option value="bstm"
                                                                            <?php if (($student['program'] ?? '') == "bstm") echo "selected"; ?>>
                                                                            BS Tourism Management</option>
                                                                        <option value="btvted"
                                                                            <?php if (($student['program'] ?? '') == "btvted") echo "selected"; ?>>
                                                                            BS Technical-Vocational Teacher Education</option>
                                                                        <option value="beced"
                                                                            <?php if (($student['program'] ?? '') == "beced") echo "selected"; ?>>
                                                                            BS Early Childhood Education</option>
                                                                        <option value="bsn"
                                                                            <?php if (($student['program'] ?? '') == "bsn") echo "selected"; ?>>
                                                                            BS Nursing</option>
                                                                        <option value="bsis"
                                                                            <?php if (($student['program'] ?? '') == "bsis") echo "selected"; ?>>
                                                                            BS Information System</option>
                                                                        <option value="bsma"
                                                                            <?php if (($student['program'] ?? '') == "bsma") echo "selected"; ?>>
                                                                            BS Management Accounting</option>
                                                                        <option value="bse"
                                                                            <?php if (($student['program'] ?? '') == "bse") echo "selected"; ?>>
                                                                            BS Entrepreneurship</option>
                                                                    </select>

                                                                    <label
                                                                        for="section<?php echo $student['student_id']; ?>">Section:</label>
                                                                    <input type="text"
                                                                        id="section<?php echo $student['student_id']; ?>"
                                                                        name="section"
                                                                        value="<?php echo htmlspecialchars($student['section'] ?? ''); ?>"
                                                                        placeholder="Section">



                                                                    <label
                                                                        for="student_id<?php echo $student['student_id']; ?>">Student
                                                                        ID:</label>
                                                                    <input type="text"
                                                                        id="student_id<?php echo $student['student_id']; ?>"
                                                                        name="student_id"
                                                                        value="<?php echo htmlspecialchars($student['student_id'] ?? ''); ?>"
                                                                        placeholder="0000-000000" maxlength="11"
                                                                        oninput="formatStudentID(this)">
                                                                </div>

                                                                <div class="section">
                                                                    <div class="section-header">Additional Information /
                                                                        Optional</div>

                                                                    <label
                                                                        for="motto<?php echo $student['student_id']; ?>">Personal
                                                                        Philosophy:</label>
                                                                    <input type="text"
                                                                        id="motto<?php echo $student['student_id']; ?>"
                                                                        name="motto"
                                                                        value="<?php echo htmlspecialchars($student['motto'] ?? ''); ?>"
                                                                        placeholder="Personal Philosophy">

                                                                    <label
                                                                        for="honors<?php echo $student['student_id']; ?>">Latin
                                                                        Awards:</label>
                                                                    <input type="text"
                                                                        id="honors<?php echo $student['student_id']; ?>"
                                                                        name="honors"
                                                                        value="<?php echo htmlspecialchars($student['honors'] ?? ''); ?>"
                                                                        placeholder="Latin Awards">

                                                                    <label
                                                                        for="milestone<?php echo $student['student_id']; ?>">Career
                                                                        Highlights:</label>
                                                                    <input type="text"
                                                                        id="milestone<?php echo $student['student_id']; ?>"
                                                                        name="milestone"
                                                                        value="<?php echo htmlspecialchars($student['milestone'] ?? ''); ?>"
                                                                        placeholder="Career Highlights">

                                                                    <div class="modal-buttons">
                                                                        <button type="button" class="modal-btn confirm"
                                                                            onclick="event.preventDefault(); event.stopPropagation(); submitStudentForm('<?php echo $student['student_id']; ?>', event)">Save</button>
                                                                        <button type="button" class="modal-btn cancel"
                                                                            onclick="closeModal('editModal_<?php echo $student['student_id']; ?>')">Cancel</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-overlay" id="delete-modal-overlay">
                <div class="modal" style="background: #34495e;">
                    <div class="modal-header">
                        <i class="fas fa-question-circle modal-icon"></i>
                        <h3>Confirm Delete</h3>
                    </div>
                    <div class="modal-content">
                        <p>Are you sure you want to delete this student?</p>
                    </div>
                    <div class="modal-buttons">
                        <button class="modal-btn confirm" id="confirm-delete-btn">
                            <i class="fas fa-check"></i> Yes, Delete
                        </button>
                        <button class="modal-btn cancel" id="cancel-delete-btn">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </div>
            </div>

            <div id="notification-container"></div>



<?php if ($outputFullHtml): ?>
            <script src="<?= $basePath ?>/Admin/assets/js/StudentList.js?v=<?php echo time(); ?>"></script>
    </body>

    </html>
<?php endif; ?>