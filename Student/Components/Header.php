<?php
// Get student information from session
$studentId = $_SESSION['student_id'] ?? '';
$studentName = $_SESSION['name'] ?? '';
$studentDepartment = $_SESSION['department'] ?? '';
$studentSection = $_SESSION['section'] ?? '';
$studentProfilePhoto = $_SESSION['profile_photo'] ?? '';

// Student photo is already fetched in StudentDashboard.php and stored in session
// No need to fetch it again here to avoid duplicate MongoDB connections
?>
<header>
  <div class="logo">
    <img
      src="https://ECADYB.b-cdn.net/img/ADMINGRALLERYLOGO.png"
      alt="Logo"
      class="logo-img"
    />
    <span>GRADUATION GALLERY</span>
  </div>
  <nav class="center-nav">
    <a href="/ECADYB/Student/Components/StudentDashboard.php">Home</a>
    <a href="/ECADYB/Student/Components/About.php">About</a>
    <a href="/ECADYB/Student/Components/Yearbook.php">Yearbooks</a>
    <a href="/ECADYB/Student/Components/Memories.php">Memories</a>
    <div class="mobile-login-dropdown">
      <button
        class="mobile-login-btn"
        id="mobileLoginDropdownBtn"
        onclick="window.location.href='/ECADYB/login'"
      >
        Log In
      </button>
    </div>
  </nav>
  <div class="notification-icon-container">
    <i class="fa-solid fa-bell notification-icon" id="notificationIcon"></i>
    <span class="notification-badge" id="notificationBadge">3</span>
    <div class="notification-dropdown" id="notificationDropdown">
      <div class="notification-header">
        <h3>Notifications</h3>
        <button class="mark-all-read" onclick="markAllAsRead()">
          Mark all as read
        </button>
      </div>
      <div class="notification-list" id="notificationList">
        <div class="notification-loading">
          <i class="fa-solid fa-spinner fa-spin"></i>
          <span>Loading announcements...</span>
        </div>
      </div>
    </div>
  </div>

  <div class="profile-dropdown">
    <img
      src="<?php echo htmlspecialchars($studentProfilePhoto ?: 'https://ECADYB.b-cdn.net/img/Profile.png'); ?>"
      alt="Profile"
      class="profile-icon"
      id="profileIcon"
    />
    <div class="dropdown-menu" id="profileDropdownMenu">
      <button
        class="dropdown-item"
        onclick="window.location.href='/ECADYB/Student/Components/ChangePassword.php'"
      >
        Change Password
      </button>
      <button class="dropdown-item" onclick="editProfile()">
        Edit Milestone
      </button>
      <button class="dropdown-item" onclick="logout()">Logout</button>
    </div>
  </div>

  <button class="hamburger-menu" id="hamburgerMenu">
    <svg
      class="hamburger-icon"
      viewBox="0 0 24 24"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path
        class="hamburger-line line-1"
        d="M3 6h18"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
      />
      <path
        class="hamburger-line line-2"
        d="M3 12h18"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
      />
      <path
        class="hamburger-line line-3"
        d="M3 18h18"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
      />
      <path
        class="close-line close-1"
        d="M18 6L6 18"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
      />
      <path
        class="close-line close-2"
        d="M6 6L18 18"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
      />
    </svg>
  </button>
</header>

