<?php
session_start();
require __DIR__ . '/../../vendor/autoload.php';

use MongoDB\Client;

$client = new Client("mongodb://mongo:tIEbUVpHiKhDZTkghDEMqERbLDdsDRnX@shortline.proxy.rlwy.net:56957/");
$departmentsDB = $client->Departments;
$adminCollection = $departmentsDB->Admin;

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['studentId']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error_message = "Please fill in all fields.";
    } elseif (strlen($password) > 8) {
        $error_message = "Password must not exceed 8 characters.";
    } else {
        $admin = $adminCollection->findOne([
            'username' => $username,
            'password' => $password
        ]);

        if ($admin) {
            $_SESSION['role']     = 'admin';
            $_SESSION['username'] = $username;
            $_SESSION['login_success'] = 'admin';
            $_SESSION['redirect_to'] = '../../Admin/Components/AdminDashboard.php';
        } else {
            $loginFound = false;
            foreach ($collections as $collectionName => $course) {
                $collection = $departmentsDB->{$collectionName};

                $student = $collection->findOne([
                    'student id' => $username,
                    'password'   => $password
                ]);

                if ($student) {
                    $_SESSION['role']       = 'student';
                    $_SESSION['student_id'] = $student['student id'];
                    $_SESSION['name']       = trim(($student['first name'] ?? '') . ' ' . ($student['middle name'] ?? '') . ' ' . ($student['last name'] ?? ''));
                    $_SESSION['department'] = $course;
                    $_SESSION['section']    = $student['department section'] ?? '';
                    $_SESSION['login_success'] = 'student';
                    $_SESSION['redirect_to'] = '../../Student/Components/StudentDashboard.php';
                    $loginFound = true;
                    break;
                }
            }

            if (!$loginFound) {
                $error_message = "Invalid student ID or password!";
            }
        }
    }
}
?>




