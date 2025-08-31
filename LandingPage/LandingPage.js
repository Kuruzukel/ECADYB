// Header show/hide on scroll with smooth transitions
document.addEventListener("DOMContentLoaded", function () {
  const header = document.querySelector("header");
  if (!header) return;

  let lastScroll = 0;
  let ticking = false;
  const scrollThreshold = 50; // pixels before header hides
  
  // Add transition class
  header.style.transition = 'all 0.3s ease-in-out';
  
  // Initial check
  checkScroll();

  // Throttled scroll handler
  window.addEventListener('scroll', function() {
    if (!ticking) {
      window.requestAnimationFrame(function() {
        checkScroll();
        ticking = false;
      });
      ticking = true;
    }
  }, { passive: true });

  function checkScroll() {
    const currentScroll = window.pageYOffset || document.documentElement.scrollTop;
    
    // At top of page
    if (currentScroll <= 0) {
      header.classList.remove("header-hidden");
      header.style.transform = 'translateX(-50%)';
      lastScroll = 0;
      return;
    }

    // Scrolling down and past threshold
    if (currentScroll > lastScroll && currentScroll > scrollThreshold) {
      if (!header.classList.contains('header-hidden')) {
        header.classList.add("header-hidden");
      }
    } 
    // Scrolling up
    else if (currentScroll < lastScroll) {
      if (header.classList.contains('header-hidden')) {
        header.classList.remove("header-hidden");
      }
    }

    lastScroll = currentScroll;
  }
});

// Login dropdown functionality
const loginBtn = document.getElementById("loginDropdownBtn");
const loginMenu = document.getElementById("loginDropdownMenu");

// Add click behavior for login button
if (loginBtn) {
  loginBtn.addEventListener("click", function () {
    // Toggle clicked class for visual feedback
    this.classList.toggle("clicked");

    // Toggle dropdown menu
    loginMenu.style.display =
      loginMenu.style.display === "block" ? "none" : "block";
  });
}

document.addEventListener("click", function (e) {
  if (loginBtn && loginBtn.contains(e.target)) {
    // Click handled by the button's own event listener
  } else {
    if (loginMenu) {
      loginMenu.style.display = "none";
    }
    // Remove clicked class when clicking outside
    if (loginBtn) {
      loginBtn.classList.remove("clicked");
    }
  }
});

// Smooth scrolling functionality
document.addEventListener("DOMContentLoaded", function () {
  const navLinks = document.querySelectorAll(".center-nav a");
  const heroButtons = document.querySelectorAll(
    ".hero-btn, .hero-btn-secondary"
  );

  // Add smooth scrolling to all nav links
  navLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();

      // Remove clicked class from all nav links
      navLinks.forEach((navLink) => navLink.classList.remove("clicked"));

      // Add clicked class to the current link
      this.classList.add("clicked");

      const targetId = this.getAttribute("href");
      const targetSection = document.querySelector(targetId);

      if (targetSection) {
        targetSection.scrollIntoView({
          behavior: "smooth",
        });
      }
    });
  });

  // Add smooth scrolling to hero buttons
  heroButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();

      // Add clicked class for visual feedback
      this.classList.add("clicked");

      // Remove clicked class after animation
      setTimeout(() => {
        this.classList.remove("clicked");
      }, 300);

      const targetId = this.getAttribute("href");
      const targetSection = document.querySelector(targetId);

      if (targetSection) {
        targetSection.scrollIntoView({
          behavior: "smooth",
        });
      }
    });
  });
});

// Carousel logic using images from HTML
const track = document.getElementById("carousel-track");
let carouselImageElements = Array.from(track.querySelectorAll(".carousel-img"));
let carouselImages = carouselImageElements.map((img) => img.src);

let currentIndex = 0;

function renderImages() {
  // For infinite effect, clone last and first images
  const images = [
    carouselImages[carouselImages.length - 1], // last
    ...carouselImages,
    carouselImages[0], // first
  ];

  track.innerHTML = images
    .map(
      (src, i) =>
        `<img src="${src}" class="carousel-img" data-index="${
          i - 1
        }" draggable="false" />`
    )
    .join("");

  // Update carouselImageElements after rendering
  carouselImageElements = Array.from(track.querySelectorAll(".carousel-img"));

  // Set initial position to the first real image
  track.style.transition = "none";
  track.style.transform = `translateX(-100%)`;
  currentIndex = 0;
}

function moveToIndex(index) {
  currentIndex = index;
  track.style.transition = "transform 0.5s ease";
  track.style.transform = `translateX(-${(index + 1) * 100}%)`;
}

