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
$studentAcademicYear = $_SESSION['academic_year'] ?? '';

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

// Fetch completion date from database
$completionDateTimestamp = null;
try {
  require_once __DIR__ . '/../../Connection/Configuration/MongoConnect.php';

  // Get the yearbook covers collection
  $coversCollection = $client->ECADYB->Yearbook_Covers;

  // Format batch year - ensure it matches the database format "Batch Year YYYY-YYYY"
  $batchYear = $studentAcademicYear;
  if (!empty($batchYear) && strpos($batchYear, 'Batch Year') === false) {
    $batchYear = 'Batch Year ' . $batchYear; // Add prefix if not present
  }
  $template = 1; // Default template, adjust if you have multiple templates

  error_log("Yearbook.php: Querying for batch_year: $batchYear, template: $template");

  // Find any cover document for this batch that has a completion date
  $coverDoc = $coversCollection->findOne(
    [
      'batch_year' => $batchYear,
      'template' => $template,
      'completion_date' => ['$exists' => true]
    ],
    ['projection' => ['completion_date' => 1]]
  );

  if ($coverDoc && isset($coverDoc['completion_date'])) {
    // Convert MongoDB UTCDateTime to JavaScript timestamp (milliseconds)
    $completionDateTimestamp = $coverDoc['completion_date']->toDateTime()->getTimestamp() * 1000;
    $readableDate = $coverDoc['completion_date']->toDateTime()->format('Y-m-d H:i:s');
    error_log("Yearbook.php: Completion date found: $readableDate (timestamp: $completionDateTimestamp)");
  } else {
    error_log("Yearbook.php: No completion date found for batch_year: $batchYear");
  }
} catch (Exception $e) {
  error_log("Error fetching completion date: " . $e->getMessage());
}
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
    /* Modern PDF Badge for Yearbook Items */
    .yearbook-pdf-badge {
      position: absolute;
      top: 15px;
      right: 15px;
      width: 55px;
      height: 55px;
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4),
        0 0 0 3px rgba(255, 255, 255, 0.2),
        inset 0 2px 4px rgba(255, 255, 255, 0.2);
      z-index: 10;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      animation: pdfBadgeFloat 3s ease-in-out infinite;
    }

    .yearbook-pdf-badge::before {
      content: '';
      position: absolute;
      inset: 0;
      border-radius: 12px;
      padding: 2px;
      background: linear-gradient(135deg, rgba(252, 218, 21, 0.6), rgba(245, 158, 11, 0.6));
      -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
      -webkit-mask-composite: xor;
      mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
      mask-composite: exclude;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .yearbook-pdf-badge:hover {
      transform: translateY(-3px) scale(1.05);
      box-shadow: 0 12px 30px rgba(239, 68, 68, 0.6),
        0 0 0 4px rgba(252, 218, 21, 0.4),
        inset 0 2px 4px rgba(255, 255, 255, 0.3);
    }

    .yearbook-pdf-badge:hover::before {
      opacity: 1;
    }

    .yearbook-pdf-badge i {
      font-size: 28px;
      color: #ffffff;
      filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
      transition: all 0.3s ease;
    }

    .yearbook-pdf-badge:hover i {
      transform: scale(1.1);
      filter: drop-shadow(0 3px 6px rgba(0, 0, 0, 0.4));
    }

    @keyframes pdfBadgeFloat {

      0%,
      100% {
        transform: translateY(0px);
      }

      50% {
        transform: translateY(-5px);
      }
    }

    /* Pulse animation for PDF badge on page load */
    .yearbook-pdf-badge.pulse {
      animation: pdfBadgeFloat 3s ease-in-out infinite, pdfPulse 2s ease-in-out 3;
    }

    @keyframes pdfPulse {

      0%,
      100% {
        box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4),
          0 0 0 3px rgba(255, 255, 255, 0.2),
          inset 0 2px 4px rgba(255, 255, 255, 0.2);
      }

      50% {
        box-shadow: 0 8px 20px rgba(239, 68, 68, 0.6),
          0 0 0 6px rgba(252, 218, 21, 0.5),
          inset 0 2px 4px rgba(255, 255, 255, 0.3);
      }
    }

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
      max-width: 700px;
      max-height: 85vh;
      background: linear-gradient(145deg, #1e2a38 0%, #2c3e50 100%);
      border-radius: 16px;
      box-shadow: 0 25px 70px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(255, 255, 255, 0.1);
      padding: 0;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      border: 1px solid rgba(252, 218, 21, 0.15);
      animation: modalSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .completion-modal-header {
      background: linear-gradient(135deg, #217ff7 0%, #3b82f6 100%);
      padding: 0;
      height: 50px;
      border-bottom: 2px solid rgba(252, 218, 21, 0.3);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      flex-shrink: 0;
      letter-spacing: 0.5px;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.15);
      position: relative;
      overflow: hidden;
      border-radius: 16px 16px 0 0;
    }

    .completion-modal-header::before {
      content: "";
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: left 0.5s;
    }

    .completion-modal-header:hover::before {
      left: 100%;
    }

    .completion-modal-header .modal-icon {
      font-size: 22px;
      color: #fcda15;
      position: relative;
      z-index: 1;
    }

    .completion-modal-header h3 {
      margin: 0;
      font-family: Arial, sans-serif;
      font-size: 1.1em;
      font-weight: 600;
      color: #ffffff;
      letter-spacing: 0.5px;
      position: relative;
      z-index: 1;
    }

    .completion-modal-body {
      padding: 24px 28px;
      flex: 1;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      text-align: center;
    }

    .completion-modal-body::-webkit-scrollbar {
      width: 6px;
    }

    .completion-modal-body::-webkit-scrollbar-track {
      background: rgba(0, 0, 0, 0.2);
      border-radius: 10px;
    }

    .completion-modal-body::-webkit-scrollbar-thumb {
      background: linear-gradient(135deg, #fcda15 0%, #f59e0b 100%);
      border-radius: 10px;
    }

    .completion-modal-body p {
      margin: 0 0 16px 0;
      font-family: Arial, sans-serif;
      font-size: 15px;
      color: #e2e8f0;
      line-height: 1.6;
    }

    .completion-date {
      display: inline-block;
      background: rgba(252, 218, 21, 0.15);
      color: #fcda15;
      padding: 6px 12px;
      border-radius: 6px;
      font-weight: 600;
      font-size: 12px;
      margin: 8px 0 16px 0;
      border: 1px solid rgba(252, 218, 21, 0.3);
    }

    .completion-date i {
      margin-right: 4px;
      font-size: 11px;
    }

    /* PDF Selection Styles - Department Grid */
    .pdf-selection-container {
      margin-top: 0;
      padding-top: 0;
    }

    .pdf-selection-subtitle {
      margin: 0 0 16px 0;
      font-family: Arial, sans-serif;
      font-size: 13px;
      color: #b8c5d0;
      font-weight: 400;
      line-height: 1.4;
      text-align: center;
    }

    .dept-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 10px;
      margin: 0 0 16px 0;
    }

    .dept-label {
      display: flex;
      align-items: center;
      padding: 12px 14px;
      background: rgba(30, 42, 56, 0.6);
      border: 1.5px solid rgba(61, 90, 122, 0.4);
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
    }

    .dept-label::before {
      content: "";
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(252, 218, 21, 0.1), transparent);
      transition: left 0.5s;
    }

    .dept-label:hover::before {
      left: 100%;
    }

    .dept-label:hover {
      transform: translateX(4px);
      border-color: #fcda15;
      background: rgba(33, 127, 247, 0.15);
    }

    .dept-label.selected {
      background: linear-gradient(135deg, rgba(33, 127, 247, 0.3) 0%, rgba(30, 93, 191, 0.3) 100%);
      border-color: #fcda15;
      box-shadow: 0 0 15px rgba(252, 218, 21, 0.3), inset 0 0 20px rgba(252, 218, 21, 0.1);
    }

    .dept-checkbox {
      margin-right: 12px;
      width: 18px;
      height: 18px;
      cursor: pointer;
      accent-color: #fcda15;
      flex-shrink: 0;
    }

    .dept-info {
      display: flex;
      flex-direction: column;
      flex: 1;
    }

    .dept-name {
      color: #ffffff;
      font-size: 14px;
      font-weight: 600;
      font-family: Arial, sans-serif;
      transition: color 0.3s ease;
    }

    .dept-code {
      color: #8a98a5;
      font-size: 11px;
      font-family: Arial, sans-serif;
      margin-top: 2px;
      font-weight: 500;
    }

    .dept-checkbox:checked~.dept-info .dept-name {
      color: #fcda15;
    }

    .dept-checkbox:checked~.dept-info .dept-code {
      color: #f59e0b;
    }

    .dept-selection-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 14px 0 0 0;
      border-top: 1px solid rgba(61, 90, 122, 0.3);
      flex-shrink: 0;
    }

    .select-all-btn {
      background: linear-gradient(135deg, #217ff7 0%, #1e5dbf 100%);
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 12px;
      font-weight: 600;
      font-family: Arial, sans-serif;
      transition: all 0.3s ease;
      box-shadow: 0 3px 10px rgba(33, 127, 247, 0.3);
    }

    .select-all-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(33, 127, 247, 0.5);
      background: linear-gradient(135deg, #1e5dbf 0%, #217ff7 100%);
    }

    .select-all-btn i {
      margin-right: 6px;
    }

    .selected-count-container {
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .selected-count-icon {
      color: #fcda15;
      font-size: 14px;
    }

    .selected-count-text {
      color: #e0e7ed;
      font-size: 12px;
      font-weight: 600;
      font-family: Arial, sans-serif;
    }

    #selected-dept-count {
      color: #fcda15;
      font-weight: 700;
    }

    .dept-checkbox:checked {
      animation: checkPulse 0.3s ease;
    }

    @keyframes checkPulse {
      0% {
        transform: scale(1);
      }

      50% {
        transform: scale(1.15);
      }

      100% {
        transform: scale(1);
      }
    }

    .dept-label.selected {
      animation: labelGlow 0.5s ease;
    }

    @keyframes labelGlow {
      0% {
        box-shadow: 0 0 0 rgba(252, 218, 21, 0);
      }

      50% {
        box-shadow: 0 0 20px rgba(252, 218, 21, 0.6);
      }

      100% {
        box-shadow: 0 0 15px rgba(252, 218, 21, 0.3);
      }
    }

    .completion-modal-footer {
      padding: 16px 24px;
      display: flex;
      gap: 10px;
      justify-content: flex-end;
      border-top: 1px solid rgba(61, 90, 122, 0.3);
      background: rgba(0, 0, 0, 0.15);
      flex-shrink: 0;
    }

    .completion-modal-btn {
      padding: 10px 20px;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      font-family: Arial, sans-serif;
      transition: all 0.3s ease;
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 6px;
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      color: white;
      box-shadow: 0 3px 10px rgba(16, 185, 129, 0.4);
    }

    .completion-modal-btn i {
      font-size: 14px;
    }

    .completion-modal-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(16, 185, 129, 0.6);
      background: linear-gradient(135deg, #059669 0%, #047857 100%);
    }

    .completion-modal-btn:active {
      transform: scale(0.96);
    }

    .completion-modal-btn:disabled {
      opacity: 0.5;
      cursor: not-allowed;
      background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
      box-shadow: none;
    }

    .completion-modal-btn:disabled:hover {
      transform: none;
      box-shadow: none;
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
        <p class="yearbook-description" id="yearbook-description-text">
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
        <i class="fas fa-file-pdf modal-icon"></i>
        <h3>Yearbook PDFs Available</h3>
      </div>
      <div class="completion-modal-body">
        <!-- PDF Selection Container -->
        <div class="pdf-selection-container">
          <div class="dept-grid">
            <!-- Maritime Education -->
            <label class="dept-label">
              <input type="checkbox" class="dept-checkbox" value="MARITIME">
              <div class="dept-info">
                <span class="dept-name">Bachelor of Science in Maritime Education</span>
              </div>
            </label>

            <!-- Tourism Management -->
            <label class="dept-label">
              <input type="checkbox" class="dept-checkbox" value="BSTM">
              <div class="dept-info">
                <span class="dept-name">Bachelor of Science in Tourism Management</span>
              </div>
            </label>

            <!-- Criminal Justice -->
            <label class="dept-label">
              <input type="checkbox" class="dept-checkbox" value="BSCJ">
              <div class="dept-info">
                <span class="dept-name">Bachelor of Science in Criminal Justice Education</span>
              </div>
            </label>

            <!-- Information System -->
            <label class="dept-label">
              <input type="checkbox" class="dept-checkbox" value="BSIS">
              <div class="dept-info">
                <span class="dept-name">Bachelor of Science in Information System</span>
              </div>
            </label>

            <!-- Education -->
            <label class="dept-label">
              <input type="checkbox" class="dept-checkbox" value="COE">
              <div class="dept-info">
                <span class="dept-name">Bachelor of Science in Education</span>
              </div>
            </label>

            <!-- Business Administration -->
            <label class="dept-label">
              <input type="checkbox" class="dept-checkbox" value="BSBA">
              <div class="dept-info">
                <span class="dept-name">Bachelor of Science in Business Administration</span>
              </div>
            </label>

            <!-- Nursing -->
            <label class="dept-label">
              <input type="checkbox" class="dept-checkbox" value="BSN">
              <div class="dept-info">
                <span class="dept-name">Bachelor of Science in Nursing</span>
              </div>
            </label>
          </div>

          <div class="dept-selection-footer">
            <button class="select-all-btn" id="select-all-dept-btn">
              <i class="fas fa-check-double"></i> Select All
            </button>
            <div class="selected-count-container">
              <i class="fas fa-check-circle selected-count-icon"></i>
              <span class="selected-count-text"><span id="selected-dept-count">0</span> selected</span>
            </div>
          </div>
        </div>
      </div>
      <div class="completion-modal-footer">
        <button class="completion-modal-btn" id="download-pdf-btn" onclick="downloadSelectedYearbooks()">
          <i class="fas fa-download"></i> Download PDF
        </button>
        <button class="completion-modal-btn" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);" onclick="closeCompletionModal()">
          <i class="fas fa-times"></i> Cancel
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
    // Completion date fetched from database
    let COMPLETION_DATE;
    <?php if ($completionDateTimestamp): ?>
      COMPLETION_DATE = new Date(<?php echo $completionDateTimestamp; ?>);
      const currentTime = new Date();
      const isExpired = currentTime >= COMPLETION_DATE;
      console.log('[Yearbook] ============ YEARBOOK ACCESS STATUS ============');
      console.log('[Yearbook] Completion date:', COMPLETION_DATE.toLocaleString());
      console.log('[Yearbook] Current time:', currentTime.toLocaleString());
      console.log('[Yearbook] Status:', isExpired ? '🔒 EXPIRED - Only completion modal will show' : '✅ ACTIVE - Yearbook loading enabled');
      if (!isExpired) {
        const timeLeft = COMPLETION_DATE - currentTime;
        const hoursLeft = Math.floor(timeLeft / (1000 * 60 * 60));
        const minutesLeft = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
        console.log('[Yearbook] Time until expiry:', hoursLeft + 'h ' + minutesLeft + 'm');
      }
      console.log('[Yearbook] ============================================');
    <?php else: ?>
      COMPLETION_DATE = null;
      console.log('[Yearbook] ⚠ No completion date set - yearbook access is open indefinitely');
      console.log('[Yearbook] Student academic year:', '<?php echo $studentAcademicYear; ?>');
      console.log('[Yearbook] Searched for batch_year:', '<?php echo isset($batchYear) ? $batchYear : "N/A"; ?>');
    <?php endif; ?>

    function isYearbookCompleted() {
      if (!COMPLETION_DATE) {
        return false; // No completion date means access is still open
      }
      const currentDate = new Date();
      const isCompleted = currentDate >= COMPLETION_DATE;
      if (isCompleted) {
        console.log('[Yearbook] Access completed on:', COMPLETION_DATE.toLocaleString());
      }
      return isCompleted;
    }

    function showCompletionModal() {
      const modal = document.getElementById('yearbook-completion-modal');
      if (modal) {
        modal.classList.add('show');
        console.log('[Yearbook] Showing PDF selection modal');
      }
    }

    function closeCompletionModal() {
      const modal = document.getElementById('yearbook-completion-modal');
      if (modal) {
        modal.classList.remove('show');
      }
    }

    function getSelectedDepartments() {
      const checkboxes = document.querySelectorAll('.dept-checkbox:checked');
      return Array.from(checkboxes).map(cb => cb.value);
    }

    function updateDepartmentCount() {
      const count = document.querySelectorAll('.dept-checkbox:checked').length;
      const countElement = document.getElementById('selected-dept-count');
      const downloadBtn = document.getElementById('download-pdf-btn');

      if (countElement) {
        countElement.textContent = count;
      }

      // Enable/disable download button based on selection
      if (downloadBtn) {
        if (count === 0) {
          downloadBtn.disabled = true;
          downloadBtn.style.opacity = '0.5';
          downloadBtn.style.cursor = 'not-allowed';
        } else {
          downloadBtn.disabled = false;
          downloadBtn.style.opacity = '1';
          downloadBtn.style.cursor = 'pointer';
        }
      }
    }

    function initializeDepartmentSelection() {
      const checkboxes = document.querySelectorAll('.dept-checkbox');
      const selectAllBtn = document.getElementById('select-all-dept-btn');

      checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
          const label = this.closest('.dept-label');
          if (this.checked) {
            label.classList.add('selected');
          } else {
            label.classList.remove('selected');
          }
          updateDepartmentCount();
          updateSelectAllButton();
        });
      });

      if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
          const allChecked = Array.from(checkboxes).every(cb => cb.checked);

          checkboxes.forEach(checkbox => {
            checkbox.checked = !allChecked;
            const label = checkbox.closest('.dept-label');
            if (checkbox.checked) {
              label.classList.add('selected');
            } else {
              label.classList.remove('selected');
            }
          });

          updateDepartmentCount();
          updateSelectAllButton();
        });
      }

      function updateSelectAllButton() {
        if (!selectAllBtn) return;

        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        const icon = selectAllBtn.querySelector('i');
        const btnText = selectAllBtn.childNodes[selectAllBtn.childNodes.length - 1];

        if (allChecked) {
          if (icon) icon.className = 'fas fa-times-circle';
          btnText.textContent = ' Deselect All';
        } else {
          if (icon) icon.className = 'fas fa-check-double';
          btnText.textContent = ' Select All';
        }
      }

      updateDepartmentCount();
      updateSelectAllButton();
    }

    function downloadSelectedYearbooks() {
      const selectedDepartments = getSelectedDepartments();

      if (selectedDepartments.length === 0) {
        alert('Please select at least one department');
        return;
      }

      // Get student's academic year
      const studentAcademicYear = window.studentData?.studentAcademicYear || '';
      const batchYear = studentAcademicYear.includes('Batch Year') ? studentAcademicYear : 'Batch Year ' + studentAcademicYear;

      console.log('[Yearbook PDF] Downloading:', selectedDepartments.length, 'departments');
      console.log('[Yearbook PDF] Student batch year:', batchYear);
      console.log('[Yearbook PDF] Departments:', selectedDepartments);

      // Download each selected department
      selectedDepartments.forEach((departmentCode, index) => {
        setTimeout(() => {
          const pdfUrl = `<?php echo BASE_URL; ?>Connection/Photos/DownloadYearbookPDF.php?department=${departmentCode}&batch_year=${encodeURIComponent(batchYear)}`;
          window.open(pdfUrl, '_blank');
          console.log('[Yearbook PDF] Download initiated:', departmentCode, pdfUrl);
        }, index * 500); // Stagger downloads by 500ms to avoid blocking
      });

      // Close modal and reset selections after a delay
      setTimeout(() => {
        closeCompletionModal();

        // Reset selections
        const checkboxes = document.querySelectorAll('.dept-checkbox');
        checkboxes.forEach(cb => {
          cb.checked = false;
          cb.closest('.dept-label').classList.remove('selected');
        });
        updateDepartmentCount();
      }, selectedDepartments.length * 500 + 500);
    }

    function showYearbookIframe(departmentCode, departmentName) {
      console.log('[Yearbook] 🔍 Clicked on yearbook:', departmentCode, departmentName);
      console.log('[Yearbook] 🔍 COMPLETION_DATE:', COMPLETION_DATE);
      console.log('[Yearbook] 🔍 Current time:', new Date());

      // Check if yearbook access has been completed
      const completed = isYearbookCompleted();
      console.log('[Yearbook] 🔍 isYearbookCompleted():', completed);

      if (completed) {
        console.log('[Yearbook] 🚫 Access expired - ONLY showing completion modal (no loading/iframe)');
        showCompletionModal();
        return; // Exit immediately - no loading, no iframe
      } else {
        console.log('[Yearbook] ✅ Access still open - loading yearbook');
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

      // Initialize department selection
      initializeDepartmentSelection();

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
      // Only show PDF mode if completion date exists AND is expired
      if (COMPLETION_DATE && isYearbookCompleted()) {
        console.log('[Yearbook] 📄 Access period has ended - PDF downloads available');

        // Update description text for expired yearbooks
        const descriptionText = document.getElementById('yearbook-description-text');
        if (descriptionText) {
          descriptionText.innerHTML = 'The digital yearbook viewing period has ended. Click on any yearbook below to download the PDF.';
        }

        // Add modern PDF badge to all yearbook items
        const yearBookItems = document.querySelectorAll('.yearbook-item');
        yearBookItems.forEach((item) => {
          const pdfBadge = document.createElement('div');
          pdfBadge.className = 'yearbook-pdf-badge pulse';
          pdfBadge.innerHTML = '<i class="fas fa-file-pdf"></i>';
          item.style.position = 'relative';
          item.appendChild(pdfBadge);

          // Remove pulse animation after 6 seconds
          setTimeout(() => {
            pdfBadge.classList.remove('pulse');
          }, 6000);
        });

        // Modal will only show when user clicks on a yearbook item
        console.log('[Yearbook] 📄 PDF badges added. Modal will show when yearbook is clicked.');
      }
    });
  </script>
</body>

</html>