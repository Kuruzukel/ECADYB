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

  <style>
    /* Completion Modal Styles */
    .yearbook-completion-modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      backdrop-filter: blur(2px);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 9999;
      animation: fadeIn 0.3s ease;
    }

    .yearbook-completion-modal-overlay.show {
      display: flex;
    }

    .completion-modal-container {
      width: 90%;
      max-width: 500px;
      background: linear-gradient(145deg, #1e2a38 0%, #2c3e50 100%);
      border-radius: 20px;
      box-shadow: 0 25px 70px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(255, 255, 255, 0.1);
      padding: 0;
      overflow: hidden;
      border: 2px solid rgba(252, 218, 21, 0.2);
      animation: modalSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .completion-modal-header {
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      padding: 24px;
      text-align: center;
      border-bottom: 2px solid rgba(252, 218, 21, 0.3);
    }

    .completion-modal-header .modal-icon {
      font-size: 48px;
      color: #fcda15;
      margin-bottom: 12px;
      display: block;
      animation: pulse 2s infinite;
    }

    .completion-modal-header h3 {
      margin: 0;
      font-family: Arial, sans-serif;
      font-size: 1.5em;
      font-weight: 700;
      color: #ffffff;
      letter-spacing: 0.5px;
    }

    .completion-modal-body {
      padding: 32px 28px;
      text-align: center;
    }

    .completion-modal-body p {
      margin: 0 0 20px 0;
      font-family: Arial, sans-serif;
      font-size: 16px;
      color: #e2e8f0;
      line-height: 1.6;
    }

    .completion-date {
      display: inline-block;
      background: rgba(252, 218, 21, 0.15);
      color: #fcda15;
      padding: 8px 16px;
      border-radius: 8px;
      font-weight: 600;
      margin: 16px 0;
      border: 1px solid rgba(252, 218, 21, 0.3);
    }

    .completion-modal-footer {
      padding: 20px 28px;
      display: flex;
      justify-content: center;
      border-top: 1px solid rgba(61, 90, 122, 0.3);
      background: rgba(0, 0, 0, 0.15);
    }

    .completion-modal-btn {
      padding: 12px 32px;
      border-radius: 10px;
      font-size: 15px;
      font-weight: 600;
      font-family: Arial, sans-serif;
      transition: all 0.3s ease;
      border: none;
      cursor: pointer;
      background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
      color: white;
      box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    }

    .completion-modal-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(59, 130, 246, 0.6);
      background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    }

    .completion-modal-btn:active {
      transform: scale(0.96);
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
      }

      to {
        opacity: 1;
      }
    }

    @keyframes modalSlideIn {
      from {
        opacity: 0;
        transform: scale(0.9) translateY(-20px);
      }

      to {
        opacity: 1;
        transform: scale(1) translateY(0);
      }
    }

    @keyframes pulse {

      0%,
      100% {
        transform: scale(1);
        opacity: 1;
      }

      50% {
        transform: scale(1.1);
        opacity: 0.8;
      }
    }
  </style>
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

  <!-- Yearbook Completion Modal -->
  <div class="yearbook-completion-modal-overlay" id="yearbook-completion-modal">
    <div class="completion-modal-container">
      <div class="completion-modal-header">
        <i class="fas fa-lock modal-icon"></i>
        <h3>Yearbook Access Completed</h3>
      </div>
      <div class="completion-modal-body">
        <p>The digital yearbook viewing period has ended.</p>
        <p>Thank you for being part of our journey!</p>
        <div class="completion-date">
          <i class="fas fa-calendar-check"></i>
          Completed: October 21, 2028
        </div>
        <p style="font-size: 14px; color: #94a3b8; margin-top: 20px;">
          For access to archived yearbooks, please contact the administration.
        </p>
      </div>
      <div class="completion-modal-footer">
        <button class="completion-modal-btn" onclick="closeCompletionModal()">
          <i class="fas fa-check"></i> Understood
        </button>
      </div>
    </div>
  </div>

  <script>
    // Initialize student data from PHP session
    window.studentData = {
      studentId: <?php echo json_encode($studentId); ?>,
      studentName: <?php echo json_encode($studentName); ?>,
      studentDepartment: <?php echo json_encode($studentDepartment); ?>,
      studentSection: <?php echo json_encode($studentSection); ?>,
      studentAcademicYear: <?php echo $_SESSION['academic_year'] ? json_encode($_SESSION['academic_year']) : '""'; ?>,
      studentProfilePhoto: <?php echo json_encode($studentProfilePhoto); ?>
    };
    console.log('Student data initialized:', window.studentData);
  </script>
  <script src="<?php echo BASE_URL; ?>Student/assets/js/SessionTracker.js"></script>
  <script src="<?php echo BASE_URL; ?>Student/assets/js/StudentDashboard.js"></script>
  <script src="<?php echo BASE_URL; ?>Student/assets/js/yearbook-loader.js"></script>
  <script>
    // Completion date check
    const COMPLETION_DATE = new Date('2028-10-21T16:04:06.000Z');

    function isYearbookCompleted() {
      const currentDate = new Date();
      return currentDate >= COMPLETION_DATE;
    }

    function showCompletionModal() {
      const modal = document.getElementById('yearbook-completion-modal');
      if (modal) {
        modal.classList.add('show');
        console.log('[Yearbook] Showing completion modal - access period ended');
      }
    }

    function closeCompletionModal() {
      const modal = document.getElementById('yearbook-completion-modal');
      if (modal) {
        modal.classList.remove('show');
      }
    }

    function showYearbookIframe(departmentCode, departmentName) {
      // Check if yearbook access has been completed
      if (isYearbookCompleted()) {
        console.log('[Yearbook] Access completed - showing completion modal');
        showCompletionModal();
        return;
      }

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
        // Check if completion modal is open
        const completionModal = document.getElementById('yearbook-completion-modal');
        if (completionModal && completionModal.classList.contains('show')) {
          closeCompletionModal();
          return;
        }

        // Otherwise check if yearbook iframe is open
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

      // Close completion modal when clicking outside
      const completionModal = document.getElementById('yearbook-completion-modal');
      if (completionModal) {
        completionModal.addEventListener('click', function(e) {
          if (e.target === completionModal) {
            closeCompletionModal();
          }
        });
      }

      // Check if yearbook is completed on page load
      if (isYearbookCompleted()) {
        console.log('[Yearbook] Access period has ended - yearbooks are locked');
        // Add lock badge to all yearbook items using notification badge styles
        const yearBookItems = document.querySelectorAll('.yearbook-item');
        yearBookItems.forEach((item) => {
          const lockBadge = document.createElement('span');
          lockBadge.className = 'notification-badge yearbook-lock-badge';
          lockBadge.innerHTML = '<i class="fas fa-lock"></i>';
          lockBadge.style.cssText = `
            width: 35px;
            height: 35px;
            font-size: 16px;
            top: 10px;
            right: 10px;
          `;
          item.style.position = 'relative';
          item.appendChild(lockBadge);
        });
      }
    });
  </script>
</body>

</html>