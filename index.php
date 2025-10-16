<?php
session_start();

require __DIR__ . '/vendor/autoload.php';

use MongoDB\Client;

$mongoPath = __DIR__ . '/Connection/Configuration/MongoConnect.php';
if (!file_exists($mongoPath)) {
    die("❌ MongoConnect.php not found at: $mongoPath");
}
require $mongoPath;

define('BASE_PATH', __DIR__);

if (getenv('RAILWAY_PUBLIC_URL')) {
    define('BASE_URL', '/ECADYB/');
} else {
    define('BASE_URL', '/ECADYB/');
}

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

            header("Location: " . BASE_URL . "Admin");
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

                header("Location: " . BASE_URL . "Student");
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

// Redirect .html requests to .php files
$htmlToPhpRedirects = [
    '/Student/Components/StudentDashboard.html' => '/Student/Components/StudentDashboard.php',
    '/Student/Components/About.html' => '/Student/Components/About.php',
    '/Student/Components/Yearbook.html' => '/Student/Components/Yearbook.php',
    '/Student/Components/Memories.html' => '/Student/Components/Memories.php',
    '/Student/Components/ChangePassword.html' => '/Student/Components/ChangePassword.php',
    // ECADYB prefixed redirects for Railway
    '/ECADYB/Student/Components/StudentDashboard.html' => '/ECADYB/Student/Components/StudentDashboard.php',
    '/ECADYB/Student/Components/About.html' => '/ECADYB/Student/Components/About.php',
    '/ECADYB/Student/Components/Yearbook.html' => '/ECADYB/Student/Components/Yearbook.php',
    '/ECADYB/Student/Components/Memories.html' => '/ECADYB/Student/Components/Memories.php',
    '/ECADYB/Student/Components/ChangePassword.html' => '/ECADYB/Student/Components/ChangePassword.php',
];

if (isset($htmlToPhpRedirects[$requestUri])) {
    header('Location: ' . $htmlToPhpRedirects[$requestUri], true, 301);
    exit();
}

