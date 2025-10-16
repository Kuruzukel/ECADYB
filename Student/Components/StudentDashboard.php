<?php
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    // Redirect to login page if not logged in or not a student
    header('Location: /ECADYB/Public/Components/Login.php');
    exit();
}

// Get student information from session
$studentId = $_SESSION['student_id'] ?? '';
$studentName = $_SESSION['name'] ?? '';
$studentDepartment = $_SESSION['department'] ?? '';
$studentSection = $_SESSION['section'] ?? '';

// Fetch additional student details from MongoDB
require __DIR__ . '/../../vendor/autoload.php';
use MongoDB\Client;

$studentAcademicYear = '';
$studentProgram = '';
$studentStatus = '';
$studentProfilePhoto = '';

try {
    $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
    $client = new Client($mongoUrl);
    
    // Collections mapping
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
    
    // Determine which database and collection to use
    $dbName = $_SESSION['batch_template'] ?? "BatchTemplate1"; // Use batch template from session
    $collectionName = $collections[$studentDepartment] ?? 'bsme';
    
    $db = $client->$dbName;
    $collection = $db->$collectionName;
    
    // Find the student by student ID
    $student = $collection->findOne([
        '$or' => [
            ['student id' => $studentId],
            ['student_id' => $studentId]
        ]
    ]);
    
    if ($student) {
        $studentAcademicYear = $student['academic year'] ?? '';
        $studentProgram = $student['program'] ?? '';
        $studentStatus = $student['status'] ?? 'Pending';
        
        // Fetch student photo from StudentPhotos collection in the same database
        $studentPhotosCollection = $db->StudentPhotos;
        
        // Find the student photo by student ID
        $studentPhoto = $studentPhotosCollection->findOne([
            'student_id' => $studentId
        ]);
        
        // Get the uniform photo URL (or toga/filipiniana as fallback)
        if ($studentPhoto && isset($studentPhoto['uniform_url'])) {
            $studentProfilePhoto = $studentPhoto['uniform_url'];
        } elseif ($studentPhoto && isset($studentPhoto['toga_url'])) {
            $studentProfilePhoto = $studentPhoto['toga_url'];
        } elseif ($studentPhoto && isset($studentPhoto['filipiniana_url'])) {
            $studentProfilePhoto = $studentPhoto['filipiniana_url'];
        }
        
        // Update session with additional info
        $_SESSION['academic_year'] = $studentAcademicYear;
        $_SESSION['program'] = $studentProgram;
        $_SESSION['status'] = $studentStatus;
        $_SESSION['profile_photo'] = $studentProfilePhoto;
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
    <meta
      property="og:title"
      content="Student Dashboard - Graduation Gallery"
    />
    <meta
      property="og:description"
      content="Step into your digital yearbook. Every achievement and memory comes alive."
    />
    <meta
      property="og:image"
      content="https://ECADYB.b-cdn.net/img/PREVIEWLOGO.png"
    />
    <meta
      property="og:image:secure_url"
      content="https://ECADYB.b-cdn.net/img/PREVIEWLOGO.png"
    />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:url" content="https://grad-gallery.up.railway.app" />
    <meta property="og:type" content="website" />

    <meta name="twitter:card" content="summary_large_image" />
    <meta
      name="twitter:title"
      content="Graduation Gallery - Student Dashboard"
    />
    <meta
      name="twitter:description"
      content="Step into your digital yearbook. Every achievement and memory comes alive."
    />
    <meta
      name="twitter:image"
      content="https://ECADYB.b-cdn.net/img/PREVIEWLOGO.png"
    />
    <meta
      name="twitter:image:alt"
      content="Student Dashboard Graduation Gallery Preview Logo"
    />

    <link
      rel="icon"
      href="https://ECADYB.b-cdn.net/img/PREVIEWLOGO.png"
      type="image/png"
    />

    <link rel="stylesheet" href="/ECADYB/Student/assets/css/StudentDashboard.css" />

    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
    />
    
    <script>
      // Pass student information to JavaScript
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
    </script>
  </head>

  <body>
    <?php include __DIR__ . '/Header.php'; ?>

    <section class="main-hero" id="main-hero">
      <div class="main_blur_overlay"></div>
      <div class="main-hero-background"></div>
      <div class="main-hero-text">
        <div class="logo-container">
          <img
            src="https://ECADYB.b-cdn.net/img/GRALLERYLOGO4.0.png"
            alt="Logo"
            class="logo-img"
          />
        </div>
        <div class="hero-message">
          <div>
            Welcome, <strong><?php echo htmlspecialchars($studentName); ?></strong>!
          </div>
          <div style="font-size: 0.9em; margin-top: 0.5em; opacity: 0.9;">
            <i class="fas fa-id-card"></i> Student ID: <?php echo htmlspecialchars($studentId); ?> | 
            <i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($studentDepartment); ?> | 
            <i class="fas fa-calendar"></i> <?php echo htmlspecialchars($studentAcademicYear); ?>
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
        <svg
          viewBox="0 0 1440 120"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
          preserveAspectRatio="none"
          style="display: block; width: 100%; height: 60px"
        >
          <path
            d="M0,60 Q180,100 360,60 T720,60 T1080,60 T1440,60 L1440,120 L0,120 Z"
            fill="#1a237e"
            opacity="0.4"
          />
          <path
            d="M0,80 Q180,40 360,80 T720,80 T1080,80 T1440,80 L1440,120 L0,120 Z"
            fill="#112d4e"
            opacity="0.7"
          />
          <path
            d="M0,100 Q180,60 360,100 T720,100 T1080,100 T1440,100 L1440,120 L0,120 Z"
            fill="#021326"
          />
        </svg>
      </div>
    </section>

    <footer class="footer-section" id="footer">
      <div class="footer-inner-container">
        <div class="footer-logo-container left">
          <img
            src="https://ECADYB.b-cdn.net/img/ECALOGO.png"
            alt="ECA Logo"
            class="footer-logo-img footer-logo-img-left"
          />
        </div>
        <div class="footer-content">
          <div class="footer-contact-info">
            <div class="footer-contact-item">
              <div class="footer-contact-desc">
                <i
                  class="fa-solid fa-location-dot"
                  style="margin-right: 8px; color: #bfcfff"
                ></i>
                Suclayin Arayat, Pampanga, Arayat, Philippines
              </div>
            </div>
            <div class="footer-contact-item">
              <div class="footer-contact-desc">
                <i
                  class="fa-solid fa-phone"
                  style="margin-right: 8px; color: #bfcfff"
                ></i>
                0969 516 6181
              </div>
            </div>
            <div class="footer-contact-item">
              <div class="footer-contact-desc">
                <i
                  class="fa-solid fa-envelope"
                  style="margin-right: 8px; color: #bfcfff"
                ></i>
                exact.colleges@yahoo.com
              </div>
            </div>
            <div class="footer-contact-item">
              <div class="footer-contact-desc">
                <i
                  class="fa-brands fa-facebook"
                  style="margin-right: 8px; color: #bfcfff"
                ></i>
                <a
                  href="https://www.facebook.com/ExactCollegesAsia"
                  target="_blank"
                  style="color: inherit; text-decoration: none"
                  >facebook.com/ExactCollegesAsia</a
                >
              </div>
            </div>
          </div>
          <div class="footer-copy">&copy; 2025 TEAM NOVA SPIRE.</div>
        </div>
        <div class="footer-logo-container right">
          <img
            src="https://ECADYB.b-cdn.net/img/GRALLERYLOGO4.0.png"
            alt="Grallery Logo"
            class="footer-logo-img footer-logo-img-right"
          />
        </div>
      </div>
    </footer>
    <script src="/ECADYB/Student/assets/js/StudentDashboard.js"></script>
  </body>
</html>
