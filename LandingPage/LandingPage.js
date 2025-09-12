

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

  // Early return if 3D carousel elements don't exist
  if (!carousel || !items.length || !pagination) {
    console.log('3D carousel elements not found - skipping 3D carousel initialization');
    return;
  }

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
    try {
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
    } catch (error) {
      console.error('Error initializing 3D carousel:', error);
    }
  }

  // Create pagination dots
  function createPagination() {
    if (!pagination) return;
    
    try {
      for (let i = 0; i < totalItems; i++) {
        const dot = document.createElement("button");
        dot.addEventListener("click", () => goToSlide(i));
        pagination.appendChild(dot);
      }
    } catch (error) {
      console.error('Error creating pagination:', error);
    }
  }

  // Update active pagination dot
  function updatePagination() {
    if (!pagination) return;
    
    try {
      const dots = pagination.querySelectorAll("button");
      dots.forEach((dot, index) => {
        dot.classList.toggle("active", index === currentIndex);
      });
    } catch (error) {
      console.error('Error updating pagination:', error);
    }
  }

  // Go to specific slide
  function goToSlide(index) {
    try {
      currentIndex = (index + totalItems) % totalItems;
      rotateCarousel();
      updatePagination();
    } catch (error) {
      console.error('Error going to slide:', error);
    }
  }

  // Rotate carousel to current index
  function rotateCarousel() {
    if (!carousel) {
      console.warn('Carousel element not found');
      return;
    }
    
    try {
      const rotation = -angle * currentIndex;
      carousel.style.transition = "transform 0.8s cubic-bezier(0.4, 0, 0.2, 1)";
      carousel.style.transform = `translateZ(-500px) rotateY(${rotation}deg)`;
    } catch (error) {
      console.error('Error rotating carousel:', error);
    }
  }

  // Next slide
  function nextSlide() {
    try {
      currentIndex = (currentIndex + 1) % totalItems;
      rotateCarousel();
      updatePagination();
    } catch (error) {
      console.error('Error moving to next slide:', error);
    }
  }

  // Previous slide
  function prevSlide() {
    try {
      currentIndex = (currentIndex - 1 + totalItems) % totalItems;
      rotateCarousel();
      updatePagination();
    } catch (error) {
      console.error('Error moving to previous slide:', error);
    }
  }

  // Setup event listeners
  function setupEventListeners() {
    try {
      // Navigation buttons (only if they exist)
      if (prevBtn) {
        prevBtn.addEventListener("click", prevSlide);
      }
      if (nextBtn) {
        nextBtn.addEventListener("click", nextSlide);
      }

      // Touch and mouse events (only if carousel exists)
      if (carousel) {
        // Touch events
        carousel.addEventListener("touchstart", touchStart);
        carousel.addEventListener("touchend", touchEnd);
        carousel.addEventListener("touchmove", touchMove);

        // Mouse events
        carousel.addEventListener("mousedown", dragStart);
        carousel.addEventListener("mouseup", dragEnd);
        carousel.addEventListener("mouseleave", dragEnd);
        carousel.addEventListener("mousemove", drag);
      }

      // Prevent image drag
      const images = document.querySelectorAll(".carousel-3d-item img");
      images.forEach((img) => {
        img.addEventListener("dragstart", (e) => e.preventDefault());
      });
    } catch (error) {
      console.error('Error setting up event listeners:', error);
    }
  }

  // Touch event handlers
  function touchStart(e) {
    if (!carousel) return;
    
    try {
      startPos = e.touches[0].clientX;
      isDragging = true;
      carousel.style.transition = "none";
      cancelAnimationFrame(animationID);
    } catch (error) {
      console.error('Error in touchStart:', error);
    }
  }

  function touchMove(e) {
    if (!isDragging || !carousel) return;
    
    try {
      const currentPosition = e.touches[0].clientX;
      const diff = currentPosition - startPos;
      const rotation = -angle * currentIndex + diff * 0.5;
      carousel.style.transform = `translateZ(-500px) rotateY(${rotation}deg)`;
    } catch (error) {
      console.error('Error in touchMove:', error);
    }
  }

  function touchEnd() {
    if (!isDragging) return;
    
    try {
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
    } catch (error) {
      console.error('Error in touchEnd:', error);
    }
  }

  // Mouse drag event handlers
  function dragStart(e) {
    if (!carousel) return;
    
    try {
      e.preventDefault();
      startPos = e.clientX;
      isDragging = true;
      carousel.style.transition = "none";
      cancelAnimationFrame(animationID);
    } catch (error) {
      console.error('Error in dragStart:', error);
    }
  }

  function drag(e) {
    if (!isDragging || !carousel) return;
    
    try {
      const currentPosition = e.clientX;
      const diff = currentPosition - startPos;
      const rotation = -angle * currentIndex + diff * 0.5;
      carousel.style.transform = `translateZ(-500px) rotateY(${rotation}deg)`;
    } catch (error) {
      console.error('Error in drag:', error);
    }
  }

  function dragEnd() {
    if (!isDragging) return;
    
    try {
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
    } catch (error) {
      console.error('Error in dragEnd:', error);
    }
  }

  // Auto-rotate carousel (only if carousel exists)
  let autoRotate = null;
  if (carousel) {
    autoRotate = setInterval(nextSlide, 5000);

    // Pause auto-rotation on hover
    carousel.addEventListener("mouseenter", () => {
      if (autoRotate) {
        clearInterval(autoRotate);
      }
    });

    carousel.addEventListener("mouseleave", () => {
      if (autoRotate) {
        clearInterval(autoRotate);
      }
      autoRotate = setInterval(nextSlide, 5000);
    });
  }

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
  // Initialize yearbook items with proper event handling
  setTimeout(() => {
    initializeYearbookItems();
  }, 100); // Small delay to ensure DOM is fully loaded
});

