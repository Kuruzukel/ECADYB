<?php
session_start();

require __DIR__ . '/Connection/Configuration/HeadersConfig.php';
require __DIR__ . '/vendor/autoload.php';

use MongoDB\Client;

define('BASE_PATH', __DIR__);

if (getenv('RAILWAY_PUBLIC_URL')) {
    define('BASE_URL', '/');
} else {
    define('BASE_URL', '/ECADYB/');
}

$error_message = '';
$mongoConnected = false;
$adminCollection = null;
$departmentsDB = null;
$collections = [];

try {
    $mongoPath = __DIR__ . '/Connection/Configuration/MongoConnect.php';
    if (!file_exists($mongoPath)) {
        throw new Exception("MongoConnect.php not found at: $mongoPath");
    }
    require $mongoPath;
    $mongoConnected = true;
} catch (Exception $e) {
    error_log("MongoDB Connection Error in index.php: " . $e->getMessage());
    $error_message = "Database connection error. The application is currently unavailable. Please contact the administrator.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['studentId'], $_POST['password']) && $mongoConnected && $adminCollection !== null && $departmentsDB !== null) {
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

if ($requestUri === '/favicon.ico' || $requestUri === '/ECADYB/favicon.ico') {
    $faviconPath = BASE_PATH . '/img/PREVIEWLOGO.png';
    if (file_exists($faviconPath)) {
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=31536000');
        readfile($faviconPath);
        exit;
    } else {
        http_response_code(204);
        exit;
    }
}

$directFileRedirects = [
    '/ECADYB/Admin/Components/AdminDashboard.php' => '/ECADYB/Admin',
    '/ECADYB/Student/Components/StudentDashboard.php' => '/ECADYB/Student',
];

if (isset($directFileRedirects[$requestUri])) {
    header('Location: ' . $directFileRedirects[$requestUri], true, 301);
    exit();
}

$htmlToPhpRedirects = [
    '/Student/Components/StudentDashboard.html' => '/Student/Components/StudentDashboard.php',
    '/Student/Components/About.html' => '/Student/Components/About.php',
    '/Student/Components/Yearbook.html' => '/Student/Components/Yearbook.php',
    '/Student/Components/Memories.html' => '/Student/Components/Memories.php',
    '/Student/Components/ChangePassword.html' => '/Student/Components/ChangePassword.php',
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

if ($requestUri === '/ECADYB/Admin' || $requestUri === '/ECADYB/Admin/') {
    $filePath = BASE_PATH . '/Admin/Components/AdminDashboard.php';
    if (file_exists($filePath)) {
        include $filePath;
        exit;
    }
}

$routes = [
    '/LandingPage'        => BASE_PATH . '/LandingPage/index.html',
    '/login'              => BASE_PATH . '/Public/Components/Login.php',
    '/Login'              => BASE_PATH . '/Public/Components/Login.php',
    '/Login.php'          => BASE_PATH . '/Public/Components/Login.php',
    '/forgotpassword'     => BASE_PATH . '/Public/Components/ForgotPassword.html',
    '/ForgotPassword'     => BASE_PATH . '/Public/Components/ForgotPassword.html',
    '/Public/Components/Login.php' => BASE_PATH . '/Public/Components/Login.php',
    '/Public/Components/ForgotPassword.html' => BASE_PATH . '/Public/Components/ForgotPassword.html',
    '/Admin'              => BASE_PATH . '/Admin/Components/AdminDashboard.php',
    '/Admin/Components/AddNewStudent.php' => BASE_PATH . '/Admin/Components/AddNewStudent.php',
    '/Admin/Components/BatchTemplates.php' => BASE_PATH . '/Admin/Components/BatchTemplates.php',
    '/Admin/Components/BatchUpload.php' => BASE_PATH . '/Admin/Components/BatchUpload.php',
    '/Admin/Components/ChangePassword.php' => BASE_PATH . '/Admin/Components/ChangePassword.php',
    '/Admin/Components/CreateAnnouncement.php' => BASE_PATH . '/Admin/Components/CreateAnnouncement.php',
    '/Admin/Components/EventCalendar.php' => BASE_PATH . '/Admin/Components/EventCalendar.php',
    '/Admin/Components/StudentList.php' => BASE_PATH . '/Admin/Components/StudentList.php',
    '/Admin/Components/Themes.php' => BASE_PATH . '/Admin/Components/Themes.php',
    '/Admin/Components/AdminLogout.php' => BASE_PATH . '/Admin/Components/AdminLogout.php',
    '/Admin/Departments/BusinessAdministration.php' => BASE_PATH . '/Admin/Departments/BusinessAdministration.php',
    '/Admin/Departments/Criminology.php' => BASE_PATH . '/Admin/Departments/Criminology.php',
    '/Admin/Departments/Education.php' => BASE_PATH . '/Admin/Departments/Education.php',
    '/Admin/Departments/InformationSystem.php' => BASE_PATH . '/Admin/Departments/InformationSystem.php',
    '/Admin/Departments/Maritime.php' => BASE_PATH . '/Admin/Departments/Maritime.php',
    '/Admin/Departments/Nursing.php' => BASE_PATH . '/Admin/Departments/Nursing.php',
    '/Admin/Departments/Tourism.php' => BASE_PATH . '/Admin/Departments/Tourism.php',
    '/Student'            => BASE_PATH . '/Student/Components/StudentDashboard.php',
    '/Student/Components/About.php' => BASE_PATH . '/Student/Components/About.php',
    '/Student/Components/Yearbook.php' => BASE_PATH . '/Student/Components/Yearbook.php',
    '/Student/Components/Memories.php' => BASE_PATH . '/Student/Components/Memories.php',
    '/Student/Components/ChangePassword.php' => BASE_PATH . '/Student/Components/ChangePassword.php',
    '/Student/ChangePassword' => BASE_PATH . '/Student/Components/ChangePassword.php',
    '/Student/Components/Logout.php' => BASE_PATH . '/Student/Components/Logout.php',
    '/Connection/Admin/ChangePassword.php' => BASE_PATH . '/Connection/Admin/ChangePassword.php',
    '/Connection/Announcement/DeleteAnnouncement.php' => BASE_PATH . '/Connection/Announcement/DeleteAnnouncement.php',
    '/Connection/Announcement/FetchAnnouncement.php' => BASE_PATH . '/Connection/Announcement/FetchAnnouncement.php',
    '/Connection/Announcement/FetchAnnouncements.php' => BASE_PATH . '/Connection/Announcement/FetchAnnouncements.php',
    '/Connection/Announcement/SubmitAnnouncement.php' => BASE_PATH . '/Connection/Announcement/SubmitAnnouncement.php',
    '/Connection/Cover/DeleteCover.php' => BASE_PATH . '/Connection/Cover/DeleteCover.php',
    '/Connection/Cover/FetchCovers.php' => BASE_PATH . '/Connection/Cover/FetchCovers.php',
    '/Connection/Cover/UploadCover.php' => BASE_PATH . '/Connection/Cover/UploadCover.php',
    '/Connection/Logo/DeleteLogo.php' => BASE_PATH . '/Connection/Logo/DeleteLogo.php',
    '/Connection/Logo/FetchAdminLogo.php' => BASE_PATH . '/Connection/Logo/FetchAdminLogo.php',
    '/Connection/Logo/FetchLogos.php' => BASE_PATH . '/Connection/Logo/FetchLogos.php',
    '/Connection/Logo/UpdateAdminLogo.php' => BASE_PATH . '/Connection/Logo/UpdateAdminLogo.php',
    '/Connection/Logo/UploadLogo.php' => BASE_PATH . '/Connection/Logo/UploadLogo.php',
    '/Connection/Photos/FetchStudentData.php' => BASE_PATH . '/Connection/Photos/FetchStudentData.php',
    '/Connection/Photos/FetchStudentPhotos.php' => BASE_PATH . '/Connection/Photos/FetchStudentPhotos.php',
    '/Connection/Photos/FetchTopManagement.php' => BASE_PATH . '/Connection/Photos/FetchTopManagement.php',
    '/Connection/Photos/FetchTopManagementMessages.php' => BASE_PATH . '/Connection/Photos/FetchTopManagementMessages.php',
    '/Connection/Photos/FetchTopManagementPhotos.php' => BASE_PATH . '/Connection/Photos/FetchTopManagementPhotos.php',
    '/Connection/Photos/UploadStudentPhotos.php' => BASE_PATH . '/Connection/Photos/UploadStudentPhotos.php',
    '/Connection/Photos/UploadTopManagementPhotos.php' => BASE_PATH . '/Connection/Photos/UploadTopManagementPhotos.php',
    '/Admin/Yearbook/FetchCoverData.php' => BASE_PATH . '/Admin/Yearbook/FetchCoverData.php',
    '/Student/Yearbook/FetchCoverData.php' => BASE_PATH . '/Student/Yearbook/FetchCoverData.php',
    '/Connection/Student/BulkUpdateStatus.php' => BASE_PATH . '/Connection/Student/BulkUpdateStatus.php',
    '/Connection/Student/CheckEmail.php' => BASE_PATH . '/Connection/Student/CheckEmail.php',
    '/Connection/Student/DeleteStudent.php' => BASE_PATH . '/Connection/Student/DeleteStudent.php',
    '/Connection/Student/ForgotPassword.php' => BASE_PATH . '/Connection/Student/ForgotPassword.php',
    '/Connection/Student/SendOTP.php' => BASE_PATH . '/Connection/Student/SendOTP.php',
    '/Connection/Student/SearchStudents.php' => BASE_PATH . '/Connection/Student/SearchStudents.php',
    '/Connection/Student/UpdateStatus.php' => BASE_PATH . '/Connection/Student/UpdateStatus.php',
    '/Connection/Student/UpdateStudent.php' => BASE_PATH . '/Connection/Student/UpdateStudent.php',
    '/'                   => BASE_PATH . '/Public/Components/Loader.html',
    '/ECADYB/LandingPage'        => BASE_PATH . '/LandingPage/index.html',
    '/ECADYB/login'              => BASE_PATH . '/Public/Components/Login.php',
    '/ECADYB/Login'              => BASE_PATH . '/Public/Components/Login.php',
    '/ECADYB/Login.php'          => BASE_PATH . '/Public/Components/Login.php',
    '/ECADYB/forgotpassword'     => BASE_PATH . '/Public/Components/ForgotPassword.html',
    '/ECADYB/ForgotPassword'     => BASE_PATH . '/Public/Components/ForgotPassword.html',
    '/ECADYB/Public/Components/Login.php' => BASE_PATH . '/Public/Components/Login.php',
    '/ECADYB/Public/Components/ForgotPassword.html' => BASE_PATH . '/Public/Components/ForgotPassword.html',
    '/ECADYB/Admin'              => BASE_PATH . '/Admin/Components/AdminDashboard.php',
    '/ECADYB/Admin/Components/AddNewStudent.php' => BASE_PATH . '/Admin/Components/AddNewStudent.php',
    '/ECADYB/Admin/Components/BatchTemplates.php' => BASE_PATH . '/Admin/Components/BatchTemplates.php',
    '/ECADYB/Admin/Components/BatchUpload.php' => BASE_PATH . '/Admin/Components/BatchUpload.php',
    '/ECADYB/Admin/Components/ChangePassword.php' => BASE_PATH . '/Admin/Components/ChangePassword.php',
    '/ECADYB/Admin/Components/CreateAnnouncement.php' => BASE_PATH . '/Admin/Components/CreateAnnouncement.php',
    '/ECADYB/Admin/Components/EventCalendar.php' => BASE_PATH . '/Admin/Components/EventCalendar.php',
    '/ECADYB/Admin/Components/StudentList.php' => BASE_PATH . '/Admin/Components/StudentList.php',
    '/ECADYB/Admin/Components/Themes.php' => BASE_PATH . '/Admin/Components/Themes.php',
    '/ECADYB/Admin/Components/AdminLogout.php' => BASE_PATH . '/Admin/Components/AdminLogout.php',
    '/ECADYB/Admin/Departments/BusinessAdministration.php' => BASE_PATH . '/Admin/Departments/BusinessAdministration.php',
    '/ECADYB/Admin/Departments/Criminology.php' => BASE_PATH . '/Admin/Departments/Criminology.php',
    '/ECADYB/Admin/Departments/Education.php' => BASE_PATH . '/Admin/Departments/Education.php',
    '/ECADYB/Admin/Departments/InformationSystem.php' => BASE_PATH . '/Admin/Departments/InformationSystem.php',
    '/ECADYB/Admin/Departments/Maritime.php' => BASE_PATH . '/Admin/Departments/Maritime.php',
    '/ECADYB/Admin/Departments/Nursing.php' => BASE_PATH . '/Admin/Departments/Nursing.php',
    '/ECADYB/Admin/Departments/Tourism.php' => BASE_PATH . '/Admin/Departments/Tourism.php',
    '/ECADYB/Student'            => BASE_PATH . '/Student/Components/StudentDashboard.php',
    '/ECADYB/Student/Components/About.php' => BASE_PATH . '/Student/Components/About.php',
    '/ECADYB/Student/Components/Yearbook.php' => BASE_PATH . '/Student/Components/Yearbook.php',
    '/ECADYB/Student/Components/Memories.php' => BASE_PATH . '/Student/Components/Memories.php',
    '/ECADYB/Student/Components/ChangePassword.php' => BASE_PATH . '/Student/Components/ChangePassword.php',
    '/ECADYB/Student/ChangePassword' => BASE_PATH . '/Student/Components/ChangePassword.php',
    '/ECADYB/Student/Components/Logout.php' => BASE_PATH . '/Student/Components/Logout.php',
    '/ECADYB/Connection/Admin/ChangePassword.php' => BASE_PATH . '/Connection/Admin/ChangePassword.php',
    '/ECADYB/Connection/Announcement/DeleteAnnouncement.php' => BASE_PATH . '/Connection/Announcement/DeleteAnnouncement.php',
    '/ECADYB/Connection/Announcement/FetchAnnouncement.php' => BASE_PATH . '/Connection/Announcement/FetchAnnouncement.php',
    '/ECADYB/Connection/Announcement/FetchAnnouncements.php' => BASE_PATH . '/Connection/Announcement/FetchAnnouncements.php',
    '/ECADYB/Connection/Announcement/SubmitAnnouncement.php' => BASE_PATH . '/Connection/Announcement/SubmitAnnouncement.php',
    '/ECADYB/Connection/Cover/DeleteCover.php' => BASE_PATH . '/Connection/Cover/DeleteCover.php',
    '/ECADYB/Connection/Cover/FetchCovers.php' => BASE_PATH . '/Connection/Cover/FetchCovers.php',
    '/ECADYB/Connection/Cover/UploadCover.php' => BASE_PATH . '/Connection/Cover/UploadCover.php',
    '/ECADYB/Connection/Logo/DeleteLogo.php' => BASE_PATH . '/Connection/Logo/DeleteLogo.php',
    '/ECADYB/Connection/Logo/FetchAdminLogo.php' => BASE_PATH . '/Connection/Logo/FetchAdminLogo.php',
    '/ECADYB/Connection/Logo/FetchLogos.php' => BASE_PATH . '/Connection/Logo/FetchLogos.php',
    '/ECADYB/Connection/Logo/UpdateAdminLogo.php' => BASE_PATH . '/Connection/Logo/UpdateAdminLogo.php',
    '/ECADYB/Connection/Logo/UploadLogo.php' => BASE_PATH . '/Connection/Logo/UploadLogo.php',
    '/ECADYB/Connection/Photos/FetchStudentData.php' => BASE_PATH . '/Connection/Photos/FetchStudentData.php',
    '/ECADYB/Connection/Photos/FetchStudentPhotos.php' => BASE_PATH . '/Connection/Photos/FetchStudentPhotos.php',
    '/ECADYB/Connection/Photos/FetchTopManagement.php' => BASE_PATH . '/Connection/Photos/FetchTopManagement.php',
    '/ECADYB/Connection/Photos/FetchTopManagementMessages.php' => BASE_PATH . '/Connection/Photos/FetchTopManagementMessages.php',
    '/ECADYB/Connection/Photos/FetchTopManagementPhotos.php' => BASE_PATH . '/Connection/Photos/FetchTopManagementPhotos.php',
    '/ECADYB/Connection/Photos/test_top_management.php' => BASE_PATH . '/Connection/Photos/test_top_management.php',
    '/ECADYB/Connection/Photos/UploadStudentPhotos.php' => BASE_PATH . '/Connection/Photos/UploadStudentPhotos.php',
    '/ECADYB/Connection/Photos/UploadTopManagementPhotos.php' => BASE_PATH . '/Connection/Photos/UploadTopManagementPhotos.php',
    '/ECADYB/Admin/Yearbook/FetchCoverData.php' => BASE_PATH . '/Admin/Yearbook/FetchCoverData.php',
    '/ECADYB/Student/Yearbook/FetchCoverData.php' => BASE_PATH . '/Student/Yearbook/FetchCoverData.php',
    '/ECADYB/Connection/Student/BulkUpdateStatus.php' => BASE_PATH . '/Connection/Student/BulkUpdateStatus.php',
    '/ECADYB/Connection/Student/CheckEmail.php' => BASE_PATH . '/Connection/Student/CheckEmail.php',
    '/ECADYB/Connection/Student/DeleteStudent.php' => BASE_PATH . '/Connection/Student/DeleteStudent.php',
    '/ECADYB/Connection/Student/ForgotPassword.php' => BASE_PATH . '/Connection/Student/ForgotPassword.php',
    '/ECADYB/Connection/Student/SearchStudents.php' => BASE_PATH . '/Connection/Student/SearchStudents.php',
    '/ECADYB/Connection/Student/SendOTP.php' => BASE_PATH . '/Connection/Student/SendOTP.php',
    '/ECADYB/Connection/Student/UpdateStatus.php' => BASE_PATH . '/Connection/Student/UpdateStatus.php',
    '/ECADYB/Connection/Student/UpdateStudent.php' => BASE_PATH . '/Connection/Student/UpdateStudent.php',
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
    '/Admin/Flipbook/'                  => '/Admin/Flipbook/',
    '/Admin/Yearbook/'                  => '/Admin/Yearbook/',
    '/Admin/Yearbook/pages/'            => '/Admin/Yearbook/pages/',
    '/Admin/Yearbook/pics/'             => '/Admin/Yearbook/pics/',
    '/Admin/Yearbook/css/'              => '/Admin/Yearbook/css/',
    '/Admin/Yearbook/js/'               => '/Admin/Yearbook/js/',
    '/Student/assets/css/'              => '/Student/assets/css/',
    '/Student/assets/js/'               => '/Student/assets/js/',
    '/Student/Yearbook/'                => '/Student/Yearbook/',
    '/Student/Yearbook/css/'            => '/Student/Yearbook/css/',
    '/Student/Yearbook/js/'             => '/Student/Yearbook/js/',
    '/Student/Yearbook/pics/'           => '/Student/Yearbook/pics/',
    '/Connection/'                      => '/Connection/',
    '/Turn.js/'                         => '/Turn.js/',
    '/ECADYB/img/'                             => '/img/',
    '/ECADYB/LandingPage/'                     => '/LandingPage/',
    '/ECADYB/Public/assets/css/'               => '/Public/assets/css/',
    '/ECADYB/Public/assets/js/'                => '/Public/assets/js/',
    '/ECADYB/Admin/assets/css/'                => '/Admin/assets/css/',
    '/ECADYB/Admin/assets/js/'                 => '/Admin/assets/js/',
    '/ECADYB/Admin/Departments/assets/css/'    => '/Admin/Departments/assets/css/',
    '/ECADYB/Admin/Departments/assets/js/'     => '/Admin/Departments/assets/js/',
    '/ECADYB/Admin/Flipbook/'                  => '/Admin/Flipbook/',
    '/ECADYB/Admin/Yearbook/'                  => '/Admin/Yearbook/',
    '/ECADYB/Admin/Yearbook/pages/'            => '/Admin/Yearbook/pages/',
    '/ECADYB/Admin/Yearbook/pics/'             => '/Admin/Yearbook/pics/',
    '/ECADYB/Admin/Yearbook/css/'              => '/Admin/Yearbook/css/',
    '/ECADYB/Admin/Yearbook/js/'               => '/Admin/Yearbook/js/',
    '/ECADYB/Admin/Yearbook/test_top_management.html' => BASE_PATH . '/Admin/Yearbook/test_top_management.html',
    '/ECADYB/Student/Yearbook/slider.html' => BASE_PATH . '/Student/Yearbook/slider.html',
    '/ECADYB/Student/Yearbook/test_top_management.html' => BASE_PATH . '/Student/Yearbook/test_top_management.html',
    '/ECADYB/Student/assets/css/'              => '/Student/assets/css/',
    '/ECADYB/Student/assets/js/'               => '/Student/assets/js/',
    '/ECADYB/Student/Yearbook/'                => '/Student/Yearbook/',
    '/ECADYB/Student/Yearbook/css/'            => '/Student/Yearbook/css/',
    '/ECADYB/Student/Yearbook/js/'             => '/Student/Yearbook/js/',
    '/ECADYB/Student/Yearbook/pics/'           => '/Student/Yearbook/pics/',
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
