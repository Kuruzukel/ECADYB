<?php
session_start();
require __DIR__ . '/../../config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    // Redirect to login page if not logged in or not a student
    header('Location: ' . BASE_URL . 'Public/Components/Login.php');
    exit();
}

// Get student information from session
$studentId = $_SESSION['student_id'] ?? '';
$studentName = $_SESSION['name'] ?? '';
$studentDepartment = $_SESSION['department'] ?? '';
$studentSection = $_SESSION['section'] ?? '';
$studentProfilePhoto = $_SESSION['profile_photo'] ?? '';

// Map department full names to department codes for yearbook URL
$departmentCodes = [
    'BS Marine Engineering' => 'BSME',
    'BS Marine Transportation' => 'BSMT',
    'BS Criminal Justice Education' => 'BSCJE',
    'BS Tourism Management' => 'BSTM',
    'BS Technical-Vocational Teacher Education' => 'BTVTED',
    'BS Early Childhood Education' => 'BSECED',
    'BS Nursing' => 'BSN',
    'BS Information System' => 'BSIS',
    'BS Management Accounting' => 'BSMA',
    'BS Entrepreneurship' => 'BSE',
    'BS Business Administration' => 'BSBA'
];

// Get the department code for the yearbook URL
$departmentCode = $departmentCodes[$studentDepartment] ?? 'BSME';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Yearbooks - Graduation Gallery</title>

    <meta property="fb:app_id" content="1767810860531321" />
    <meta property="og:locale" content="en_US" />
    <meta
      property="og:title"
      content="Yearbooks - Graduation Gallery"
    />
    <meta
      property="og:description"
      content="Explore digital yearbooks from Exact Colleges of Asia."
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
      content="Yearbooks - Graduation Gallery"
    />
    <meta
      name="twitter:description"
      content="Explore digital yearbooks from Exact Colleges of Asia."
    />
    <meta
      name="twitter:image"
      content="https://ECADYB.b-cdn.net/img/PREVIEWLOGO.png"
    />
    <meta
      name="twitter:image:alt"
      content="Graduation Gallery Preview Logo"
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
  </head>

  <body>
    <?php include __DIR__ . '/Header.php'; ?>

    <section class="yearbooks-section" id="yearbooks">
      <div class="yearbooks-background"></div>

      <main class="yearbook-slider-main">
        <div class="yearbook-iframe-container">
          <iframe 
            src="/ECADYB/Admin/Yearbook/index.html?department=<?php echo htmlspecialchars($departmentCode); ?>" 
            width="100%" 
            height="100%"
            style="border: none;"
            title="Digital Yearbook - <?php echo htmlspecialchars($studentDepartment); ?>"
          ></iframe>
        </div>

        <div class="yearbook-lower-curl">
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
      </main>
    </section>

    <?php include __DIR__ . '/Footer.php'; ?>
    <script src="/ECADYB/Student/assets/js/StudentDashboard.js"></script>
  </body>
</html>