// Initialize yearbook items with event delegation
function initializeYearbookItems() {
  const itemsContainer = document.querySelector('.yearbook-items-container');
  const sliderMain = document.querySelector('.yearbook-slider-main');
  
  if (!itemsContainer || !sliderMain) {
    console.warn('Required yearbook elements not found, retrying...');
    // Retry after a short delay if elements aren't ready
    setTimeout(initializeYearbookItems, 200);
    return;
  }
  
  // Remove any existing event listeners to prevent duplicates
  const existingListener = itemsContainer.getAttribute('data-listener');
  if (existingListener) {
    return; // Already initialized
  }
  
  // Mark as initialized
  itemsContainer.setAttribute('data-listener', 'true');
  
  // Add stable hover handling to prevent jiggling
  const yearBookItems = document.querySelectorAll('.yearbook-item');
  yearBookItems.forEach(item => {
    if (item) {
      // Remove any existing transform to ensure clean state
      item.style.transform = '';
      
      // Ensure proper CSS properties for stable hover
      item.style.willChange = 'transform';
      item.style.backfaceVisibility = 'hidden';
    }
  });
  
  console.log('Yearbook items initialized successfully - hover should work smoothly now');
  
  // The onclick handlers in HTML will handle the click events
  // This just ensures the containers are properly set up for CSS hover
}

// Yearbook Background Display Functionality
function showYearbookBackground(clickedItem, imageUrl) {
  try {
    if (!clickedItem || !imageUrl) {
      console.error('Missing required parameters for showYearbookBackground');
      return;
    }
    
    const sliderMain = document.querySelector('.yearbook-slider-main');
    if (!sliderMain) {
      console.error('Yearbook slider main container not found');
      return;
    }
    
    const allItems = document.querySelectorAll('.yearbook-item');
    
    // Remove active class from all items
    allItems.forEach(item => {
      if (item && item.classList) {
        item.classList.remove('active');
      }
    });
    
    // Add active class to clicked item
    if (clickedItem.classList) {
      clickedItem.classList.add('active');
    }
    
    // Set background image and show full background view
    sliderMain.style.backgroundImage = `url('${imageUrl}')`;
    sliderMain.classList.add('show-yearbook-bg');
    
    console.log('Background set successfully:', imageUrl);
    
  } catch (error) {
    console.error('Error in showYearbookBackground:', error);
  }
}

function closeYearbookView() {
  try {
    const sliderMain = document.querySelector('.yearbook-slider-main');
    if (!sliderMain) {
      console.error('Yearbook slider main container not found');
      return;
    }
    
    const allItems = document.querySelectorAll('.yearbook-item');
    
    // Remove background and full view class
    sliderMain.style.backgroundImage = '';
    sliderMain.classList.remove('show-yearbook-bg', 'background-loaded');
    
    // Remove active class from all items
    allItems.forEach(item => {
      if (item && item.classList) {
        item.classList.remove('active');
      }
    });
    
    console.log('Yearbook view closed successfully');
    
  } catch (error) {
    console.error('Error in closeYearbookView:', error);
  }
}

// Keyboard support for closing yearbook view
document.addEventListener('keydown', function(e) {
  try {
    if (e.key === 'Escape') {
      const sliderMain = document.querySelector('.yearbook-slider-main');
      if (sliderMain && sliderMain.classList && sliderMain.classList.contains('show-yearbook-bg')) {
        closeYearbookView();
      }
    }
  } catch (error) {
    console.error('Error in keyboard handler:', error);
  }
});

// Click outside to close yearbook view
document.addEventListener('click', function(e) {
  try {
    const sliderMain = document.querySelector('.yearbook-slider-main');
    if (!sliderMain || !sliderMain.classList || !sliderMain.classList.contains('show-yearbook-bg')) {
      return;
    }
    
    const itemsContainer = document.querySelector('.yearbook-items-container');
    
    // Check if click is outside yearbook items container
    const clickedInContainer = itemsContainer && itemsContainer.contains(e.target);
    const clickedIntroContent = e.target.closest('.yearbook-intro-content');
    
    // Only close if clicked in the background area (not on items or intro content)
    if (!clickedInContainer && !clickedIntroContent && sliderMain.contains(e.target)) {
      closeYearbookView();
    }
  } catch (error) {
    console.error('Error in click outside handler:', error);
  }
});