$routes = [
    '/LandingPage'        => BASE_PATH . '/LandingPage/index.html',
    '/login'              => BASE_PATH . '/Public/Components/Login.php',
    '/Login'              => BASE_PATH . '/Public/Components/Login.php',
    '/Admin'              => BASE_PATH . '/Admin/Components/AdminDashboard.php',
    '/Admin/Components/AdminLogout.php' => BASE_PATH . '/Admin/Components/AdminLogout.php',
    '/Student'            => BASE_PATH . '/Student/Components/StudentDashboard.php',
    '/Student/Components/StudentDashboard.php' => BASE_PATH . '/Student/Components/StudentDashboard.php',
    '/Student/Components/About.php' => BASE_PATH . '/Student/Components/About.php',
    '/Student/Components/Yearbook.php' => BASE_PATH . '/Student/Components/Yearbook.php',
    '/Student/Components/Memories.php' => BASE_PATH . '/Student/Components/Memories.php',
    '/Student/Components/Logout.php' => BASE_PATH . '/Student/Components/Logout.php',
    '/'                   => BASE_PATH . '/Public/Components/Loader.html',
    // ECADYB prefixed routes for Railway
    '/ECADYB/LandingPage'        => BASE_PATH . '/LandingPage/index.html',
    '/ECADYB/login'              => BASE_PATH . '/Public/Components/Login.php',
    '/ECADYB/Login'              => BASE_PATH . '/Public/Components/Login.php',
    '/ECADYB/Admin'              => BASE_PATH . '/Admin/Components/AdminDashboard.php',
    '/ECADYB/Admin/Components/AdminLogout.php' => BASE_PATH . '/Admin/Components/AdminLogout.php',
    '/ECADYB/Student'            => BASE_PATH . '/Student/Components/StudentDashboard.php',
    '/ECADYB/Student/Components/StudentDashboard.php' => BASE_PATH . '/Student/Components/StudentDashboard.php',
    '/ECADYB/Student/Components/About.php' => BASE_PATH . '/Student/Components/About.php',
    '/ECADYB/Student/Components/Yearbook.php' => BASE_PATH . '/Student/Components/Yearbook.php',
    '/ECADYB/Student/Components/Memories.php' => BASE_PATH . '/Student/Components/Memories.php',
    '/ECADYB/Student/Components/Logout.php' => BASE_PATH . '/Student/Components/Logout.php',
    '/ECADYB/'                   => BASE_PATH . '/Public/Components/Loader.html',
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
                    'href="style.css"',
                    'href="style.css?v=',
                    'src="script.js"',
                    'src="script.js?v=',
                    'src="../img/',
                    'src="LandingPageYB/',
                    'href="../assets/css/Login.css"',
                    'href="../assets/css/Login.css?v=',
                    'src="../assets/js/Login.js"',
                    'src="../assets/js/Login.js?v=',
                    'href="../assets/css/ForgotPassword.css"',
                    'href="../assets/css/ForgotPassword.css?v=',
                    'src="../assets/js/ForgotPassword.js"',
                    'src="../assets/js/ForgotPassword.js?v='
                ],
                [
                    'href="' . BASE_URL . 'LandingPage/style.css"',
                    'href="' . BASE_URL . 'LandingPage/style.css?v=',
                    'src="' . BASE_URL . 'LandingPage/script.js"',
                    'src="' . BASE_URL . 'LandingPage/script.js?v=',
                    'src="' . BASE_URL . 'img/',
                    'src="' . BASE_URL . 'LandingPage/LandingPageYB/',
                    'href="' . BASE_URL . 'Public/assets/css/Login.css"',
                    'href="' . BASE_URL . 'Public/assets/css/Login.css?v=',
                    'src="' . BASE_URL . 'Public/assets/js/Login.js"',
                    'src="' . BASE_URL . 'Public/assets/js/Login.js?v=',
                    'href="' . BASE_URL . 'Public/assets/css/ForgotPassword.css"',
                    'href="' . BASE_URL . 'Public/assets/css/ForgotPassword.css?v=',
                    'src="' . BASE_URL . 'Public/assets/js/ForgotPassword.js"',
                    'src="' . BASE_URL . 'Public/assets/js/ForgotPassword.js?v='
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

$staticPaths = [
    '/img/'                             => '/img/',
    '/LandingPage/'                     => '/LandingPage/',
    '/Public/assets/css/'               => '/Public/assets/css/',
    '/Public/assets/js/'                => '/Public/assets/js/',
    '/Admin/assets/css/'                => '/Admin/assets/css/',
    '/Admin/assets/js/'                 => '/Admin/assets/js/',
    '/Admin/Departments/assets/css/'    => '/Admin/Departments/assets/css/',
    '/Admin/Departments/assets/js/'     => '/Admin/Departments/assets/js/',
    '/Admin/Yearbook/'                  => '/Admin/Yearbook/',
    '/Admin/Yearbook/pages/'            => '/Admin/Yearbook/pages/',
    '/Admin/Yearbook/pics/'             => '/Admin/Yearbook/pics/',
    '/Admin/Yearbook/css/'              => '/Admin/Yearbook/css/',
    '/Admin/Yearbook/js/'               => '/Admin/Yearbook/js/',
    '/Student/assets/css/'              => '/Student/assets/css/',
    '/Student/assets/js/'               => '/Student/assets/js/',
    '/Connection/'                      => '/Connection/',
    '/Turn.js/'                         => '/Turn.js/',
    // ECADYB prefixed static paths for Railway
    '/ECADYB/img/'                             => '/img/',
    '/ECADYB/LandingPage/'                     => '/LandingPage/',
    '/ECADYB/Public/assets/css/'               => '/Public/assets/css/',
    '/ECADYB/Public/assets/js/'                => '/Public/assets/js/',
    '/ECADYB/Admin/assets/css/'                => '/Admin/assets/css/',
    '/ECADYB/Admin/assets/js/'                 => '/Admin/assets/js/',
    '/ECADYB/Admin/Departments/assets/css/'    => '/Admin/Departments/assets/css/',
    '/ECADYB/Admin/Departments/assets/js/'     => '/Admin/Departments/assets/js/',
    '/ECADYB/Admin/Yearbook/'                  => '/Admin/Yearbook/',
    '/ECADYB/Admin/Yearbook/pages/'            => '/Admin/Yearbook/pages/',
    '/ECADYB/Admin/Yearbook/pics/'             => '/Admin/Yearbook/pics/',
    '/ECADYB/Admin/Yearbook/css/'              => '/Admin/Yearbook/css/',
    '/ECADYB/Admin/Yearbook/js/'               => '/Admin/Yearbook/js/',
    '/ECADYB/Student/assets/css/'              => '/Student/assets/css/',
    '/ECADYB/Student/assets/js/'               => '/Student/assets/js/',
    '/ECADYB/Connection/'                      => '/Connection/',
    '/ECADYB/Turn.js/'                         => '/Turn.js/'
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

http_response_code(404);
echo '<h1>ECADYB Application</h1>';
echo '<p>Page not found.</p>';
exit;
