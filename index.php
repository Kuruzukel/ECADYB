<?php
session_start();
require __DIR__ . '/vendor/autoload.php';

// ----------------------
// MongoDB connection
// ----------------------
$client = new MongoDB\Client("mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957/");
$departmentsDB = $client->Departments;
$adminCollection = $departmentsDB->Admin;

// Department collections
$collections = [
    "bsme"   => "BS Marine Engineering",
    "bsmt"   => "BS Marine Transportation",
    "bscje"  => "BS Criminal Justice Education",
    "bstm"   => "BS Tourism Management",
    "btvted" => "BS Technical-Vocational Teacher Education",
    "beced"  => "BS Early Childhood Education",
    "bsn"    => "BS Nursing",
    "bsis"   => "BS Information System",
    "bsma"   => "BS Management Accounting",
    "bse"    => "BS Entrepreneurship"
];

$error_message = '';

// ----------------------
// Handle login POST
// ----------------------
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['studentId'], $_POST['password'])) {
    $username = trim($_POST['studentId']);
    $password = trim($_POST['password']);

    if (strlen($password) > 8) {
        $error_message = "Password must not exceed 8 characters.";
    } else {
        // ADMIN LOGIN
        $admin = $adminCollection->findOne([
            'username' => $username,
            'password' => $password
        ]);

        if ($admin) {
            $_SESSION['role'] = 'admin';
            $_SESSION['username'] = $username;
            header("Location: /Admin/Components/AdminDashboard.php");
            exit();
        }

        // STUDENT LOGIN
        foreach ($collections as $collectionName => $course) {
            $collection = $departmentsDB->{$collectionName};
            $student = $collection->findOne([
                'student_id' => $username,
                'password'   => $password
            ]);

            if ($student) {
                $_SESSION['role']       = 'student';
                $_SESSION['student_id'] = $student['student_id'];
                $_SESSION['name']       = $student['name'];
                $_SESSION['department'] = $course;
                header("Location: /student_dashboard.php");
                exit();
            }
        }

        $error_message = "Invalid username or password!";
    }
}

// ----------------------
// Router
// ----------------------
if (php_sapi_name() !== 'cli') {
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $normalizedUri = strtolower($requestUri);

    // Serve PHP files directly if they exist
    $directFile = __DIR__ . $requestUri;
    if (file_exists($directFile) && pathinfo($directFile, PATHINFO_EXTENSION) === 'php') {
        include $directFile;
        exit;
    }

    // Root Landing Page
    if ($normalizedUri === '/' || $normalizedUri === '/index.php') {
        $landingPagePath = __DIR__ . '/LandingPage/LandingPage.html';
        if (file_exists($landingPagePath)) {
            $htmlContent = file_get_contents($landingPagePath);

            // Fix paths for deployment
            $htmlContent = str_replace(
                ['href="LandingPage.css"', 'src="LandingPage.js"', 'src="../img/', 'src="LandingPageYB/'],
                ['href="/LandingPage/LandingPage.css"', 'src="/LandingPage/LandingPage.js"', 'src="/img/', 'src="/LandingPage/LandingPageYB/'],
                $htmlContent
            );

            // Fix login buttons → point to PHP login page
            $htmlContent = str_replace(
                ['id="loginDropdownBtn"', 'id="mobileLoginDropdownBtn"'],
                [
                    'id="loginDropdownBtn" onclick="window.location.href=\'/Public/Login.php\'"',
                    'id="mobileLoginDropdownBtn" onclick="window.location.href=\'/Public/Login.php\'"'
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

    // Serve static assets (images, css, js)
    $staticPatterns = [
        '#^/img/(.+)$#i'                   => '/img/',
        '#^/LandingPage/LandingPageYB/pages/(.+)$#i' => '/LandingPage/LandingPageYB/pages/',
        '#^/Public/(css|js)/(.+)$#i'      => '/Public/$1/'
    ];

    foreach ($staticPatterns as $pattern => $baseDir) {
        if (preg_match($pattern, $requestUri, $matches)) {
            $filePath = __DIR__ . $baseDir . $matches[1];
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
}