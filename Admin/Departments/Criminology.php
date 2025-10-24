<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>College of Criminal Justice Education</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link href="<?= $basePath ?>/Admin/Flipbook/turn.js/dist/style.css" rel="stylesheet">
  <link href="<?= $basePath ?>/Admin/Departments/assets/css/Criminology.css" rel="stylesheet">
  <link href="<?= $basePath ?>/Admin/Departments/assets/css/yearbook-loader.css" rel="stylesheet">
</head>

<body>
  <?php
  if (!defined('ADMIN_DASHBOARD_INCLUDED')) {
    header('Location: ../');
    exit;
  }
  ?>
  <div class="container">
    <div class="catalog-root" draggable="false">
      <div class="catalog-app" draggable="false">
        <!-- Simple Loader -->
        <div class="yearbook-loader-overlay" draggable="false">
          <div class="loader-content" draggable="false">
            <div class="spinner" draggable="false">
              <div class="spinner-dot" draggable="false"></div>
              <div class="spinner-dot" draggable="false"></div>
              <div class="spinner-dot" draggable="false"></div>
              <div class="spinner-dot" draggable="false"></div>
              <div class="spinner-dot" draggable="false"></div>
              <div class="spinner-dot" draggable="false"></div>
              <div class="spinner-dot" draggable="false"></div>
              <div class="spinner-dot" draggable="false"></div>
            </div>
            <div class="loader-text" draggable="false">Loading Yearbook...</div>
          </div>
        </div>
        
        <?php
        // Get student parameters from URL if present
        $studentId = isset($_GET['student_id']) ? htmlspecialchars($_GET['student_id']) : '';
        $studentName = isset($_GET['student_name']) ? htmlspecialchars($_GET['student_name']) : '';
        
        // Build iframe URL with student parameters
        $iframeUrl = $basePath . '/Admin/Yearbook/index.html?department=BSCJ';
        if ($studentId && $studentName) {
            $iframeUrl .= '&student_id=' . urlencode($studentId) . '&student_name=' . urlencode($studentName);
        }
        ?>
        <iframe id="yearbook-iframe" src="<?= $iframeUrl ?>" width="100%" height="100%"
          style="border: none; min-height: 670px;"></iframe>
      </div>
    </div>

    <script src="https://code.jquery.com/jquery-2.0.3.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.9.1/underscore-min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/backbone.js/1.4.0/backbone-min.js"></script>
    <script src="<?= $basePath ?>/Admin/Departments/assets/js/yearbook-loader.js"></script>
    <script src="<?= $basePath ?>/Admin/Departments/assets/js/Criminology.js"></script>
  </div>
</body>

</html>