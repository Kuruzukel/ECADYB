<?php
session_start();

// Include Composer autoload
require __DIR__ . '/vendor/autoload.php'; 

// Include MongoDB connections
$mongoPath = __DIR__ . '/Connection/MongoConnect.php';
if (!file_exists($mongoPath)) {
    die("MongoConnect.php not found at: $mongoPath");
}
require $mongoPath; // This file should define $client, $departmentsDB, $collections, etc.

// ----------------------
// Base paths
// ----------------------
define('BASE_PATH', __DIR__); // Filesystem path to project root
define('BASE_URL', '/');      // Base URL (adjust if project is in a subfolder)

// ----------------------
// Handle login POST
// ----------------------
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['studentId'], $_POST['password'])) {
    $username = trim($_POST['studentId']);
    $password = trim($_POST['password']);

    if (strlen($password) > 8) {
        $error_message = "Password must not exceed 8 characters.";
    } else {
        // Admin login
        $admin = $adminCollection->findOne(['username'=>$username,'password'=>$password]);

        if ($admin) {
            $_SESSION['role'] = 'admin';
            $_SESSION['username'] = $username;
            header("Location: " . BASE_URL . "Admin/Components/AdminDashboard.php");
            exit();
        }

        // Student login
        foreach ($collections as $collectionName => $course) {
            $collection = $departmentsDB->{$collectionName};
            $student = $collection->findOne(['student_id'=>$username,'password'=>$password]);

            if ($student) {
                $_SESSION['role']       = 'student';
                $_SESSION['student_id'] = $student['student_id'];
                $_SESSION['name']       = $student['name'];
                $_SESSION['department'] = $course;
                header("Location: " . BASE_URL . "Student/Components/StudentDashboard.php");
                exit();
            }
        }

        $error_message = "Invalid username or password!";
    }
}

// ----------------------
// Router / Landing Page
// ----------------------
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve PHP files directly
$phpFile = BASE_PATH . $requestUri;
if (file_exists($phpFile) && pathinfo($phpFile, PATHINFO_EXTENSION) === 'php') {
    include $phpFile;
    exit;
}

// Serve landing page
if ($requestUri === '/' || $requestUri === '/index.php') {
    $landingPagePath = BASE_PATH . '/LandingPage/LandingPage.html';
    if (file_exists($landingPagePath)) {
        $htmlContent = file_get_contents($landingPagePath);

        // Fix asset paths
        $htmlContent = str_replace(
            ['href="LandingPage.css"', 'src="LandingPage.js"', 'src="../img/', 'src="LandingPageYB/'],
            ['href="'.BASE_URL.'LandingPage/LandingPage.css"', 
             'src="'.BASE_URL.'LandingPage/LandingPage.js"', 
             'src="'.BASE_URL.'img/', 
             'src="'.BASE_URL.'LandingPage/LandingPageYB/'],
            $htmlContent
        );

        // Fix login buttons
        $htmlContent = str_replace(
            ['id="loginDropdownBtn"', 'id="mobileLoginDropdownBtn"'],
            [
                'id="loginDropdownBtn" onclick="window.location.href=\''.BASE_URL.'Public/Components/Login.php\'"',
                'id="mobileLoginDropdownBtn" onclick="window.location.href=\''.BASE_URL.'Public/Components/Login.php\'"'
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
    '/Public/css/' => '/Public/css/',
    '/Public/js/'  => '/Public/js/'
];

foreach ($staticPaths as $uriPrefix => $folder) {
    if (str_starts_with($requestUri, $uriPrefix)) {
        $filePath = BASE_PATH . $folder . substr($requestUri, strlen($uriPrefix));
        if (file_exists($filePath)) {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimeTypes = [
                'jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png',
                'gif'=>'image/gif','webp'=>'image/webp','css'=>'text/css','js'=>'application/javascript'
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