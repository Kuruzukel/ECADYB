<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../../Connection/Configuration/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
  header('Location: ' . BASE_URL . 'Login');
  exit();
}

$studentId = $_SESSION['student_id'] ?? '';
$studentName = $_SESSION['name'] ?? '';
$studentDepartment = $_SESSION['department'] ?? '';
$studentSection = $_SESSION['section'] ?? '';

require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

$studentAcademicYear = '';
$studentProgram = '';
$studentStatus = '';
$studentProfilePhoto = '';

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

  if (!$student) {
    foreach ($collections as $fullName => $collName) {
      if ($collName === $collectionName) continue;

      $altCollection = $db->$collName;
      $student = $altCollection->findOne([
        '$or' => [
          ['student id' => $studentId],
          ['student_id' => $studentId]
        ]
      ]);

      if ($student) {
        $collectionName = $collName;
        $collection = $altCollection;
        break;
      }
    }
  }

  if ($student) {
    $studentAcademicYear = $student['academic year'] ?? '';
    $studentProgram = $student['program'] ?? '';
    $studentStatus = $student['status'] ?? 'Pending';

    $studentPhotosCollection = $db->Student_Photos;

    $studentPhoto = $studentPhotosCollection->findOne([
      'student_id' => $studentId
    ]);

    $isMaritime = in_array($studentDepartment, ['BS Marine Engineering', 'BS Marine Transportation']);

    if ($isMaritime && $studentPhoto && isset($studentPhoto['dwhite_url'])) {
      $studentProfilePhoto = $studentPhoto['dwhite_url'];
    } elseif ($studentPhoto && isset($studentPhoto['uniform_url'])) {
      $studentProfilePhoto = $studentPhoto['uniform_url'];
    } elseif ($studentPhoto && isset($studentPhoto['toga_url'])) {
      $studentProfilePhoto = $studentPhoto['toga_url'];
    } elseif ($studentPhoto && isset($studentPhoto['filipiniana_url'])) {
      $studentProfilePhoto = $studentPhoto['filipiniana_url'];
    }

    $_SESSION['academic_year'] = $studentAcademicYear;
    $_SESSION['program'] = $studentProgram;
    $_SESSION['status'] = $studentStatus;
    $_SESSION['profile_photo'] = $studentProfilePhoto;
    $_SESSION['first_name'] = $student['first name'] ?? '';
    $_SESSION['middle_name'] = $student['middle name'] ?? '';
    $_SESSION['last_name'] = $student['last name'] ?? '';
    $_SESSION['email'] = $student['email'] ?? '';
    $_SESSION['motto'] = $student['motto'] ?? '';
    $_SESSION['honors'] = $student['honors'] ?? '';
    $_SESSION['milestone'] = $student['milestone'] ?? '';
    $_SESSION['collection'] = $collectionName;
    $_SESSION['batch_template'] = $dbName;
  } else {
    error_log("Student not found with ID: " . $studentId . " in database: " . $dbName . " collection: " . $collectionName);
  }
} catch (Exception $e) {
  error_log("Error fetching student data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Student Dashboard - Graduation Gallery</title>

  <meta property="fb:app_id" content="1767810860531321" />
  <meta property="og:locale" content="en_US" />
  <meta property="og:title" content="Student Dashboard - Graduation Gallery" />
  <meta property="og:description"
    content="Step into your digital yearbook. Every achievement and memory comes alive." />
  <meta property="og:image" content="https://ECADYB.b-cdn.net/img/PREVIEWLOGO.png" />
  <meta property="og:image:secure_url" content="https://ECADYB.b-cdn.net/img/PREVIEWLOGO.png" />
  <meta property="og:image:width" content="1200" />
  <meta property="og:image:height" content="630" />
  <meta property="og:url" content="https://grad-gallery.up.railway.app" />
  <meta property="og:type" content="website" />

  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="Graduation Gallery - Student Dashboard" />
  <meta name="twitter:description"
    content="Step into your digital yearbook. Every achievement and memory comes alive." />
  <meta name="twitter:image" content="https://ECADYB.b-cdn.net/img/PREVIEWLOGO.png" />
  <meta name="twitter:image:alt" content="Student Dashboard Graduation Gallery Preview Logo" />

  <link rel="icon" href="https://ECADYB.b-cdn.net/img/PREVIEWLOGO.png" type="image/png" />

  <link rel="stylesheet" href="<?php echo BASE_URL; ?>Student/assets/css/StudentDashboard.css" />

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />

  <script>
    window.studentData = {
      studentId: <?php echo json_encode($studentId); ?>,
      studentName: <?php echo json_encode($studentName); ?>,
      studentDepartment: <?php echo json_encode($studentDepartment); ?>,
      studentSection: <?php echo json_encode($studentSection); ?>,
      studentAcademicYear: <?php echo json_encode($studentAcademicYear); ?>,
      studentProgram: <?php echo json_encode($studentProgram); ?>,
      studentStatus: <?php echo json_encode($studentStatus); ?>
    };

    console.log('Student Data Loaded:', window.studentData);
    console.log('Database:', '<?php echo $dbName ?? "ECADYB"; ?>');
    console.log('Collection:', '<?php echo $collectionName ?? "bsn"; ?>');
    console.log('Student Found:', <?php echo $student ? 'true' : 'false'; ?>);
  </script>
</head>

<body>
  <?php include __DIR__ . '/Header.php'; ?>

  <section class="main-hero" id="main-hero">
    <div class="main_blur_overlay"></div>
    <div class="main-hero-background"></div>
    <div class="main-hero-text">
      <div class="logo-container">
        <img src="https://ECADYB.b-cdn.net/img/GRALLERYLOGO4.0.png" alt="Logo" class="logo-img" />
      </div>
      <div class="hero-message">
        <div>
          Welcome, <strong><?php echo htmlspecialchars($studentName ?: 'Student'); ?></strong>!
        </div>
        <div class="student-info-container" style="font-size: 0.9em; margin-top: 0.5em; opacity: 0.9;">
          <!-- Default inline format for larger screens -->
          <div class="student-info-inline">
            <i class="fas fa-id-card"></i> Student ID: <?php echo htmlspecialchars($studentId ?: 'N/A'); ?> |
            <i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($studentDepartment ?: 'N/A'); ?> |
            <i class="fas fa-calendar"></i> <?php echo htmlspecialchars($studentAcademicYear ?: 'N/A'); ?>
          </div>
          <!-- Separate divs for mobile screens only -->
          <div class="student-info-mobile">
            <div class="student-info-item">
              <i class="fas fa-id-card"></i> Student ID: <?php echo htmlspecialchars($studentId ?: 'N/A'); ?>
            </div>
            <div class="student-info-item">
              <i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($studentDepartment ?: 'N/A'); ?>
            </div>
            <div class="student-info-item">
              <i class="fas fa-calendar"></i> <?php echo htmlspecialchars($studentAcademicYear ?: 'N/A'); ?>
            </div>
          </div>
        </div>
        <div style="margin-top: 1em;">
          Step into your digital yearbook. Every achievement and memory comes
          alive.
        </div>
        <div class="hero-message-bold">
          Explore memories, connect the present, inspire the future.
        </div>
      </div>
    </div>
    <div class="main-hero-lower-curl">
      <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none"
        style="display: block; width: 100%; height: 60px">
        <path d="M0,60 Q180,100 360,60 T720,60 T1080,60 T1440,60 L1440,120 L0,120 Z" fill="#1a237e"
          opacity="0.4" />
        <path d="M0,80 Q180,40 360,80 T720,80 T1080,80 T1440,80 L1440,120 L0,120 Z" fill="#112d4e"
          opacity="0.7" />
        <path d="M0,100 Q180,60 360,100 T720,100 T1080,100 T1440,100 L1440,120 L0,120 Z" fill="#021326" />
      </svg>
    </div>
  </section>

  <?php include __DIR__ . '/Footer.php'; ?>
  <script>
    window.studentData = {
      studentId: <?php echo json_encode($studentId); ?>,
      studentName: <?php echo json_encode($studentName); ?>,
      studentDepartment: <?php echo json_encode($studentDepartment); ?>,
      studentSection: <?php echo json_encode($studentSection); ?>,
      studentAcademicYear: <?php echo json_encode($studentAcademicYear); ?>,
      studentProgram: <?php echo json_encode($studentProgram); ?>,
      studentProfilePhoto: <?php echo json_encode($studentProfilePhoto); ?>
    };
    console.log('Student data initialized:', window.studentData);
  </script>
  <script src="<?php echo BASE_URL; ?>Student/assets/js/SessionTracker.js"></script>
  <script src="<?php echo BASE_URL; ?>Student/assets/js/StudentDashboard.js"></script>
</body>

</html>