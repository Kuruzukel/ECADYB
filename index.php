<?php
// Start session
session_start();

require __DIR__ . '/vendor/autoload.php';

use MongoDB\Client;

$mongoPath = __DIR__ . '/Connection/MongoConnect.php';
if (!file_exists($mongoPath)) {
    die("❌ MongoConnect.php not found at: $mongoPath");
}
require $mongoPath;

define('BASE_PATH', __DIR__);
define('BASE_URL', '/');

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['studentId'], $_POST['password'])) {
    $studentId = trim($_POST['studentId']);
    $password  = trim($_POST['password']);

    try {
        $admin = $adminCollection->findOne([
            'username' => $studentId,
            'password' => $password
        ]);

        if ($admin) {
            $_SESSION['role']     = 'admin';
            $_SESSION['username'] = $studentId;

            header("Location: " . BASE_URL . "admin");
            exit;
        }

        $foundStudent = false;
        foreach ($collections as $collectionName => $departmentName) {
            $collection = $departmentsDB->{$collectionName};

            $student = $collection->findOne([
                '$and' => [
                    [
                        '$or' => [
                            ['student id' => $studentId],
                            ['student_id' => $studentId]
                        ]
                    ],
                    ['password' => $password]
                ]
            ]);

            if ($student) {
                $_SESSION['role']       = 'student';

                $_SESSION['student_id'] = $student['student id'] ?? $student['student_id'];

                $_SESSION['name']       = trim(
                    ($student['first name'] ?? '') . ' ' .
                        ($student['middle name'] ?? '') . ' ' .
                        ($student['last name'] ?? '')
                );
                $_SESSION['department'] = $departmentName;
                $_SESSION['section']    = $student['department section'] ?? '';

                $foundStudent = true;

                header("Location: " . BASE_URL . "student");
                exit;
            }
        }

        if (!$foundStudent) {
            $error_message = "Invalid Student ID or password!";
        }
    } catch (Exception $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
}

if (!empty($error_message)) {
    echo "<div style='color:red; font-weight:bold; margin:10px 0;'>$error_message</div>";
}

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$routes = [
    '/LandingPage'        => BASE_PATH . '/LandingPage/LandingPage.html',
    '/Login'              => BASE_PATH . '/Public/Components/Login.php',
    '/Admin'              => BASE_PATH . '/Admin/Components/AdminDashboard.php',
    '/Admin/Components/AdminLogout.php' => BASE_PATH . '/Admin/Components/AdminLogout.php',
    '/Student'            => BASE_PATH . '/Student/Components/StudentDashboard.php',
    '/'                   => BASE_PATH . '/Public/Components/Loader.html',
];

if (array_key_exists($requestUri, $routes)) {
    $filePath = $routes[$requestUri];
    if (file_exists($filePath)) {
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);

        if ($ext === 'php') {
            include $filePath;
        } else {
            $htmlContent = file_get_contents($filePath);

            if (strpos($htmlContent, 'og:title') === false) {
                $metaTags = '
    <!-- Open Graph / Facebook -->
    <meta property="fb:app_id" content="1767810860531321" />
    <meta property="og:locale" content="en_US" />
    <meta
      property="og:title"
      content="Graduation Gallery - Exact Colleges of Asia"
    />
    <meta
      property="og:description"
      content="Step into your digital yearbook. Every achievement and memory comes alive."
    />
    <meta
      property="og:image"
      content="https://ECADYB.b-cdn.net/img/PREVIEWLOGO.png"
    />
    <meta
      property="og:image:secure_url"
      content="https://ECADYB.b-cdn.net/img/PREVIEWLOGO.png"
    />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:url" content="https://grad-gallery.up.railway.app" />
    <meta property="og:type" content="website" />

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta
      name="twitter:title"
      content="Graduation Gallery - Exact Colleges of Asia"
    />
    <meta
      name="twitter:description"
      content="Step into your digital yearbook. Every achievement and memory comes alive."
    />
    <meta
      name="twitter:image"
      content="https://ECADYB.b-cdn.net/img/PREVIEWLOGO.png"
    />
    <meta
      name="twitter:image:alt"
      content="Exact Colleges of Asia Graduation Gallery Preview Logo"
    />

    <!-- Favicon -->
    <link
      rel="icon"
      href="https://ECADYB.b-cdn.net/img/PREVIEWLOGO.png"
      type="image/png"
    />
';

                $htmlContent = str_replace('</head>', $metaTags . '</head>', $htmlContent);
            }

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

            $htmlContent = str_replace(
                ['id="loginDropdownBtn"', 'id="mobileLoginDropdownBtn"'],
                [
                    'id="loginDropdownBtn" onclick="window.location.href=\'' . BASE_URL . 'login\'"',
                    'id="mobileLoginDropdownBtn" onclick="window.location.href=\'' . BASE_URL . 'login\'"'
                ],
                $htmlContent
            );

            echo $htmlContent;
        }
        exit;
    } else {
        http_response_code(404);
        echo "❌ File not found for route: $requestUri";
        exit;
    }
}

// Static File Serving (CSS, JS, Images)
$staticPaths = [
    '/img/'                             => '/img/',
    '/LandingPage/LandingPageYB/pages/' => '/LandingPage/LandingPageYB/pages/',
    '/LandingPage/'                     => '/LandingPage/',
    '/Public/assets/css/'               => '/Public/assets/css/',
    '/Public/assets/js/'                => '/Public/assets/js/'
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
                'js'   => 'application/javascript',
                'svg'  => 'image/svg+xml',
                'html' => 'text/html'
            ];
            header('Content-Type: ' . ($mimeTypes[$ext] ?? 'application/octet-stream'));
            readfile($filePath);
            exit;
        }
    }
}

// 404 Fallback
http_response_code(404);
echo '<h1>ECADYB Application</h1>';
echo '<p>Page not found.</p>';
exit;
