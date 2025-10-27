<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

$studentId = $_SESSION['student_id'] ?? '';
$studentName = $_SESSION['name'] ?? '';
$studentFirstName = $_SESSION['first_name'] ?? '';
$studentMiddleName = $_SESSION['middle_name'] ?? '';
$studentLastName = $_SESSION['last_name'] ?? '';
$studentEmail = $_SESSION['email'] ?? '';
$studentDepartment = $_SESSION['department'] ?? '';
$studentSection = $_SESSION['section'] ?? '';
$studentAcademicYear = $_SESSION['academic_year'] ?? '';
$studentProgram = $_SESSION['program'] ?? '';
$studentMotto = $_SESSION['motto'] ?? '';
$studentHonors = $_SESSION['honors'] ?? '';
$studentMilestone = $_SESSION['milestone'] ?? '';
$studentProfilePhoto = $_SESSION['profile_photo'] ?? '';

if (empty($studentEmail) || empty($studentFirstName)) {

  try {
    require_once __DIR__ . '/../../Connection/Configuration/EnvLoader.php';
    $mongoUrl = getMongoUrl();
    $client = new Client($mongoUrl);

    $collections = [
      "BS Marine Engineering" => "bsme",
      "BS Marine Transportation" => "bsmt",
      "BS Criminal Justice Education" => "bscje",
      "BS Tourism Management" => "bstm",
      "BS Technical-Vocational Teacher Education" => "btvted",
      "BS Early Childhood Education" => "beced",
      "BS Nursing" => "bsn",
      "BS Information System" => "bsis",
      "BS Management Accounting" => "bsma",
      "BS Entrepreneurship" => "bse"
    ];

    $dbName = $_SESSION['batch_template'] ?? "ECADYB";
    $collectionName = $collections[$studentDepartment] ?? 'bsme';

    $db = $client->$dbName;
    $collection = $db->$collectionName;

    $student = $collection->findOne([
      '$or' => [
        ['student id' => $studentId],
        ['student_id' => $studentId]
      ]
    ]);

    if ($student) {
      $studentFirstName = $_SESSION['first_name'] = $student['first name'] ?? '';
      $studentMiddleName = $_SESSION['middle_name'] = $student['middle name'] ?? '';
      $studentLastName = $_SESSION['last_name'] = $student['last name'] ?? '';
      $studentEmail = $_SESSION['email'] = $student['email'] ?? '';
      $studentMotto = $_SESSION['motto'] = $student['motto'] ?? '';
      $studentHonors = $_SESSION['honors'] = $student['honors'] ?? '';
      $studentMilestone = $_SESSION['milestone'] = $student['milestone'] ?? '';
      $studentAcademicYear = $_SESSION['academic_year'] = $student['academic year'] ?? '';
      $studentProgram = $_SESSION['program'] = $student['program'] ?? '';
    }
  } catch (Exception $e) {
    error_log("Error fetching student data in Header.php: " . $e->getMessage());
  }
}

if (empty($studentFirstName) && empty($studentLastName) && !empty($studentName)) {
  $nameParts = explode(' ', trim($studentName));
  if (count($nameParts) >= 3) {
    $studentFirstName = $nameParts[0];
    $studentMiddleName = $nameParts[1];
    $studentLastName = implode(' ', array_slice($nameParts, 2));
  } elseif (count($nameParts) == 2) {
    $studentFirstName = $nameParts[0];
    $studentLastName = $nameParts[1];
  } elseif (count($nameParts) == 1) {
    $studentFirstName = $nameParts[0];
  }
}
?>
<header>
  <div class="logo">
    <img src="https://ECADYB.b-cdn.net/img/ADMINGRALLERYLOGO.png" alt="Logo" class="logo-img" />
    <span>GRADUATION GALLERY</span>
  </div>
  <nav class="center-nav">
    <a href="/ECADYB/Student/Components/StudentDashboard.php">Home</a>
    <a href="/ECADYB/Student/Components/About.php">About</a>
    <a href="/ECADYB/Student/Components/Yearbook.php">Yearbooks</a>
    <a href="/ECADYB/Student/Components/Memories.php">Memories</a>
    <div class="mobile-login-dropdown">
      <button class="mobile-login-btn" id="mobileLoginDropdownBtn" onclick="window.location.href='/ECADYB/login'">
        Log In
      </button>
    </div>
  </nav>
  <div class="notification-icon-container">
    <i class="fa-solid fa-bell notification-icon" id="notificationIcon"></i>
    <span class="notification-badge" id="notificationBadge">0</span>
    <div class="notification-dropdown" id="notificationDropdown">
      <div class="notification-header">
        <h3>Notifications</h3>
        <button class="mark-all-read" onclick="markAllAsRead()">
          Mark all as read
        </button>
      </div>
      <div class="notification-list" id="notificationList">
        <div class="notification-loading">
          <i class="fa-solid fa-spinner fa-spin"></i>
          <span>Loading announcements...</span>
        </div>
      </div>
    </div>
  </div>

  <div class="profile-dropdown">
    <img src="<?php echo htmlspecialchars($studentProfilePhoto ?: 'https://ECADYB.b-cdn.net/img/Profile.png'); ?>"
      alt="Profile" class="profile-icon" id="profileIcon" />
    <div class="dropdown-menu" id="profileDropdownMenu">
      <button class="dropdown-item"
        onclick="window.location.href='/ECADYB/Student/ChangePassword'">
        Change Password
      </button>
      <button class="dropdown-item" onclick="editProfile()">
        Edit Profile
      </button>
      <button class="dropdown-item" onclick="logout()">Log Out</button>
    </div>
  </div>

  <button class="hamburger-menu" id="hamburgerMenu">
    <svg class="hamburger-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path class="hamburger-line line-1" d="M3 6h18" stroke="currentColor" stroke-width="2"
        stroke-linecap="round" />
      <path class="hamburger-line line-2" d="M3 12h18" stroke="currentColor" stroke-width="2"
        stroke-linecap="round" />
      <path class="hamburger-line line-3" d="M3 18h18" stroke="currentColor" stroke-width="2"
        stroke-linecap="round" />
      <path class="close-line close-1" d="M18 6L6 18" stroke="currentColor" stroke-width="2"
        stroke-linecap="round" />
      <path class="close-line close-2" d="M6 6L18 18" stroke="currentColor" stroke-width="2"
        stroke-linecap="round" />
    </svg>
  </button>
