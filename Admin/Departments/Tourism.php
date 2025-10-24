<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>College of Tourism Management</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link href="<?= $basePath ?>/Admin/Flipbook/turn.js/dist/style.css" rel="stylesheet">
  <link href="<?= $basePath ?>/Admin/Departments/assets/css/Tourism.css" rel="stylesheet">
</head>

<body>
  <?php
  if (!defined('ADMIN_DASHBOARD_INCLUDED')) {
    header('Location: ../');
    exit;
  }
  ?>
  <div class="container">
    <div class="catalog-root">
      <div class="catalog-app">
        <?php
        // Get student parameters from URL if present
        $studentId = isset($_GET['student_id']) ? htmlspecialchars($_GET['student_id']) : '';
        $studentName = isset($_GET['student_name']) ? htmlspecialchars($_GET['student_name']) : '';
        
        // Build iframe URL with student parameters
        $iframeUrl = $basePath . '/Admin/Yearbook/index.html?department=BSTM';
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
    <script src="<?= $basePath ?>/Admin/Departments/assets/js/Tourism.js"></script>
  </div>
</body>

</html>