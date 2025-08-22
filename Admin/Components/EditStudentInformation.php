<?php
require __DIR__ . '/../../vendor/autoload.php'; 

use MongoDB\Client;

$student = null;
$collection = null;

if (isset($_GET['student_id']) && isset($_GET['collection'])) {
    $studentId = $_GET['student_id'];
    $collectionName = $_GET['collection'];
    
    try {
        // Get MongoDB connection string from environment variable
    $mongoUrl = getenv('MONGO_URL') ?: 'mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957';
    $client = new Client($mongoUrl);
        $db = $client->Departments;
        $collection = $db->$collectionName;
        
        $student = $collection->findOne(['student id' => $studentId]);
    } catch (Exception $e) {
        $error = "Error fetching student: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student Information</title>
    <link rel="stylesheet" href="../Assets/css/EditStudentInformation.css">
</head>

<body>
    <div class="container">
        <div class="header-container">
            <h1>Edit Student Information</h1>
        </div>

        <div class="content">
            <a href="studentlist.php" class="back-btn">← Back to Student List</a>

            <?php if (isset($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php elseif ($student): ?>
            <div class="student-info">
                <h2>Student Information</h2>
                <div class="info-row">
                    <span class="info-label">Student ID:</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['student id'] ?? ''); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span class="info-value">
                        <?php echo htmlspecialchars(($student['first name'] ?? '') . ' ' . ($student['middle name'] ?? '') . ' ' . ($student['last name'] ?? '')); ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Department:</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['program'] ?? ''); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Section:</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['section'] ?? ''); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Academic Year:</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['academic year'] ?? ''); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['email'] ?? ''); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Motto:</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['motto'] ?? ''); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Honors:</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['honors'] ?? ''); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Milestone:</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['milestone'] ?? ''); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Batch Name:</span>
                    <span class="info-value"><?php echo htmlspecialchars($student['batch name'] ?? ''); ?></span>
                </div>
            </div>

            <p style="margin-top: 20px; color: #b0b0b0;">
                <em>Edit functionality will be implemented here. For now, you can view the student information.</em>
            </p>
            <?php else: ?>
            <div class="error">Student not found.</div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>