</header>

<div id="notification-container"></div>

<div id="editStudentModal" class="editStudentModal">
  <div class="modal-content">
    <div class="modal-header">
      <i class="fas fa-user-edit modal-icon"></i>
      <h3>Edit Profile</h3>
    </div>

    <div class="modal-body">
      <form id="edit-student-form" onsubmit="return false;">
        <input type="hidden" id="student-id-hidden" name="student_id"
          value="<?php echo htmlspecialchars($studentId); ?>">

        <div class="form-group">
          <div class="section">
            <div class="section-header">Personal Information</div>

            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" autocomplete="given-name"
              value="<?php echo htmlspecialchars($studentFirstName); ?>" placeholder="First Name"
              readonly>

            <label for="middle_name">Middle Name:</label>
            <input type="text" id="middle_name" name="middle_name" autocomplete="additional-name"
              value="<?php echo htmlspecialchars($studentMiddleName); ?>" placeholder="Middle Name"
              readonly>

            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" autocomplete="family-name"
              value="<?php echo htmlspecialchars($studentLastName); ?>" placeholder="Last Name" readonly>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" autocomplete="email"
              value="<?php echo htmlspecialchars($studentEmail); ?>" oninput="removeSpaces(this)"
              placeholder="Email">
          </div>

          <div class="section">
            <div class="section-header">Academic Information</div>

            <label for="academic_year">Academic Year:</label>
            <input type="text" id="academic_year" name="academic_year" autocomplete="off"
              value="<?php echo htmlspecialchars($studentAcademicYear); ?>" placeholder="0000-0000"
              maxlength="9" oninput="formatAcademicYear(this)" readonly>

            <label for="program">Program:</label>
            <input type="text" id="program" name="program" autocomplete="off"
              value="<?php echo htmlspecialchars($studentDepartment); ?>" placeholder="Program" readonly>

            <label for="section">Section:</label>
            <input type="text" id="section" name="section" autocomplete="off"
              value="<?php echo htmlspecialchars($studentSection); ?>" placeholder="Section" readonly>

            <label for="student_id_display">Student ID:</label>
            <input type="text" id="student_id_display" name="student_id_display" autocomplete="off"
              value="<?php echo htmlspecialchars($studentId); ?>" placeholder="0000-000000" maxlength="11"
              readonly>
          </div>

          <div class="section">
            <div class="section-header">Additional Information</div>

            <label for="motto">Personal Philosophy:</label>
            <textarea id="motto" name="motto" autocomplete="off" rows="3"
              placeholder="Personal Philosophy"><?php echo htmlspecialchars($studentMotto); ?></textarea>

            <label for="honors">Latin Awards:</label>
            <input type="text" id="honors" name="honors" autocomplete="off"
              value="<?php echo htmlspecialchars($studentHonors); ?>" placeholder="Latin Awards" readonly>

            <label for="milestone">Career Highlights:</label>
            <textarea id="milestone" name="milestone" autocomplete="off" rows="3"
              placeholder="Career Highlights"><?php echo htmlspecialchars($studentMilestone); ?></textarea>

            <div class="modal-buttons">
              <button type="button" class="modal-btn confirm" onclick="submitStudentInfo(event)">
                <i class="fas fa-save"></i> Save Changes
              </button>
              <button type="button" class="modal-btn cancel" onclick="closeEditModal()">
                <i class="fas fa-times"></i> Cancel
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>