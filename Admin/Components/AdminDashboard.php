<?php
define('ADMIN_DASHBOARD_INCLUDED', true);

$basePath = strpos($_SERVER['REQUEST_URI'], '/ECADYB/') !== false ? '/ECADYB' : '';


$mongoPath = realpath(__DIR__ . '/../../Connection/Configuration/MongoConnect.php');

if (!$mongoPath || !file_exists($mongoPath)) {
    die("MongoConnect.php not found at: " . (__DIR__ . '/../../Connection/Configuration/MongoConnect.php'));
}

require $mongoPath;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$adminProfile = null;
$adminName = 'Admin';
$adminEmail = 'admin@ecadyb.edu.ph';
$adminProfileImage = 'https://ECADYB.b-cdn.net/img/Profile.png'; // Default profile image

if (isset($_SESSION['username']) && !empty($_SESSION['username'])) {
    try {
        $adminData = $adminCollection->findOne(['username' => $_SESSION['username']]);
        if ($adminData) {
            $adminName = $adminData['name'] ?? 'Admin';
            $adminEmail = $adminData['email'] ?? 'admin@ecadyb.edu.ph';
            $adminProfileImage = $adminData['profile'] ?? 'https://ECADYB.b-cdn.net/img/Profile.png';

            error_log("Admin profile fetched: " . $adminName . " | Email: " . $adminEmail);
        } else {
            // Debug: Log if no admin found (remove after testing)
            error_log("No admin found for username: " . $_SESSION['username']);
        }
    } catch (Exception $e) {
        // Use default values if query fails
        error_log("Failed to fetch admin profile: " . $e->getMessage());
    }
} else {
    error_log("No session username found");
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Graduation Gallery</title>

    <!-- Prevent caching -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="<?= $basePath ?>/Admin/assets/css/AdminDashboard.css" rel="stylesheet">

    <?php
    $currentPage = isset($_GET['page']) ? $_GET['page'] : 'student-list';
    $pageCSS = [
        'student-list' => $basePath . '/Admin/assets/css/StudentList.css',
        'add-new-student' => $basePath . '/Admin/assets/css/AddNewStudent.css',
        'create-announcement' => $basePath . '/Admin/assets/css/CreateAnnouncement.css',
        'event-calendar' => $basePath . '/Admin/assets/css/EventCalendar.css',
        'batchupload' => $basePath . '/Admin/assets/css/BatchUpload.css',
        'themes' => $basePath . '/Admin/assets/css/Themes.css',
        'template' => $basePath . '/Admin/assets/css/BatchTemplates.css',
        'changepassword' => $basePath . '/Admin/assets/css/ChangePassword.css',
    ];

    if (isset($pageCSS[$currentPage])) {
        echo '<link rel="stylesheet" href="' . $pageCSS[$currentPage] . '">';
    }
    ?>

    <script>
        (function() {
            const themes = {
                "Light Mode": {
                    "--primary-bg": "#ffffff",
                    "--header-bg": "#94a3b8",
                    "--accent": "#fcda15",
                    "--section-bg": "#f8fafc",
                    "--section-header": "#cbd5e1",
                    "--body-bg": "#64748b",
                    "--sidebar-bg": "#94a3b8",
                    "--content-bg": "#ffffff",
                    "--menu-bg-active": "#cbd5e1",
                    "--menu-border-active": "#64748b",
                    "--menu-hover-bg": "#e2e8f0",
                },
                "Dark Mode": {
                    "--primary-bg": "#0f172a",
                    "--header-bg": "#1e293b",
                    "--accent": "#fcda15",
                    "--section-bg": "#334155",
                    "--section-header": "#475569",
                    "--body-bg": "#0f172a",
                    "--sidebar-bg": "#1e293b",
                    "--content-bg": "#334155",
                    "--menu-bg-active": "#475569",
                    "--menu-border-active": "#334155",
                    "--menu-hover-bg": "#64748b",
                },
                "Theme 1": {
                    "--primary-bg": "#470a0a",
                    "--header-bg": "#b21c0e",
                    "--accent": "#fcda15",
                    "--section-bg": "#bc4f5e",
                    "--section-header": "#cb5382",
                    "--body-bg": "#470a0a",
                    "--sidebar-bg": "#b21c0e",
                    "--content-bg": "#bc4f5e",
                    "--menu-bg-active": "#cb5382",
                    "--menu-border-active": "#fff176",
                    "--menu-hover-bg": "#cb5382",
                },
                "Theme 2": {
                    "--primary-bg": "#12086F",
                    "--header-bg": "#2B35AF",
                    "--accent": "#fcda15",
                    "--section-bg": "#4895EF",
                    "--section-header": "#4CC9F0",
                    "--body-bg": "#12086F",
                    "--sidebar-bg": "#2B35AF",
                    "--content-bg": "#4895EF",
                    "--menu-bg-active": "#4CC9F0",
                    "--menu-border-active": "#ffffff",
                    "--menu-hover-bg": "#4361EE",
                },
                "Theme 3": {
                    "--primary-bg": "#0d381e",
                    "--header-bg": "#164f2c",
                    "--accent": "#fcda15",
                    "--section-bg": "#2a834d",
                    "--section-header": "#349e5e",
                    "--body-bg": "#0d381e",
                    "--sidebar-bg": "#164f2c",
                    "--content-bg": "#2a834d",
                    "--menu-bg-active": "#349e5e",
                    "--menu-border-active": "#ffffff",
                    "--menu-hover-bg": "#1f693c",
                },
                "Theme 4": {
                    "--primary-bg": "#281E18",
                    "--header-bg": "#572D0C",
                    "--accent": "#fcda15",
                    "--section-bg": "#E3B76A",
                    "--section-header": "#9D9C75",
                    "--body-bg": "#281E18",
                    "--sidebar-bg": "#572D0C",
                    "--content-bg": "#E3B76A",
                    "--menu-bg-active": "#9D9C75",
                    "--menu-border-active": "#ffffff",
                    "--menu-hover-bg": "#C78E3A",
                },
                "Default": {
                    "--primary-bg": "#112d4e",
                    "--header-bg": "#0c27be",
                    "--accent": "#fcda15",
                    "--section-bg": "#34495e",
                    "--section-header": "#217ff7",
                    "--body-bg": "#000042",
                    "--sidebar-bg": "#0c27be",
                    "--content-bg": "#112d4e",
                    "--menu-bg-active": "#000042",
                    "--menu-border-active": "#fcda15",
                    "--menu-hover-bg": "#1c1c84",
                }
            };

            const savedTheme = localStorage.getItem("dashboard-theme") || "Default";
            const selectedTheme = themes[savedTheme] || themes["Default"];
            const root = document.documentElement;

            for (const [varName, color] of Object.entries(selectedTheme)) {
                root.style.setProperty(varName, color);
            }

            if (savedTheme === "Light Mode") {
                document.documentElement.classList.add("theme-light-mode");
            } else if (savedTheme === "Dark Mode") {
                document.documentElement.classList.add("theme-dark-mode");
            }

            const savedLogoUrl = localStorage.getItem("admin-logo-url");
            if (savedLogoUrl) {
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', function() {
                        const adminLogo = document.getElementById("admin-logo");
                        if (adminLogo) {
                            adminLogo.src = savedLogoUrl;
                        }
                    });
                } else {
                    const adminLogo = document.getElementById("admin-logo");
                    if (adminLogo) {
                        adminLogo.src = savedLogoUrl;
                    }
                }
            }
        })();
    </script>
