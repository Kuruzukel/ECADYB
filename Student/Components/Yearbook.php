<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../Connection/Configuration/config.php';

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
    <link rel="stylesheet" href="/ECADYB/Student/assets/css/yearbook-loader.css" />

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
        <!-- Intro Content -->
        <div class="yearbook-intro-content">
          <h1 class="yearbook-main-title">Digital Yearbook</h1>
          <h2 class="yearbook-subtitle">Exact Colleges of Asia</h2>
          <p class="yearbook-description">
            Click on any yearbook below to explore your department yearbook.
          </p>
        </div>

        <!-- Iframe Container (hidden by default) -->
        <div class="yearbook-iframe-container" style="display: none; position: relative;">
          <!-- Yearbook Loader Overlay -->
          <div class="yearbook-loader-overlay">
            <div class="loader-content">
              <div class="spinner">
                <div class="spinner-dot"></div>
                <div class="spinner-dot"></div>
                <div class="spinner-dot"></div>
                <div class="spinner-dot"></div>
                <div class="spinner-dot"></div>
                <div class="spinner-dot"></div>
                <div class="spinner-dot"></div>
                <div class="spinner-dot"></div>
              </div>
              <div class="loader-text">Loading Yearbook...</div>
            </div>
          </div>

          <iframe 
            id="yearbookIframe"
            src="" 
            width="100%" 
            height="100%"
            style="border: none;"
            title="Digital Yearbook"
          ></iframe>
          
          <!-- Bottom Curl for Fullscreen -->
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
        </div>

        <!-- Yearbook Items Container -->
        <div class="yearbook-items-container">
          <ul class="yearbook-slider">
            <li class="yearbook-item" 
                style="background-image: url('https://ECADYB.b-cdn.net/img/YB%20COVER/MaritimeEducation.png');"
                onclick="showYearbookIframe('BSME', 'Maritime Education')"
                data-department="BSME">
            </li>
            <li class="yearbook-item" 
                style="background-image: url('https://ECADYB.b-cdn.net/img/YB%20COVER/TourismManagement.png');"
                onclick="showYearbookIframe('BSTM', 'Tourism Management')"
                data-department="BSTM">
            </li>
            <li class="yearbook-item" 
                style="background-image: url('https://ECADYB.b-cdn.net/img/YB%20COVER/CriminalJusticeEducation.png');"
                onclick="showYearbookIframe('BSCJE', 'Criminal Justice Education')"
                data-department="BSCJE">
            </li>
            <li class="yearbook-item" 
                style="background-image: url('https://ECADYB.b-cdn.net/img/YB%20COVER/InformationSystem.png');"
                onclick="showYearbookIframe('BSIS', 'Information System')"
                data-department="BSIS">
            </li>
            <li class="yearbook-item" 
                style="background-image: url('https://ECADYB.b-cdn.net/img/YB%20COVER/Education.png');"
                onclick="showYearbookIframe('BTVTED', 'Education')"
                data-department="BTVTED">
            </li>
            <li class="yearbook-item" 
                style="background-image: url('https://ECADYB.b-cdn.net/img/YB%20COVER/BusinessAdministration.png');"
                onclick="showYearbookIframe('BSBA', 'Business Administration')"
                data-department="BSBA">
            </li>
            <li class="yearbook-item" 
                style="background-image: url('https://ECADYB.b-cdn.net/img/YB%20COVER/Nursing.png');"
                onclick="showYearbookIframe('BSN', 'Nursing')"
                data-department="BSN">
            </li>
          </ul>
        </div>

        <!-- Bottom Curl -->
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
    <script src="/ECADYB/Student/assets/js/yearbook-loader.js"></script>
    <script>
      // Function to show yearbook iframe when clicking on a yearbook item
      function showYearbookIframe(departmentCode, departmentName) {
        const introContent = document.querySelector('.yearbook-intro-content');
        const iframeContainer = document.querySelector('.yearbook-iframe-container');
        const iframe = document.getElementById('yearbookIframe');
        const itemsContainer = document.querySelector('.yearbook-items-container');
        const allItems = document.querySelectorAll('.yearbook-item');
        const loader = document.querySelector('.yearbook-loader-overlay');

        // Remove active class from all items
        allItems.forEach(item => item.classList.remove('active'));
        
        // Add active class to clicked item
        const clickedItem = document.querySelector(`[data-department="${departmentCode}"]`);
        if (clickedItem) {
          clickedItem.classList.add('active');
        }

        // Hide intro content
        if (introContent) {
          introContent.style.display = 'none';
        }

        // Hide yearbook slider
        if (itemsContainer) {
          itemsContainer.style.display = 'none';
        }

        // Show loader and reset its state
        if (loader) {
          loader.classList.remove('hidden');
          loader.style.display = 'flex';
          loader.style.opacity = '1';
          loader.style.visibility = 'visible';
        }

        // Reset loader manager state if it exists
        if (window.YearbookLoader) {
          window.YearbookLoader.isLoaded = false;
          window.YearbookLoader.magazineReady = false;
          window.YearbookLoader.coverVisible = false;
          window.YearbookLoader.navigationComplete = false;
          
          // Clear any existing timers
          if (window.YearbookLoader.timeout) {
            clearTimeout(window.YearbookLoader.timeout);
          }
          if (window.YearbookLoader.checkInterval) {
            clearInterval(window.YearbookLoader.checkInterval);
          }
          
          // Restart loading process
          window.YearbookLoader.loaderElement = loader;
          window.YearbookLoader.iframe = iframe;
          window.YearbookLoader.setMaxTimeout();
          window.YearbookLoader.startChecking();
        }

        // Update iframe src and show it
        if (iframe && iframeContainer) {
          // Add fullscreen=true parameter to trigger fullscreen styles in the iframe
          iframe.src = `/ECADYB/Student/Yearbook/index.html?department=${departmentCode}&fullscreen=true`;
          iframe.title = `Digital Yearbook - ${departmentName}`;
          iframeContainer.style.display = 'block';
          
          // Request full screen mode
          if (iframeContainer.requestFullscreen) {
            iframeContainer.requestFullscreen().catch(err => {
              console.log('Full screen request failed:', err);
            });
          } else if (iframeContainer.webkitRequestFullscreen) {
            iframeContainer.webkitRequestFullscreen();
          } else if (iframeContainer.msRequestFullscreen) {
            iframeContainer.msRequestFullscreen();
          }
        }
      }

      // Function to close yearbook iframe
      function closeYearbookIframe() {
        const introContent = document.querySelector('.yearbook-intro-content');
        const iframeContainer = document.querySelector('.yearbook-iframe-container');
        const iframe = document.getElementById('yearbookIframe');
        const itemsContainer = document.querySelector('.yearbook-items-container');
        const allItems = document.querySelectorAll('.yearbook-item');

        // Exit full screen mode
        if (document.exitFullscreen) {
          document.exitFullscreen().catch(err => {
            console.log('Exit full screen failed:', err);
          });
        } else if (document.webkitExitFullscreen) {
          document.webkitExitFullscreen();
        } else if (document.msExitFullscreen) {
          document.msExitFullscreen();
        }

        // Remove active class from all items
        allItems.forEach(item => item.classList.remove('active'));

        // Show intro content
        if (introContent) {
          introContent.style.display = 'block';
        }

        // Show yearbook slider
        if (itemsContainer) {
          itemsContainer.style.display = 'flex';
        }

        // Hide and reset iframe
        if (iframeContainer && iframe) {
          iframeContainer.style.display = 'none';
          iframe.src = '';
        }
      }

      // Close iframe with Escape key
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
          const iframeContainer = document.querySelector('.yearbook-iframe-container');
          if (iframeContainer && iframeContainer.style.display === 'block') {
            closeYearbookIframe();
          }
        }
      });

      // Listen for full screen change events
      document.addEventListener('fullscreenchange', function() {
        if (!document.fullscreenElement) {
          // User exited full screen, close the iframe
          const iframeContainer = document.querySelector('.yearbook-iframe-container');
          if (iframeContainer && iframeContainer.style.display === 'block') {
            closeYearbookIframe();
          }
        }
      });

      // Listen for webkit full screen change events (Safari)
      document.addEventListener('webkitfullscreenchange', function() {
        if (!document.webkitFullscreenElement) {
          // User exited full screen, close the iframe
          const iframeContainer = document.querySelector('.yearbook-iframe-container');
          if (iframeContainer && iframeContainer.style.display === 'block') {
            closeYearbookIframe();
          }
        }
      });

      // Initialize yearbook items on page load
      document.addEventListener('DOMContentLoaded', function() {
        const yearBookItems = document.querySelectorAll('.yearbook-item');
        yearBookItems.forEach((item) => {
          if (item) {
            item.style.transform = '';
            item.style.willChange = 'transform';
            item.style.backfaceVisibility = 'hidden';
          }
        });
      });
    </script>
  </body>
</html>