<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Graduation Gallery</title>

    <meta property="fb:app_id" content="1767810860531321" />
    <meta property="og:locale" content="en_US" />
    <meta property="og:title" content="Graduation Gallery - Exact Colleges of Asia" />
    <meta property="og:description"
        content="Step into your digital yearbook. Every achievement and memory comes alive." />
    <meta property="og:image" content="https://ECADYB.b-cdn.net/img/PREVIEWLOGO.png" />
    <meta property="og:image:secure_url" content="https://ECADYB.b-cdn.net/img/PREVIEWLOGO.png" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta property="og:url" content="https://grad-gallery.up.railway.app" />
    <meta property="og:type" content="website" />

    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="Graduation Gallery - Exact Colleges of Asia" />
    <meta name="twitter:description"
        content="Step into your digital yearbook. Every achievement and memory comes alive." />
    <meta name="twitter:image" content="https://ECADYB.b-cdn.net/img/PREVIEWLOGO.png" />
    <meta name="twitter:image:alt" content="Exact Colleges of Asia Graduation Gallery Preview Logo" />

    <link rel="icon" href="https://ECADYB.b-cdn.net/img/PREVIEWLOGO.png" type="image/png" />
    <link href="../assets/css/Login.css?v=2025012" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body<?php
        if (isset($_SESSION['login_success']) && isset($_SESSION['redirect_to'])) {
            echo ' data-login-success="' . htmlspecialchars($_SESSION['login_success']) . '"';
            echo ' data-redirect-to="' . htmlspecialchars($_SESSION['redirect_to']) . '"';
            unset($_SESSION['login_success']);
            unset($_SESSION['redirect_to']);
        }
        if (!empty($error_message)) {
            echo ' data-error-message="' . htmlspecialchars($error_message) . '"';
        }
        ?>>
    <!-- Error Popup Modal -->
    <div id="errorModal" class="error-modal">
        <div class="error-modal-content">
            <button class="error-modal-close" onclick="hideErrorModal()">&times;</button>
            <div class="error-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24">
                    <path fill="white"
                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10s10-4.48 10-10S17.52 2 12 2zM13 17h-2v-2h2v2zm0-4h-2V7h2v6z" />
                </svg>
            </div>
            <div class="error-text">
                <p id="errorMessage"></p>
            </div>
        </div>
    </div>

    <div class="loginCard">
        <form method="POST" action="">
            <p class="title">GRADUATION GALLERY</p>
            <div class="user field">
                <div class="handle">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                        <g fill="none">
                            <path
                                d="M24 0v24H0V0zM12.594 23.258l-.012.002l-.071.035l-.02.004l-.014-.004l-.071-.036q-.016-.004-.024.006l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427q-.004-.016-.016-.018m.264-.113l-.014.002l-.184.093l-.01.01l-.003.011l.018.43l.005.012l.008.008l.201.092q.019.005.029-.008l.004-.014l-.034-.614q-.005-.018-.02-.022m-.715.002a.02.02 0 0 0-.027.006l-.006.014l-.034.614q.001.018.017.024l.015-.002l.201-.093l.01-.008l.003-.011l.018-.43l-.003-.012l-.01-.01z" />
                            <path fill="#ffffff"
                                d="M11 2a5 5 0 1 0 0 10a5 5 0 0 0 0-10m0 11c-2.395 0-4.575.694-6.178 1.672c-.8.488-1.484 1.064-1.978 1.69C2.358 16.976 2 17.713 2 18.5c0 .845.411 1.511 1.003 1.986c.56.45 1.299.748 2.084.956C6.665 21.859 8.771 22 11 22l.685-.005a1 1 0 0 0 .89-1.428A6 6 0 0 1 12 18c0-1.252.383-2.412 1.037-3.373a1 1 0 0 0-.72-1.557Q11.671 13 11 13m9.616 2.469a1 1 0 1 0-1.732-1l-.336.582a3 3 0 0 0-1.097-.001l-.335-.581a1 1 0 1 0-1.732 1l.335.58a3 3 0 0 0-.547.951H14.5a1 1 0 0 0 0 2h.671a3 3 0 0 0 .549.95l-.336.581a1 1 0 1 0 1.732 1l.336-.581c.359.066.73.068 1.097 0l.335.581a1 1 0 1 0 1.732-1l-.335-.58c.242-.284.426-.607.547-.951h.672a1 1 0 1 0 0-2h-.671a3 3 0 0 0-.549-.95z" />
                        </g>
                    </svg>
                    <p>Username / Student ID:</p>
                </div>
                <input name="studentId" id="idInput" type="text" placeholder="Username / Student ID" maxlength="11"
                    autocomplete="off" oninput="limitID()" />
            </div>
            <div class="pass field">
                <div class="handle">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                        <g fill="none" fill-rule="evenodd">
                            <path
                                d="M24 0v24H0V0zM12.593 23.258l-.011.002l-.071.035l-.02.004l-.014-.004l-.071-.035q-.016-.005-.024.005l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427q-.004-.016-.017-.018m.265-.113l-.013.002l-.185.093l-.01.01l-.003.011l.018.43l.005.012l.008.007l.201.093q.019.005.029-.008l.004-.014l-.034-.614q-.005-.019-.02-.022m-.715.002a.02.02 0 0 0-.027.006l-.006.014l-.034.614q.001.018.017.024l.015-.002l.201-.093l.01-.008l.004-.011l.017-.43l-.003-.012l-.01-.01z" />
                            <path fill="#ffffff"
                                d="M6 8a6 6 0 1 1 12 0h1a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V10a2 2 0 0 1 2-2zm6-4a4 4 0 0 1 4 4H8a4 4 0 0 1 4-4m2 10a2 2 0 0 1-1 1.732V17a1 1 0 1 1-2 0v-1.268A2 2 0 0 1 12 12a2 2 0 0 1 2 2" />
                        </g>
                    </svg>
                    <p>Password:</p>
                </div>
                <div class="passwordField" data-isvisible="false">
                    <input name="password" id="loginPass" type="password" placeholder="Password" maxlength="8"
                        autocomplete="off" />
                    <div class="eyeIcon open" onclick="togglePass()">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <g fill="none">
                                <path
                                    d="M24 0v24H0V0zM12.593 23.258l-.011.002l-.071.035l-.02.004l-.014-.004l-.071-.035q-.016-.005-.024.005l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427q-.004-.016-.017-.018m.265-.113l-.013.002l-.185.093l-.01.01l-.003.011l.018.43l.005.012l.008.007l.201.093q.019.005.029-.008l.004-.014l-.034-.614q-.005-.019-.02-.022m-.715.002a.02.02 0 0 0-.027.006l-.006.014l-.034.614q.001.018.017.024l.015-.002l.201-.093l.01-.008l.004-.011l.017-.43l-.003-.012l-.01-.01z" />
                                <path fill="#000000"
                                    d="M12 5c3.679 0 8.162 2.417 9.73 5.901c.146.328.27.71.27 1.099c0 .388-.123.771-.27 1.099C20.161 16.583 15.678 19 12 19s-8.162-2.417-9.73-5.901C2.124 12.77 2 12.389 2 12c0-.388.123-.771.27-1.099C3.839 7.417 8.322 5 12 5m0 3a4 4 0 1 0 0 8a4 4 0 0 0 0-8m0 2a2 2 0 1 1 0 4a2 2 0 0 1 0-4" />
                            </g>
                        </svg>
                    </div>
                    <div class="eyeIcon close" onclick="togglePass()">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <g fill="none" fill-rule="evenodd">
                                <path
                                    d="M24 0v24H0V0zM12.593 23.258l-.011.002l-.071.035l-.02.004l-.014-.004l-.071-.035q-.016-.005-.024.005l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427q-.004-.016-.017-.018m.265-.113l-.013.002l-.185.093l-.01.01l-.003.011l.018.43l.005.012l.008.007l.201.093q.019.005.029-.008l.004-.014l-.034-.614q-.005-.019-.02-.022m-.715.002a.02.02 0 0 0-.027.006l-.006.014l-.034.614q.001.018.017.024l.015-.002l.201-.093l.01-.008l.004-.011l.017-.43l-.003-.012l-.01-.01z" />
                                <path fill="#000000"
                                    d="M2.5 9a1.5 1.5 0 0 1 2.945-.404c1.947 6.502 11.158 6.503 13.109.005a1.5 1.5 0 1 1 2.877.85a10.1 10.1 0 0 1-1.623 3.236l.96.96a1.5 1.5 0 1 1-2.122 2.12l-1.01-1.01a9.6 9.6 0 0 1-1.67.915l.243.906a1.5 1.5 0 0 1-2.897.776l-.251-.935c-.705.073-1.417.073-2.122 0l-.25.935a1.5 1.5 0 0 1-2.898-.776l.242-.907a9.6 9.6 0 0 1-1.669-.914l-1.01 1.01a1.5 1.5 0 1 1-2.122-2.12l.96-.96a10.1 10.1 0 0 1-1.62-3.23A1.5 1.5 0 0 1 2.5 9" />
                            </g>
                        </svg>
                    </div>
                </div>
                <div class="forgot-password">
                    <a href="ForgotPassword.html">Forgot Password?</a>
                </div>
            </div>
            <button type="submit">Login</button>
            <div class="logoContainer"></div>
            <button type="back">
                <i class="fas fa-arrow-left"></i> Back to Homepage
            </button>
        </form>
    </div>
    </body>
    <script src="../assets/js/Login.js?v=2025012"></script>

</html>