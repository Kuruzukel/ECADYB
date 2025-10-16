<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Captured Moments - Graduation Gallery</title>

    <meta property="fb:app_id" content="1767810860531321" />
    <meta property="og:locale" content="en_US" />
    <meta
      property="og:title"
      content="Captured Moments - Graduation Gallery"
    />
    <meta
      property="og:description"
      content="View captured moments from our journey together."
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

    <meta name="twitter:card" content="summary_large_image" />
    <meta
      name="twitter:title"
      content="Captured Moments - Graduation Gallery"
    />
    <meta
      name="twitter:description"
      content="View captured moments from our journey together."
    />
    <meta
      name="twitter:image"
      content="https://ECADYB.b-cdn.net/img/PREVIEWLOGO.png"
    />
    <meta
      name="twitter:image:alt"
      content="Graduation Gallery Preview Logo"
    />

    <link
      rel="icon"
      href="https://ECADYB.b-cdn.net/img/PREVIEWLOGO.png"
      type="image/png"
    />

    <link rel="stylesheet" href="/ECADYB/Student/assets/css/StudentDashboard.css" />

    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
    />
  </head>

  <body>
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
          <div class="notification-list">
            <div class="notification-item unread">
              <i class="fa-solid fa-info-circle notification-item-icon"></i>
              <div class="notification-content">
                <p class="notification-text">Your profile has been updated successfully</p>
                <span class="notification-time">2 hours ago</span>
              </div>
            </div>
            <div class="notification-item unread">
              <i class="fa-solid fa-calendar notification-item-icon"></i>
              <div class="notification-content">
                <p class="notification-text">New event added to the yearbook</p>
                <span class="notification-time">5 hours ago</span>
              </div>
            </div>
            <div class="notification-item unread">
              <i class="fa-solid fa-image notification-item-icon"></i>
              <div class="notification-content">
                <p class="notification-text">New photos have been added to your gallery</p>
                <span class="notification-time">1 day ago</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="profile-dropdown">
        <img
          src="https://ECADYB.b-cdn.net/Top Management Photos/Batch Template 1/ERONBAKLA.jpg"
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

    <section class="carousel-section" id="captured-moments">
      <div class="carousel-background"></div>
      <h2 class="section-title">Captured Moments</h2>
      <p class="carousel-subtitle">
        A collection of unforgettable memories from our journey together.
      </p>
      <div class="carousel-container">
        <div class="carousel-track" id="carousel-track">
          <img
            src="https://ECADYB.b-cdn.net/img/CAROUSEL/sample1.jpg"
            class="carousel-img"
          />
          <img
            src="https://ECADYB.b-cdn.net/img/CAROUSEL/sample2.jpg"
            class="carousel-img"
          />
          <img
            src="https://ECADYB.b-cdn.net/img/CAROUSEL/sample3.jpg"
            class="carousel-img"
          />
          <img
            src="https://ECADYB.b-cdn.net/img/CAROUSEL/sample4.jpg"
            class="carousel-img"
          />
          <img
            src="https://ECADYB.b-cdn.net/img/CAROUSEL/sample5.jpg"
            class="carousel-img"
          />
          <img
            src="https://ECADYB.b-cdn.net/img/CAROUSEL/sample6.jpg"
            class="carousel-img"
          />
          <img
            src="https://ECADYB.b-cdn.net/img/CAROUSEL/sample7.jpg"
            class="carousel-img"
          />
          <img
            src="https://ECADYB.b-cdn.net/img/CAROUSEL/sample8.jpg"
            class="carousel-img"
          />
          <img
            src="https://ECADYB.b-cdn.net/img/CAROUSEL/sample9.jpg"
            class="carousel-img"
          />
          <img
            src="https://ECADYB.b-cdn.net/img/CAROUSEL/sample10.jpg"
            class="carousel-img"
          />
          <img
            src="https://ECADYB.b-cdn.net/img/CAROUSEL/sample11.jpg"
            class="carousel-img"
          />
          <img
            src="https://ECADYB.b-cdn.net/img/CAROUSEL/sample12.jpg"
            class="carousel-img"
          />
          <img
            src="https://ECADYB.b-cdn.net/img/CAROUSEL/sample13.jpg"
            class="carousel-img"
          />
          <img
            src="https://ECADYB.b-cdn.net/img/CAROUSEL/sample14.jpg"
            class="carousel-img"
          />
          <img
            src="https://ECADYB.b-cdn.net/img/CAROUSEL/sample15.jpg"
            class="carousel-img"
          />
        </div>
      </div>
      <div class="main-hero-lower-curl">
        <svg
          viewBox="0 0 1440 120"
          fill="none"
          xmlns="http://www.w3.org/2000/svg"
          preserveAspectRatio="none"
          style="display: block; width: 100%; height: 60px"
        >
          <path
            d="M0,60 Q180,100 360,60 T720,60 T1080,60 T1440,60 L1440,120 L0,120 Z"
            fill="#1a237e"
            opacity="0.4"
          />
          <path
            d="M0,80 Q180,40 360,80 T720,80 T1080,80 T1440,80 L1440,120 L0,120 Z"
            fill="#112d4e"
            opacity="0.7"
          />
          <path
            d="M0,100 Q180,60 360,100 T720,100 T1080,100 T1440,100 L1440,120 L0,120 Z"
            fill="#021326"
          />
        </svg>
      </div>
    </section>

    <footer class="footer-section" id="footer">
      <div class="footer-inner-container">
        <div class="footer-logo-container left">
          <img
            src="https://ECADYB.b-cdn.net/img/ECALOGO.png"
            alt="ECA Logo"
            class="footer-logo-img footer-logo-img-left"
          />
        </div>
        <div class="footer-content">
          <div class="footer-contact-info">
            <div class="footer-contact-item">
              <div class="footer-contact-desc">
                <i
                  class="fa-solid fa-location-dot"
                  style="margin-right: 8px; color: #bfcfff"
                ></i>
                Suclayin Arayat, Pampanga, Arayat, Philippines
              </div>
            </div>
            <div class="footer-contact-item">
              <div class="footer-contact-desc">
                <i
                  class="fa-solid fa-phone"
                  style="margin-right: 8px; color: #bfcfff"
                ></i>
                0969 516 6181
              </div>
            </div>
            <div class="footer-contact-item">
              <div class="footer-contact-desc">
                <i
                  class="fa-solid fa-envelope"
                  style="margin-right: 8px; color: #bfcfff"
                ></i>
                exact.colleges@yahoo.com
              </div>
            </div>
            <div class="footer-contact-item">
              <div class="footer-contact-desc">
                <i
                  class="fa-brands fa-facebook"
                  style="margin-right: 8px; color: #bfcfff"
                ></i>
                <a
                  href="https://www.facebook.com/ExactCollegesAsia"
                  target="_blank"
                  style="color: inherit; text-decoration: none"
                  >facebook.com/ExactCollegesAsia</a
                >
              </div>
            </div>
          </div>
          <div class="footer-copy">&copy; 2025 TEAM NOVA SPIRE.</div>
        </div>
        <div class="footer-logo-container right">
          <img
            src="https://ECADYB.b-cdn.net/img/GRALLERYLOGO4.0.png"
            alt="Grallery Logo"
            class="footer-logo-img footer-logo-img-right"
          />
        </div>
      </div>
    </footer>
    <script src="/ECADYB/Student/assets/js/StudentDashboard.js"></script>
  </body>
</html>

