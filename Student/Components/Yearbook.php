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
$studentProfilePhoto = $_SESSION['profile_photo'] ?? '';

$departmentCodes = [
  'BS Marine Engineering' => 'MARITIME',
  'BS Marine Transportation' => 'MARITIME',
  'BS Criminal Justice Education' => 'BSCJ',
  'BS Tourism Management' => 'BSTM',
  'BS Technical-Vocational Teacher Education' => 'COE',
  'BS Early Childhood Education' => 'COE',
  'BS Nursing' => 'BSN',
  'BS Information System' => 'BSIS',
  'BS Management Accounting' => 'BSBA',
  'BS Entrepreneurship' => 'BSBA',
  'BS Business Administration' => 'BSBA'
];

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
  <meta property="og:title" content="Yearbooks - Graduation Gallery" />
  <meta property="og:description" content="Explore digital yearbooks from Exact Colleges of Asia." />
  <meta property="og:image" content="https://ECADYB.b-cdn.net/img/PREVIEWLOGO.png" />
  <meta property="og:image:secure_url" content="https://ECADYB.b-cdn.net/img/PREVIEWLOGO.png" />
  <meta property="og:image:width" content="1200" />
  <meta property="og:image:height" content="630" />
  <meta property="og:url" content="https://grad-gallery.up.railway.app" />
  <meta property="og:type" content="website" />

  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="Yearbooks - Graduation Gallery" />
  <meta name="twitter:description" content="Explore digital yearbooks from Exact Colleges of Asia." />
  <meta name="twitter:image" content="https://ECADYB.b-cdn.net/img/PREVIEWLOGO.png" />
  <meta name="twitter:image:alt" content="Graduation Gallery Preview Logo" />

  <link rel="icon" href="https://ECADYB.b-cdn.net/img/PREVIEWLOGO.png" type="image/png" />

  <link rel="stylesheet" href="<?php echo BASE_URL; ?>Student/assets/css/StudentDashboard.css" />
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>Student/assets/css/yearbook-loader.css" />

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
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
          Click on any yearbook below to explore your department yearbook.
        </p>
      </div>

      <div class="yearbook-iframe-container" style="display: none; position: relative;">
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

        <iframe id="yearbookIframe" src="" width="100%" height="100%" style="border: none;"
          title="Digital Yearbook"></iframe>

        <div class="yearbook-lower-curl">
          <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg"
            preserveAspectRatio="none" style="display: block; width: 100%; height: 60px">
            <path d="M0,60 Q180,100 360,60 T720,60 T1080,60 T1440,60 L1440,120 L0,120 Z" fill="#1a237e"
              opacity="0.4" />
            <path d="M0,80 Q180,40 360,80 T720,80 T1080,80 T1440,80 L1440,120 L0,120 Z" fill="#112d4e"
              opacity="0.7" />
            <path d="M0,100 Q180,60 360,100 T720,100 T1080,100 T1440,100 L1440,120 L0,120 Z"
              fill="#021326" />
          </svg>
        </div>
      </div>

      <div class="yearbook-items-container">
        <ul class="yearbook-slider">
          <li class="yearbook-item"
            style="background-image: url('https://ECADYB.b-cdn.net/img/YB%20COVER/MaritimeEducation.png');"
            onclick="showYearbookIframe('BSME', 'Maritime Education')" data-department="BSME">
          </li>
          <li class="yearbook-item"
            style="background-image: url('https://ECADYB.b-cdn.net/img/YB%20COVER/TourismManagement.png');"
            onclick="showYearbookIframe('BSTM', 'Tourism Management')" data-department="BSTM">
          </li>
          <li class="yearbook-item"
            style="background-image: url('https://ECADYB.b-cdn.net/img/YB%20COVER/CriminalJusticeEducation.png');"
            onclick="showYearbookIframe('BSCJ', 'Criminal Justice Education')" data-department="BSCJ">
          </li>
          <li class="yearbook-item"
            style="background-image: url('https://ECADYB.b-cdn.net/img/YB%20COVER/InformationSystem.png');"
            onclick="showYearbookIframe('BSIS', 'Information System')" data-department="BSIS">
          </li>
          <li class="yearbook-item"
            style="background-image: url('https://ECADYB.b-cdn.net/img/YB%20COVER/Education.png');"
            onclick="showYearbookIframe('COE', 'Education')" data-department="COE">
          </li>
          <li class="yearbook-item"
            style="background-image: url('https://ECADYB.b-cdn.net/img/YB%20COVER/BusinessAdministration.png');"
            onclick="showYearbookIframe('BSBA', 'Business Administration')" data-department="BSBA">
          </li>
          <li class="yearbook-item"
            style="background-image: url('https://ECADYB.b-cdn.net/img/YB%20COVER/Nursing.png');"
            onclick="showYearbookIframe('BSN', 'Nursing')" data-department="BSN">
          </li>
        </ul>
      </div>

      <div class="yearbook-lower-curl">
        <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none"
          style="display: block; width: 100%; height: 60px">
          <path d="M0,60 Q180,100 360,60 T720,60 T1080,60 T1440,60 L1440,120 L0,120 Z" fill="#1a237e"
            opacity="0.4" />
          <path d="M0,80 Q180,40 360,80 T720,80 T1080,80 T1440,80 L1440,120 L0,120 Z" fill="#112d4e"
            opacity="0.7" />
          <path d="M0,100 Q180,60 360,100 T720,100 T1080,100 T1440,100 L1440,120 L0,120 Z" fill="#021326" />
        </svg>
      </div>
    </main>
  </section>

  <?php include __DIR__ . '/Footer.php'; ?>
  <script src="<?php echo BASE_URL; ?>Student/assets/js/StudentDashboard.js"></script>
  <script src="<?php echo BASE_URL; ?>Student/assets/js/yearbook-loader.js"></script>
  <script>
    function showYearbookIframe(departmentCode, departmentName) {
      const introContent = document.querySelector('.yearbook-intro-content');
      const iframeContainer = document.querySelector('.yearbook-iframe-container');
      const iframe = document.getElementById('yearbookIframe');
      const itemsContainer = document.querySelector('.yearbook-items-container');
      const allItems = document.querySelectorAll('.yearbook-item');
      const loader = document.querySelector('.yearbook-loader-overlay');

      console.log('[Yearbook] Opening yearbook:', departmentCode, departmentName);

      allItems.forEach(item => item.classList.remove('active'));

      const clickedItem = document.querySelector(`[data-department="${departmentCode}"]`);
      if (clickedItem) {
        clickedItem.classList.add('active');
      }

      if (introContent) {
        introContent.style.display = 'none';
      }

      if (itemsContainer) {
        itemsContainer.style.display = 'none';
      }

      if (loader) {
        loader.classList.remove('hidden');
        loader.style.display = 'flex';
        loader.style.opacity = '1';
        loader.style.visibility = 'visible';
        loader.style.pointerEvents = 'auto';
        console.log('[Yearbook] Loader shown');
      }

      if (window.YearbookLoader) {
        console.log('[Yearbook] Resetting loader manager state');

        window.YearbookLoader.isLoaded = false;
        window.YearbookLoader.magazineReady = false;
        window.YearbookLoader.coverVisible = false;
        window.YearbookLoader.navigationComplete = false;

        if (window.YearbookLoader.timeout) {
          clearTimeout(window.YearbookLoader.timeout);
          window.YearbookLoader.timeout = null;
        }
        if (window.YearbookLoader.checkInterval) {
          clearInterval(window.YearbookLoader.checkInterval);
          window.YearbookLoader.checkInterval = null;
        }

        window.YearbookLoader.loaderElement = loader;
        window.YearbookLoader.iframe = iframe;

        window.YearbookLoader.setMaxTimeout();
        window.YearbookLoader.startChecking();
      }

      if (iframe) {
        iframe.src = '';
      }

      setTimeout(() => {
        if (iframe && iframeContainer) {
          const basePath = '<?php echo BASE_URL; ?>';
          iframe.src = `${basePath}Student/Yearbook/index.html?department=${departmentCode}&fullscreen=true`;
          iframe.title = `Digital Yearbook - ${departmentName}`;
          iframeContainer.style.display = 'block';

          console.log('[Yearbook] Iframe src updated:', iframe.src);

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
      }, 100);
    }

    function closeYearbookIframe() {
      const introContent = document.querySelector('.yearbook-intro-content');
      const iframeContainer = document.querySelector('.yearbook-iframe-container');
      const iframe = document.getElementById('yearbookIframe');
      const itemsContainer = document.querySelector('.yearbook-items-container');
      const allItems = document.querySelectorAll('.yearbook-item');
      const loader = document.querySelector('.yearbook-loader-overlay');

      console.log('[Yearbook] Closing yearbook iframe');

      if (document.exitFullscreen) {
        document.exitFullscreen().catch(err => {
          console.log('Exit full screen failed:', err);
        });
      } else if (document.webkitExitFullscreen) {
        document.webkitExitFullscreen();
      } else if (document.msExitFullscreen) {
        document.msExitFullscreen();
      }

      allItems.forEach(item => item.classList.remove('active'));

      if (introContent) {
        introContent.style.display = 'block';
      }

      if (itemsContainer) {
        itemsContainer.style.display = 'flex';
      }

      if (window.YearbookLoader) {
        console.log('[Yearbook] Resetting loader for next transition');

        if (window.YearbookLoader.timeout) {
          clearTimeout(window.YearbookLoader.timeout);
          window.YearbookLoader.timeout = null;
        }
        if (window.YearbookLoader.checkInterval) {
          clearInterval(window.YearbookLoader.checkInterval);
          window.YearbookLoader.checkInterval = null;
        }

        window.YearbookLoader.isLoaded = false;
        window.YearbookLoader.magazineReady = false;
        window.YearbookLoader.coverVisible = false;
        window.YearbookLoader.navigationComplete = false;
      }

      if (loader) {
        loader.classList.add('hidden');
        setTimeout(() => {
          loader.style.display = 'none';
        }, 400);
      }

      if (iframeContainer && iframe) {
        iframeContainer.style.display = 'none';
        iframe.src = '';
      }
    }

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        const iframeContainer = document.querySelector('.yearbook-iframe-container');
        if (iframeContainer && iframeContainer.style.display === 'block') {
          closeYearbookIframe();
        }
      }
    });

    document.addEventListener('fullscreenchange', function() {
      if (!document.fullscreenElement) {
        const iframeContainer = document.querySelector('.yearbook-iframe-container');
        if (iframeContainer && iframeContainer.style.display === 'block') {
          closeYearbookIframe();
        }
      }
    });

    document.addEventListener('webkitfullscreenchange', function() {
      if (!document.webkitFullscreenElement) {
        const iframeContainer = document.querySelector('.yearbook-iframe-container');
        if (iframeContainer && iframeContainer.style.display === 'block') {
          closeYearbookIframe();
        }
      }
    });

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