</head>

<body>

    <main>
        <div class="sidebar">
            <img src="https://ECADYB.b-cdn.net/img/ADMINGRALLERYLOGO.png" alt="Logo" class="logoadmin" id="admin-logo">
            <div class="line"></div>

            <div class="menu-items" style="font-family: 'Oxygen', sans-serif;">
                <a class="tab" id="addash-tab" onclick="toggleSubmenu('dashboard-submenu')">
                    <i class="fas fa-home"></i> <span>Dashboard</span>
                    <span class="chevron"><i class="fas fa-chevron-down"></i></span>
                </a>

                <div id="dashboard-submenu" class="submenu">

                    <a href="<?= $basePath ?>/Admin?page=student-list" class="tab sub-tab">
                        </i> Student List
                    </a>

                    <a href="<?= $basePath ?>/Admin?page=add-new-student" class="tab sub-tab">
                        </i> Add New Student
                    </a>

                </div>

                <a class="tab" id="announcement-tab" onclick="toggleSubmenu('announcement-submenu')">
                    <i class="fas fa-bullhorn"></i> Announcement
                    <span class="chevron"><i class="fas fa-chevron-down"></i></span>
                </a>

                <div id="announcement-submenu" class="submenu">

                    <a href="<?= $basePath ?>/Admin?page=create-announcement" class="tab sub-tab">
                        </i> Create Announcement
                    </a>

                    <a href="<?= $basePath ?>/Admin?page=event-calendar" class="tab sub-tab">
                        </i> Event Calendar
                    </a>

                </div>

                <a class="tab" id="yearbook-tab" onclick="toggleSubmenu('yearbook-submenu')">
                    <i class="fas fa-book"></i> Year Book
                    <span class="chevron"><i class="fas fa-chevron-down"></i></span>
                </a>

                <div id="yearbook-submenu" class="submenu">
                    <a href="<?= $basePath ?>/Admin?page=maritime" class="tab sub-tab">
                        </i>Maritime Education
                    </a>

                    <a href="<?= $basePath ?>/Admin?page=criminology" class="tab sub-tab">
                        </i>College of Criminology
                    </a>

                    <a href="<?= $basePath ?>/Admin?page=tourism" class="tab sub-tab">
                        </i>Tourism Management
                    </a>

                    <a href="<?= $basePath ?>/Admin?page=education" class="tab sub-tab">
                        </i>College of Education
                    </a>

                    <a href="<?= $basePath ?>/Admin?page=nursing" class="tab sub-tab">
                        </i>College of Nursing
                    </a>

                    <a href="<?= $basePath ?>/Admin?page=informationsys" class="tab sub-tab">
                        </i>Information System
                    </a>

                    <a href="<?= $basePath ?>/Admin?page=businessad" class="tab sub-tab">
                        </i>Business Administration
                    </a>
                </div>

                <a href="<?= $basePath ?>/Admin?page=batchupload" class="tab" id="batchupload-tab"
                    onclick="setTabActive('batchupload-tab');">
                    <i class="fas fa-cloud-upload-alt"></i> Batch Upload
                </a>

                <a class="tab" id="customize-tab" onclick="toggleSubmenu('customize-submenu')">
                    <i class="fas fa-sliders-h"></i> Customize
                    <span class="chevron"><i class="fas fa-chevron-down"></i></span>

                </a>

                <div id="customize-submenu" class="submenu">
                    <a href="<?= $basePath ?>/Admin?page=themes" class="tab sub-tab">
                        </i>Themes
                    </a>

                    <a href="<?= $basePath ?>/Admin?page=template" class="tab sub-tab">
                        </i>Batch Templates
                    </a>

                </div>

                <a href="<?= $basePath ?>/Admin?page=changepassword" class="tab" id="changepassword-tab"
                    onclick="setTabActive('changepassword-tab');">
                    <i class="fas fa-key"></i> Change password
                </a>

                <a href="#" class="tab" id="logout-tab">
                    <i class="fas fa-sign-out-alt"></i> Log Out
                </a>
                <!-- Logout Confirmation Modal -->
                <!--
