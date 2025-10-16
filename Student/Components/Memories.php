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
$studentProfilePhoto = $_SESSION['profile_photo'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Captured Moments - Graduation Gallery</title>

    <meta property="fb:app_id" content="1767810860531321" />
    <meta property="og:locale" content="en_US" />
    <meta
      property="og:title"
      content="Captured Moments - Graduation Gallery"
    />
    <meta
      property="og:description"
      content="View captured moments from our journey together."
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
      content="Captured Moments - Graduation Gallery"
    />
    <meta
      name="twitter:description"
      content="View captured moments from our journey together."
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

    <section class="carousel-section" id="captured-moments">
      <div class="carousel-background"></div>
      <h2 class="section-title">Captured Moments</h2>
      <p class="carousel-subtitle">
        A collection of unforgettable memories from our journey together.
      </p>
      <div class="carousel-container">
        <div class="carousel-track" id="carousel-track">
          <img
            src="https://ECADYB.b-cdn.net/img/CAROUSEL/sample1.jpg"
            class="carousel-img"
          />
          <img
            src="https://ECADYB.b-cdn.net/img/CAROUSEL/sample2.jpg"
            class="carousel-img"
          />
          <img
            src="https://ECADYB.b-cdn.net/img/CAROUSEL/sample3.jpg"
            class="carousel-img"
          />
          <img
            src="https://ECADYB.b-cdn.net/img/CAROUSEL/sample4.jpg"
            class="carousel-img"
          />
          <img
            src="https://ECADYB.b-cdn.net/img/CAROUSEL/sample5.jpg"
            class="carousel-img"
          />
          <img
            src="https://ECADYB.b-cdn.net/img/CAROUSEL/sample6.jpg"
            class="carousel-img"
          />
          <img
            src="https://ECADYB.b-cdn.net/img/CAROUSEL/sample7.jpg"
            class="carousel-img"
          />
          <img
            src="https://ECADYB.b-cdn.net/img/CAROUSEL/sample8.jpg"
            class="carousel-img"
          />
          <img
            src="https://ECADYB.b-cdn.net/img/CAROUSEL/sample9.jpg"
            class="carousel-img"
          />
          <img
            src="https://ECADYB.b-cdn.net/img/CAROUSEL/sample10.jpg"
            class="carousel-img"
          />
          <img
            src="https://ECADYB.b-cdn.net/img/CAROUSEL/sample11.jpg"
            class="carousel-img"
          />
          <img
            src="https://ECADYB.b-cdn.net/img/CAROUSEL/sample12.jpg"
            class="carousel-img"
          />
          <img
            src="https://ECADYB.b-cdn.net/img/CAROUSEL/sample13.jpg"
            class="carousel-img"
          />
          <img
            src="https://ECADYB.b-cdn.net/img/CAROUSEL/sample14.jpg"
            class="carousel-img"
          />
          <img
            src="https://ECADYB.b-cdn.net/img/CAROUSEL/sample15.jpg"
            class="carousel-img"
          />
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

    <?php include __DIR__ . '/Footer.php'; ?>
    <script src="/ECADYB/Student/assets/js/StudentDashboard.js"></script>
  </body>
</html>