function handleTransitionEnd() {
  // Loop logic
  if (currentIndex < 0) {
    currentIndex = carouselImages.length - 1;
    track.style.transition = "none";
    track.style.transform = `translateX(-${(currentIndex + 1) * 100}%)`;
  } else if (currentIndex >= carouselImages.length) {
    currentIndex = 0;
    track.style.transition = "none";
    track.style.transform = `translateX(-100%)`;
  }
}

function nextImage() {
  moveToIndex(currentIndex + 1);
}

function prevImage() {
  moveToIndex(currentIndex - 1);
}

track.addEventListener("transitionend", handleTransitionEnd);

// Touch support
let startX = 0;
let isDragging = false;

track.addEventListener("touchstart", (e) => {
  startX = e.touches[0].clientX;
  isDragging = true;
});

track.addEventListener("touchmove", (e) => {
  if (!isDragging) return;
  const diff = e.touches[0].clientX - startX;
  track.style.transition = "none";
  track.style.transform = `translateX(calc(-${
    (currentIndex + 1) * 100
  }% + ${diff}px))`;
});

track.addEventListener("touchend", (e) => {
  isDragging = false;
  const diff = e.changedTouches[0].clientX - startX;
  if (diff > 50) {
    prevImage();
  } else if (diff < -50) {
    nextImage();
  } else {
    moveToIndex(currentIndex);
  }
});

// Initialize
renderImages();

// Auto-slide
let autoSlideInterval = null;
let timeoutId = null;

function startAutoSlide() {
  autoSlideInterval = setInterval(() => {
    nextImage();
  }, 3000);
}

function stopAutoSlide() {
  clearInterval(autoSlideInterval);
}

function resetCarouselAfterTimeout() {
  timeoutId = setTimeout(() => {
    stopAutoSlide();
    currentIndex = 0;
    track.style.transition = "none";
    track.style.transform = `translateX(-100%)`;
    setTimeout(() => {
      startAutoSlide();
    }, 100);
    resetCarouselAfterTimeout();
  }, 60000);
}

startAutoSlide();
resetCarouselAfterTimeout();

