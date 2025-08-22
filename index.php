<?php
session_start();

// ----------------------
// Composer autoload
// ----------------------
require __DIR__ . '/vendor/autoload.php';

use MongoDB\Client;

// ----------------------
// MongoDB Connection
// ----------------------
$mongoPath = __DIR__ . '/Connection/MongoConnect.php';
if (!file_exists($mongoPath)) {
    die("❌ MongoConnect.php not found at: $mongoPath");
}
require $mongoPath; // provides $client, $departmentsDB, $collections, $adminCollection

// ----------------------
// Base paths
// ----------------------
define('BASE_PATH', __DIR__);
define('BASE_URL', '/'); // Root path for Railway deployment

// ----------------------
// Handle login POST
// ----------------------
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['studentId'], $_POST['password'])) {
    $studentId = trim($_POST['studentId']);
    $password  = trim($_POST['password']);

    if (strlen($password) > 8) {
        $error_message = "Password must not exceed 8 characters.";
    } else {
        try {
            // Admin login
            $admin = $adminCollection->findOne([
                'username' => $studentId,
                'password' => $password
            ]);

            if ($admin) {
                $_SESSION['role'] = 'admin';
                $_SESSION['username'] = $studentId;
                header("Location: " . BASE_URL . "Admin/Components/AdminDashboard.php");
                exit();
            }

            // Student login
            $foundStudent = false;

            foreach ($collections as $collectionName => $departmentName) {
                $collection = $departmentsDB->{$collectionName};

                $student = $collection->findOne([
                    'student id' => $studentId,
                    'password'   => $password
                ]);

                if ($student) {
                    $_SESSION['role'] = 'student';
                    $_SESSION['student_id'] = $student['student id'];
                    $_SESSION['name'] = $student['first name'] . ' ' . $student['middle name'] . ' ' . $student['last name'];
                    $_SESSION['department'] = $departmentName;
                    $_SESSION['section'] = $student['department section'] ?? '';

                    $foundStudent = true;

                    header("Location: " . BASE_URL . "Student/Components/StudentDashboard.php");
                    exit();
                }
            }

            if (!$foundStudent) {
                $error_message = "Invalid Student ID or password!";
            }

        } catch (Exception $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}

// Display login error
if (!empty($error_message)) {
    echo "<div style='color:red; font-weight:bold; margin:10px 0;'>$error_message</div>";
}

// ----------------------
// Router / Landing Page
// ----------------------
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Redirect old login path
if ($requestUri === '/Public/Components/Login.php') {
    header("Location: " . BASE_URL . "Public/Components/Login.php");
    exit;
}

// Serve PHP files directly
$phpFile = BASE_PATH . $requestUri;
if (file_exists($phpFile) && pathinfo($phpFile, PATHINFO_EXTENSION) === 'php') {
    include $phpFile;
    exit;
}

// Serve Landing Page
if ($requestUri === '/' || $requestUri === '/index.php') {
    $landingPagePath = BASE_PATH . '/LandingPage/LandingPage.html';
    if (file_exists($landingPagePath)) {
        $htmlContent = file_get_contents($landingPagePath);

        // Fix asset paths for Railway deployment
        $htmlContent = str_replace(
            [
                'href="LandingPage.css"',
                'src="LandingPage.js"',
                'src="../img/',
                'src="LandingPageYB/'
            ],
            [
                'href="' . BASE_URL . 'LandingPage/LandingPage.css"',
                'src="' . BASE_URL . 'LandingPage/LandingPage.js"',
                'src="' . BASE_URL . 'img/',
                'src="' . BASE_URL . 'LandingPage/LandingPageYB/'
            ],
            $htmlContent
        );

        // Fix login buttons
        $htmlContent = str_replace(
            ['id="loginDropdownBtn"', 'id="mobileLoginDropdownBtn"'],
            [
                'id="loginDropdownBtn" onclick="window.location.href=\'' . BASE_URL . 'Public/Components/Login.php\'"',
                'id="mobileLoginDropdownBtn" onclick="window.location.href=\'' . BASE_URL . 'Public/Components/Login.php\'"'
            ],
            $htmlContent
        );

        echo $htmlContent;
        exit;
    } else {
        http_response_code(404);
        echo "Landing page not found";
        exit;
    }
}

// Serve static assets
$staticPaths = [
    '/img/' => '/img/',
    '/LandingPage/LandingPageYB/pages/' => '/LandingPage/LandingPageYB/pages/',
    '/Public/assets/css/' => '/Public/assets/css/',
    '/Public/assets/js/' => '/Public/assets/js/'
];

foreach ($staticPaths as $uriPrefix => $folder) {
    if (str_starts_with($requestUri, $uriPrefix)) {
        $filePath = BASE_PATH . $folder . substr($requestUri, strlen($uriPrefix));
        if (file_exists($filePath)) {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimeTypes = [
                'jpg'  => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png'  => 'image/png',
                'gif'  => 'image/gif',
                'webp' => 'image/webp',
                'css'  => 'text/css',
                'js'   => 'application/javascript'
            ];
            header('Content-Type: ' . ($mimeTypes[$ext] ?? 'application/octet-stream'));
            readfile($filePath);
            exit;
        }
    }
}

// Default fallback
http_response_code(404);
echo '<h1>ECADYB Application</h1>';
echo '<p>Page not found.</p>';
exit;