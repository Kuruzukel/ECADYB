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
        <div class="yearbook-intro-content">
          <h1 class="yearbook-main-title">Digital Yearbook</h1>
          <h2 class="yearbook-subtitle">Exact Colleges of Asia</h2>
          <p class="yearbook-description">
            Click on any yearbook below to explore the department yearbook and
            its description.
          </p>
        </div>

        <div class="yearbook-detail-display" style="display: none">
          <div class="yearbook-detail-container">
            <button
              class="yearbook-detail-close-btn"
              onclick="closeYearbookView()"
            >
              <svg
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M18 6L6 18M6 6L18 18"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </button>
            <div class="yearbook-cover-large">
              <img src="" alt="Yearbook Cover" class="yearbook-cover-image" />
            </div>
            <div class="yearbook-info">
              <h1 class="yearbook-detail-title"></h1>
              <h2 class="yearbook-detail-subtitle">Exact Colleges of Asia</h2>
              <p class="yearbook-detail-description"></p>
            </div>
          </div>
        </div>
        <div class="yearbook-items-container">
          <ul class="yearbook-slider">
            <li
              class="yearbook-item"
              style="
                background-image: url('https://ECADYB.b-cdn.net/img/YB%20COVER/MaritimeEducation.png');
              "
              onclick="showYearbookBackground(this, 'https://ECADYB.b-cdn.net/img/BGGRALLERY2.0.png')"
            >
              <div class="yearbook-content">
                <button class="yearbook-btn">Explore Now</button>
              </div>
            </li>
            <li
              class="yearbook-item"
              style="
                background-image: url('https://ECADYB.b-cdn.net/img/YB%20COVER/TourismManagement.png');
              "
              onclick="showYearbookBackground(this, 'https://ECADYB.b-cdn.net/img/BGGRALLERY2.0.png')"
            >
              <div class="yearbook-content">
                <button class="yearbook-btn">View Gallery</button>
              </div>
            </li>
            <li
              class="yearbook-item"
              style="
                background-image: url('https://ECADYB.b-cdn.net/img/YB%20COVER/CriminalJusticeEducation.png');
              "
              onclick="showYearbookBackground(this, 'https://ECADYB.b-cdn.net/img/BGGRALLERY2.0.png')"
            >
              <div class="yearbook-content">
                <button class="yearbook-btn">Discover More</button>
              </div>
            </li>
            <li
              class="yearbook-item"
              style="
                background-image: url('https://ECADYB.b-cdn.net/img/YB%20COVER/InformationSystem.png');
              "
              onclick="showYearbookBackground(this, 'https://ECADYB.b-cdn.net/img/BGGRALLERY2.0.png')"
            >
              <div class="yearbook-content">
                <button class="yearbook-btn">Meet Graduates</button>
              </div>
            </li>
            <li
              class="yearbook-item"
              style="
                background-image: url('https://ECADYB.b-cdn.net/img/YB%20COVER/Education.png');
              "
              onclick="showYearbookBackground(this, 'https://ECADYB.b-cdn.net/img/BGGRALLERY2.0.png')"
            >
              <div class="yearbook-content">
                <button class="yearbook-btn">View Moments</button>
              </div>
            </li>
            <li
              class="yearbook-item"
              style="
                background-image: url('https://ECADYB.b-cdn.net/img/YB%20COVER/BusinessAdministration.png');
              "
              onclick="showYearbookBackground(this, 'https://ECADYB.b-cdn.net/img/BGGRALLERY2.0.png')"
            >
              <div class="yearbook-content">
                <button class="yearbook-btn">See Graduates</button>
              </div>
            </li>
            <li
              class="yearbook-item"
              style="
                background-image: url('https://ECADYB.b-cdn.net/img/YB%20COVER/Nursing.png');
              "
              onclick="showYearbookBackground(this, 'https://ECADYB.b-cdn.net/img/BGGRALLERY2.0.png')"
            >
              <div class="yearbook-content">
                <button class="yearbook-btn">Connect Now</button>
              </div>
            </li>
          </ul>
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

