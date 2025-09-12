

// Hamburger menu functionality
document.addEventListener("DOMContentLoaded", function () {
  const hamburgerMenu = document.getElementById("hamburgerMenu");
  const centerNav = document.querySelector(".center-nav");

  if (!hamburgerMenu || !centerNav) return;

  // Toggle mobile menu
  hamburgerMenu.addEventListener("click", function (e) {
    e.stopPropagation();
    this.classList.toggle("active");
    centerNav.classList.toggle("mobile-active");
  });

  // Close mobile menu when clicking on a link
  const navLinks = centerNav.querySelectorAll("a");
  navLinks.forEach((link) => {
    link.addEventListener("click", function () {
      hamburgerMenu.classList.remove("active");
      centerNav.classList.remove("mobile-active");
    });
  });

  // Close mobile menu when clicking outside
  document.addEventListener("click", function (event) {
    if (
      !hamburgerMenu.contains(event.target) &&
      !centerNav.contains(event.target)
    ) {
      hamburgerMenu.classList.remove("active");
      centerNav.classList.remove("mobile-active");
    }
  });
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

// Mobile & Desktop login buttons with modern page transition
document.addEventListener("DOMContentLoaded", function () {
  const loginBtn = document.getElementById("loginDropdownBtn");
  const mobileLoginBtn = document.getElementById("mobileLoginDropdownBtn");

  function handleLoginClick(e) {
    e.preventDefault();
    e.stopPropagation();
    
    // Add modern page transition class to body
    document.body.classList.add('page-transition-out');
    
    // Redirect after animation completes
    setTimeout(() => {
      window.location.href = "../Public/Components/Login.php";
    }, 1000); // Match this with CSS animation duration
  }

  if (loginBtn) {
    // Remove any existing click handlers to avoid duplicates
    loginBtn.replaceWith(loginBtn.cloneNode(true));
    const newLoginBtn = document.getElementById("loginDropdownBtn");
    newLoginBtn.addEventListener("click", handleLoginClick);
    
    // Also update the inline onclick handler
    newLoginBtn.onclick = handleLoginClick;
  }

  if (mobileLoginBtn) {
    // Remove any existing click handlers to avoid duplicates
    mobileLoginBtn.replaceWith(mobileLoginBtn.cloneNode(true));
    const newMobileBtn = document.getElementById("mobileLoginDropdownBtn");
    newMobileBtn.addEventListener("click", handleLoginClick);
    
    // Also update the inline onclick handler
    newMobileBtn.onclick = handleLoginClick;
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

// Yearbook Slider Functionality
document.addEventListener("DOMContentLoaded", function () {
  const slider = document.querySelector('.yearbook-slider');
  
  if (!slider) return;
  
  function activate(e) {
    const items = document.querySelectorAll('.yearbook-item');
    
    if (e.target.matches('.yearbook-next') && items.length > 0) {
      slider.append(items[0]);
    }
    
    if (e.target.matches('.yearbook-prev') && items.length > 0) {
      slider.prepend(items[items.length - 1]);
    }
  }
  
  document.addEventListener('click', activate, false);
  
  // Auto-rotate functionality removed per user request
});

// Yearbook Background Display Functionality
function showYearbookBackground(clickedItem, imageUrl) {
  const sliderMain = document.querySelector('.yearbook-slider-main');
  const allItems = document.querySelectorAll('.yearbook-item');
  
  if (!sliderMain) return;
  
  // Remove active class from all items
  allItems.forEach(item => item.classList.remove('active'));
  
  // Add active class to clicked item
  clickedItem.classList.add('active');
  
  // Set background image and show full background view
  sliderMain.style.backgroundImage = `url('${imageUrl}')`;
  sliderMain.classList.add('show-yearbook-bg');
  
  // Auto-rotation functionality removed
}

function closeYearbookView() {
  const sliderMain = document.querySelector('.yearbook-slider-main');
  const allItems = document.querySelectorAll('.yearbook-item');
  
  if (!sliderMain) return;
  
  // Remove background and full view class
  sliderMain.style.backgroundImage = '';
  sliderMain.classList.remove('show-yearbook-bg');
  
  // Remove active class from all items
  allItems.forEach(item => item.classList.remove('active'));
  
  // Auto-rotation functionality removed
}

// Keyboard support for closing yearbook view
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    const sliderMain = document.querySelector('.yearbook-slider-main');
    if (sliderMain && sliderMain.classList.contains('show-yearbook-bg')) {
      closeYearbookView();
    }
  }
});

// Click outside to close yearbook view
document.addEventListener('click', function(e) {
  const sliderMain = document.querySelector('.yearbook-slider-main');
  const closeBtn = document.querySelector('.yearbook-close-btn');
  const items = document.querySelectorAll('.yearbook-item');
  
  if (sliderMain && sliderMain.classList.contains('show-yearbook-bg')) {
    // Check if click is outside yearbook items and not on close button
    const clickedItem = Array.from(items).some(item => item.contains(e.target));
    const clickedCloseBtn = closeBtn && closeBtn.contains(e.target);
    
    if (!clickedItem && !clickedCloseBtn && sliderMain.contains(e.target)) {
      closeYearbookView();
    }
  }
});