<div class="modal-overlay" id="modal-overlay">
    <div class="modal" style="font-family: Arial, sans-serif;">
        <h3>Are you sure you want to logout?</h3>
        <div class="modal-buttons">
            <button type="button" class="modal-btn confirm" id="confirm-btn">Yes, Logout</button>
            <button type="button" class="modal-btn cancel" id="cancel-btn">Cancel</button>
        </div>
    </div>
</div>
-->

            </div>
        </div>

        <div class="scroll-container" id="scrollContainer">
            <div class="contents" id="content">
                <header>

                    <div class="menu-container">
                        <div class="menu-btn">
                            <div class="hamburger-menu-ico">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <path d="M3 18v-2h18v2zm0-5v-2h18v2zm0-5V6h18v2z" fill="#F8FAFC" />
                                </svg>

                            </div>
                            <div class="close-ico hidden">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                    <path
                                        d="M6.4 19L5 17.6l5.6-5.6L5 6.4L6.4 5l5.6 5.6L17.6 5L19 6.4L13.4 12l5.6 5.6l-1.4 1.4l-5.6-5.6z"
                                        fill="#F8FAFC" />
                                </svg>
                            </div>
                        </div>
                        <div class="search-wrapper">
                            <div class="search-container">
                                <input type="text" id="search-input" name="search-input" class="search-input"
                                    placeholder="Search student ID or name..." autocomplete="off" />
                                <button class="search-button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <div class="search-suggestions" id="search-suggestions"></div>
                        </div>

                        <div class="admin-profile-container">
                            <img src="<?= htmlspecialchars($adminProfileImage) ?>" alt="Admin Profile"
                                class="admin-profile-img">
                            <div class="admin-dropdown">
                                <div class="admin-dropdown-header">
                                    <i class="fas fa-user-circle"></i>
                                    <span>Account Details</span>
                                </div>
                                <div class="admin-dropdown-item">
                                    <i class="fas fa-user"></i>
                                    <div class="admin-dropdown-content">
                                        <span class="admin-dropdown-label">Name</span>
                                        <span class="admin-dropdown-value"><?= htmlspecialchars($adminName) ?></span>
                                    </div>
                                </div>
                                <div class="admin-dropdown-item">
                                    <i class="fas fa-envelope"></i>
                                    <div class="admin-dropdown-content">
                                        <span class="admin-dropdown-label">Email</span>
                                        <span class="admin-dropdown-value"><?= htmlspecialchars($adminEmail) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Debug info (remove after testing) -->
                        <!-- Name: <?= $adminName ?>, Email: <?= $adminEmail ?> -->

                    </div>
                </header>

                <?php
                $page = isset($_GET['page']) ? $_GET['page'] : 'student-list';
                switch ($page) {

                    case 'student-list':
                        include(__DIR__ . '/StudentList.php');
                        break;
                    case 'add-new-student':
                        include(__DIR__ . '/AddNewStudent.php');
                        break;
                    case 'create-announcement':
                        include(__DIR__ . '/CreateAnnouncement.php');
                        break;
                    case 'event-calendar':
                        include(__DIR__ . '/EventCalendar.php');
                        break;
                    case 'batchupload':
                        include(__DIR__ . '/BatchUpload.php');
                        break;
                    case 'themes':
                        include(__DIR__ . '/Themes.php');
                        break;
                    case 'template':
                        include(__DIR__ . '/BatchTemplates.php');
                        break;
                    case 'changepassword':
                        include(__DIR__ . '/ChangePassword.php');
                        break;
                    case 'maritime':
                        include(__DIR__ . '/../Departments/Maritime.php');
                        break;
                    case 'criminology':
                        include(__DIR__ . '/../Departments/Criminology.php');
                        break;
                    case 'tourism':
                        include(__DIR__ . '/../Departments/Tourism.php');
                        break;
                    case 'education':
                        include(__DIR__ . '/../Departments/Education.php');
                        break;
                    case 'nursing':
                        include(__DIR__ . '/../Departments/Nursing.php');
                        break;
                    case 'informationsys':
                        include(__DIR__ . '/../Departments/InformationSystem.php');
                        break;
                    case 'businessad':
                        include(__DIR__ . '/../Departments/BusinessAdministration.php');
                        break;
                    default:
                        include(__DIR__ . '/StudentList.php');
                        break;
                }
                ?>

            </div>

    </main>
    <script src="<?= $basePath ?>/Admin/assets/js/AdminDashboard.js?v=<?php echo microtime(true); ?>"></script>

    <?php
    $cacheBuster = microtime(true);
    $pageJS = [
        'student-list' => $basePath . '/Admin/assets/js/StudentList.js?v=' . $cacheBuster,
        'add-new-student' => $basePath . '/Admin/assets/js/AddNewStudent.js?v=' . $cacheBuster,
        'create-announcement' => $basePath . '/Admin/assets/js/CreateAnnouncement.js',
        'event-calendar' => $basePath . '/Admin/assets/js/EventCalendar.js',
        'batchupload' => $basePath . '/Admin/assets/js/BatchUpload.js?v=' . $cacheBuster,
        'themes' => $basePath . '/Admin/assets/js/Themes.js',
        'template' => $basePath . '/Admin/assets/js/BatchTemplates.js',
        'changepassword' => $basePath . '/Admin/assets/js/ChangePassword.js',
    ];

    if (isset($pageJS[$currentPage])) {
        echo '<script src="' . $pageJS[$currentPage] . '"></script>';
    }
    ?>
</body>

</html>