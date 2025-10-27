<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../Connection/Configuration/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Location: ' . BASE_URL . 'Login');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Debug</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            margin-bottom: 20px;
        }

        .session-info {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            border-left: 4px solid #3b82f6;
        }

        .session-key {
            font-weight: bold;
            color: #2563eb;
            display: inline-block;
            width: 200px;
        }

        .session-value {
            color: #333;
            font-family: 'Courier New', monospace;
        }

        .empty {
            color: #ef4444;
            font-style: italic;
        }

        .warning {
            background: #fef3c7;
            border-left-color: #f59e0b;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .back-btn:hover {
            background: #2563eb;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Session Debug Information</h1>

        <div class="session-info">
            <span class="session-key">Student ID:</span>
            <span class="session-value <?php echo empty($_SESSION['student_id']) ? 'empty' : ''; ?>">
                <?php echo !empty($_SESSION['student_id']) ? htmlspecialchars($_SESSION['student_id']) : '(EMPTY OR NOT SET)'; ?>
            </span>
        </div>

        <div class="session-info">
            <span class="session-key">Name:</span>
            <span class="session-value <?php echo empty($_SESSION['name']) ? 'empty' : ''; ?>">
                <?php echo !empty($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : '(EMPTY OR NOT SET)'; ?>
            </span>
        </div>

        <div class="session-info">
            <span class="session-key">Department:</span>
            <span class="session-value <?php echo empty($_SESSION['department']) ? 'empty' : ''; ?>">
                <?php echo !empty($_SESSION['department']) ? htmlspecialchars($_SESSION['department']) : '(EMPTY OR NOT SET)'; ?>
            </span>
        </div>

        <div class="session-info">
            <span class="session-key">Section:</span>
            <span class="session-value <?php echo empty($_SESSION['section']) ? 'empty' : ''; ?>">
                <?php echo !empty($_SESSION['section']) ? htmlspecialchars($_SESSION['section']) : '(EMPTY OR NOT SET)'; ?>
            </span>
        </div>

        <div class="session-info">
            <span class="session-key">Academic Year:</span>
            <span class="session-value <?php echo empty($_SESSION['academic_year']) ? 'empty' : ''; ?>">
                <?php echo !empty($_SESSION['academic_year']) ? htmlspecialchars($_SESSION['academic_year']) : '(EMPTY OR NOT SET)'; ?>
            </span>
        </div>

        <div class="session-info">
            <span class="session-key">Batch Template:</span>
            <span class="session-value <?php echo empty($_SESSION['batch_template']) ? 'empty' : ''; ?>">
                <?php echo !empty($_SESSION['batch_template']) ? htmlspecialchars($_SESSION['batch_template']) : '(EMPTY OR NOT SET)'; ?>
            </span>
        </div>

        <div class="session-info">
            <span class="session-key">Email:</span>
            <span class="session-value <?php echo empty($_SESSION['email']) ? 'empty' : ''; ?>">
                <?php echo !empty($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : '(EMPTY OR NOT SET)'; ?>
            </span>
        </div>

        <div class="session-info">
            <span class="session-key">Role:</span>
            <span class="session-value">
                <?php echo htmlspecialchars($_SESSION['role'] ?? '(NOT SET)'); ?>
            </span>
        </div>

        <?php if (empty($_SESSION['student_id']) || $_SESSION['student_id'] === '0000-000000'): ?>
            <div class="warning">
                <strong>⚠️ Issue Detected:</strong> Your student ID is empty or invalid. This will cause errors when trying to update your profile.
                <br><br>
                <strong>Solution:</strong> Please log out and log in again. If the problem persists, contact your administrator.
            </div>
        <?php endif; ?>

        <h2 style="margin-top: 30px;">All Session Data (Raw)</h2>
        <pre style="background: #f9f9f9; padding: 15px; border-radius: 5px; overflow-x: auto;"><?php print_r($_SESSION); ?></pre>

        <a href="<?php echo BASE_URL; ?>Student/Components/StudentDashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>
</body>

</html>