// 3D Carousel Functionality
document.addEventListener("DOMContentLoaded", function () {
  const carousel = document.querySelector(".carousel-3d");
  const items = document.querySelectorAll(".carousel-3d-item");
  const prevBtn = document.querySelector(".carousel-3d-prev");
  const nextBtn = document.querySelector(".carousel-3d-next");
  const pagination = document.querySelector(".carousel-3d-pagination");

  let currentIndex = 0;
  const totalItems = items.length;
  const angle = 360 / totalItems;
  let isDragging = false;
  let startPos = 0;
  let currentTranslate = 0;
  let prevTranslate = 0;
  let animationID = 0;

  // Initialize carousel items
  function initCarousel() {
    // Position items in a circle
    items.forEach((item, index) => {
      // Calculate rotation for each item
      const rotation = angle * index;
      item.style.transform = `rotateY(${rotation}deg) translateZ(500px)`;

      // Add data-index for reference
      item.setAttribute("data-index", index);
    });

    // Create pagination
    createPagination();
    updatePagination();

    // Add touch and mouse events
    setupEventListeners();
  }

  // Create pagination dots
  function createPagination() {
    for (let i = 0; i < totalItems; i++) {
      const dot = document.createElement("button");
      dot.addEventListener("click", () => goToSlide(i));
      pagination.appendChild(dot);
    }
  }

  // Update active pagination dot
  function updatePagination() {
    const dots = pagination.querySelectorAll("button");
    dots.forEach((dot, index) => {
      dot.classList.toggle("active", index === currentIndex);
    });
  }

  // Go to specific slide
  function goToSlide(index) {
    currentIndex = (index + totalItems) % totalItems;
    rotateCarousel();
    updatePagination();
  }

  // Rotate carousel to current index
  function rotateCarousel() {
    const rotation = -angle * currentIndex;
    carousel.style.transition = "transform 0.8s cubic-bezier(0.4, 0, 0.2, 1)";
    carousel.style.transform = `translateZ(-500px) rotateY(${rotation}deg)`;
  }

  // Next slide
  function nextSlide() {
    currentIndex = (currentIndex + 1) % totalItems;
    rotateCarousel();
    updatePagination();
  }

  // Previous slide
  function prevSlide() {
    currentIndex = (currentIndex - 1 + totalItems) % totalItems;
    rotateCarousel();
    updatePagination();
  }

  // Setup event listeners
  function setupEventListeners() {
    // Navigation buttons
    prevBtn.addEventListener("click", prevSlide);
    nextBtn.addEventListener("click", nextSlide);

    // Touch events
    carousel.addEventListener("touchstart", touchStart);
    carousel.addEventListener("touchend", touchEnd);
    carousel.addEventListener("touchmove", touchMove);

    // Mouse events
    carousel.addEventListener("mousedown", dragStart);
    carousel.addEventListener("mouseup", dragEnd);
    carousel.addEventListener("mouseleave", dragEnd);
    carousel.addEventListener("mousemove", drag);

    // Prevent image drag
    const images = document.querySelectorAll(".carousel-3d-item img");
    images.forEach((img) => {
      img.addEventListener("dragstart", (e) => e.preventDefault());
    });
  }

  // Touch event handlers
  function touchStart(e) {
    startPos = e.touches[0].clientX;
    isDragging = true;
    carousel.style.transition = "none";
    cancelAnimationFrame(animationID);
  }

  function touchMove(e) {
    if (!isDragging) return;
    const currentPosition = e.touches[0].clientX;
    const diff = currentPosition - startPos;
    const rotation = -angle * currentIndex + diff * 0.5;
    carousel.style.transform = `translateZ(-500px) rotateY(${rotation}deg)`;
  }

  function touchEnd() {
    if (!isDragging) return;
    isDragging = false;

    const threshold = 50;
    const touchEndX = event.changedTouches[0].clientX;
    const diff = touchEndX - startPos;

    if (Math.abs(diff) > threshold) {
      if (diff > 0) {
        prevSlide();
      } else {
        nextSlide();
      }
    } else {
      rotateCarousel();
    }
  }

  // Mouse drag event handlers
  function dragStart(e) {
    e.preventDefault();
    startPos = e.clientX;
    isDragging = true;
    carousel.style.transition = "none";
    cancelAnimationFrame(animationID);
  }

  function drag(e) {
    if (!isDragging) return;
    const currentPosition = e.clientX;
    const diff = currentPosition - startPos;
    const rotation = -angle * currentIndex + diff * 0.5;
    carousel.style.transform = `translateZ(-500px) rotateY(${rotation}deg)`;
  }

  function dragEnd() {
    if (!isDragging) return;
    isDragging = false;

    const threshold = 50;
    const diff = currentTranslate - prevTranslate;

    if (Math.abs(diff) > threshold) {
      if (diff > 0) {
        prevSlide();
      } else {
        nextSlide();
      }
    } else {
      rotateCarousel();
    }
  }

  // Auto-rotate carousel
  let autoRotate = setInterval(nextSlide, 5000);

  // Pause auto-rotation on hover
  carousel.addEventListener("mouseenter", () => {
    clearInterval(autoRotate);
  });

  carousel.addEventListener("mouseleave", () => {
    autoRotate = setInterval(nextSlide, 5000);
  });

  // Initialize the carousel
  initCarousel();
});

// Simple scroll spy functionality
window.addEventListener("scroll", () => {
  const sections = document.querySelectorAll("section, footer");
  const navLinks = document.querySelectorAll(".center-nav a");

  let current = "";

  sections.forEach((section) => {
    const sectionTop = section.offsetTop - 100; // Adjusted for header height (72px) + top margin (1rem) + extra buffer
    const sectionHeight = section.offsetHeight;
    if (
      window.scrollY >= sectionTop &&
      window.scrollY < sectionTop + sectionHeight
    ) {
      current = section.getAttribute("id");
    }
  });

  navLinks.forEach((link) => {
    link.classList.remove("active");
    if (link.getAttribute("href") === `#${current}`) {
      link.classList.add("active");
    }
  });
});

// Mobile & Desktop login buttons - simple redirect
document.addEventListener("DOMContentLoaded", function () {
  const loginBtn = document.getElementById("loginDropdownBtn");
  const mobileLoginBtn = document.getElementById("mobileLoginDropdownBtn");

  if (loginBtn) {
    loginBtn.addEventListener("click", () => {
      window.location.href = "/Public/Components/Login.php";
    });
  }

  if (mobileLoginBtn) {
    mobileLoginBtn.addEventListener("click", () => {
      window.location.href = "/Public/Components/Login.php";
    });
  }
});

if ("serviceWorker" in navigator) {
  window.addEventListener("load", () => {
    navigator.serviceWorker
      .register("./service-worker.js")
      .then((reg) => console.log("✅ Service Worker registered:", reg.scope))
      .catch((err) => console.log("❌ Service Worker failed:", err));
  });
}
