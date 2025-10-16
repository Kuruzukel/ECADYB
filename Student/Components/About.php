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
    <title>About - Graduation Gallery</title>

    <meta property="fb:app_id" content="1767810860531321" />
    <meta property="og:locale" content="en_US" />
    <meta
      property="og:title"
      content="About - Graduation Gallery"
    />
    <meta
      property="og:description"
      content="Learn about our Digital Yearbook: Graduation Gallery."
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
      content="About - Graduation Gallery"
    />
    <meta
      name="twitter:description"
      content="Learn about our Digital Yearbook: Graduation Gallery."
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

    <section class="about-hero-section" id="about">
      <div class="main_blur_overlay"></div>
      <div class="about-hero-background"></div>
      <div class="about-hero-content">
        <div class="about-content-flex">
          <div>
            <img
              src="https://ECADYB.b-cdn.net/img/ABOUTIMG.png"
              class="about-image"
              alt="Yearbook Preview"
            />
          </div>
          <div>
            <h2 class="section-title">About</h2>
            <p class="about-description">
              Welcome to our Digital Yearbook: Graduation Gallery, a space created
              to celebrate the incredible journey of our graduates. This virtual
              gallery captures memories, milestones, and meaningful moments from
              the academic year. A tribute to the resilience, growth, and
              achievements of every student. Whether you're looking back at your
              own story or exploring the journey of classmates, this digital
              yearbook is a living reminder that while graduation marks the end of
              a chapter, the memories last forever.
            </p>
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

