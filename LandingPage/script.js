document.addEventListener("DOMContentLoaded", function () {
  const hamburgerMenu = document.getElementById("hamburgerMenu");
  const centerNav = document.querySelector(".center-nav");

  if (!hamburgerMenu || !centerNav) return;

  const toggleMenu = function (e) {
    e.stopPropagation();
    e.preventDefault();
    this.classList.toggle("active");
    centerNav.classList.toggle("mobile-active");
  };

  hamburgerMenu.addEventListener("click", toggleMenu);
  hamburgerMenu.addEventListener("touchend", function(e) {
    e.preventDefault();
    toggleMenu.call(this, e);
  }, { passive: false });

  const navLinks = centerNav.querySelectorAll("a");
  navLinks.forEach((link) => {
    link.addEventListener("click", function () {
      hamburgerMenu.classList.remove("active");
      centerNav.classList.remove("mobile-active");
    });
  });

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

document.addEventListener("DOMContentLoaded", function () {
  const navLinks = document.querySelectorAll(".center-nav a");
  const heroButtons = document.querySelectorAll(
    ".hero-btn, .hero-btn-secondary"
  );

  navLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault();

      navLinks.forEach((navLink) => navLink.classList.remove("clicked"));

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

  heroButtons.forEach((button) => {
    // Handle both click and touch events
    const handleInteraction = function (e) {
      e.preventDefault();
      e.stopPropagation();

      this.classList.add("clicked");

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
    };

    button.addEventListener("click", handleInteraction);
    button.addEventListener("touchend", function(e) {
      e.preventDefault();
      handleInteraction.call(this, e);
    }, { passive: false });
  });
});

const track = document.getElementById("carousel-track");
let carouselImageElements = Array.from(track.querySelectorAll(".carousel-img"));
let carouselImages = carouselImageElements.map((img) => img.src);

let currentIndex = 0;

function renderImages() {
  const images = [
    carouselImages[carouselImages.length - 1],
    ...carouselImages,
    carouselImages[0],
  ];

  track.innerHTML = images
    .map(
      (src, i) =>
        `<img src="${src}" class="carousel-img" data-index="${
          i - 1
        }" draggable="false" />`
    )
    .join("");

  carouselImageElements = Array.from(track.querySelectorAll(".carousel-img"));

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

let startX = 0;
let isDragging = false;

track.addEventListener("touchstart", (e) => {
  startX = e.touches[0].clientX;
  isDragging = true;
}, { passive: true });

track.addEventListener("touchmove", (e) => {
  if (!isDragging) return;
  const diff = e.touches[0].clientX - startX;
  track.style.transition = "none";
  track.style.transform = `translateX(calc(-${
    (currentIndex + 1) * 100
  }% + ${diff}px))`;
}, { passive: true });

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

renderImages();

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

document.addEventListener("DOMContentLoaded", function () {
  const carousel = document.querySelector(".carousel-3d");
  const items = document.querySelectorAll(".carousel-3d-item");
  const prevBtn = document.querySelector(".carousel-3d-prev");
  const nextBtn = document.querySelector(".carousel-3d-next");
  const pagination = document.querySelector(".carousel-3d-pagination");

  if (!carousel || !items.length || !pagination) {
    console.log(
      "3D carousel elements not found - skipping 3D carousel initialization"
    );
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

  function initCarousel() {
    try {
      items.forEach((item, index) => {
        const rotation = angle * index;
        item.style.transform = `rotateY(${rotation}deg) translateZ(500px)`;

        item.setAttribute("data-index", index);
      });

      createPagination();
      updatePagination();

      setupEventListeners();
    } catch (error) {
      console.error("Error initializing 3D carousel:", error);
    }
  }

  function createPagination() {
    if (!pagination) return;

    try {
      for (let i = 0; i < totalItems; i++) {
        const dot = document.createElement("button");
        dot.addEventListener("click", () => goToSlide(i));
        pagination.appendChild(dot);
      }
    } catch (error) {
      console.error("Error creating pagination:", error);
    }
  }

  function updatePagination() {
    if (!pagination) return;

    try {
      const dots = pagination.querySelectorAll("button");
      dots.forEach((dot, index) => {
        dot.classList.toggle("active", index === currentIndex);
      });
    } catch (error) {
      console.error("Error updating pagination:", error);
    }
  }

  function goToSlide(index) {
    try {
      currentIndex = (index + totalItems) % totalItems;
      rotateCarousel();
      updatePagination();
    } catch (error) {
      console.error("Error going to slide:", error);
    }
  }

  function rotateCarousel() {
    if (!carousel) {
      console.warn("Carousel element not found");
      return;
    }

    try {
      const rotation = -angle * currentIndex;
      carousel.style.transition = "transform 0.8s cubic-bezier(0.4, 0, 0.2, 1)";
      carousel.style.transform = `translateZ(-500px) rotateY(${rotation}deg)`;
    } catch (error) {
      console.error("Error rotating carousel:", error);
    }
  }

  function nextSlide() {
    try {
      currentIndex = (currentIndex + 1) % totalItems;
      rotateCarousel();
      updatePagination();
    } catch (error) {
      console.error("Error moving to next slide:", error);
    }
  }

  function prevSlide() {
    try {
      currentIndex = (currentIndex - 1 + totalItems) % totalItems;
      rotateCarousel();
      updatePagination();
    } catch (error) {
      console.error("Error moving to previous slide:", error);
    }
  }

  function setupEventListeners() {
    try {
      if (prevBtn) {
        prevBtn.addEventListener("click", prevSlide);
      }
      if (nextBtn) {
        nextBtn.addEventListener("click", nextSlide);
      }

      if (carousel) {
        carousel.addEventListener("touchstart", touchStart);
        carousel.addEventListener("touchend", touchEnd);
        carousel.addEventListener("touchmove", touchMove);

        carousel.addEventListener("mousedown", dragStart);
        carousel.addEventListener("mouseup", dragEnd);
        carousel.addEventListener("mouseleave", dragEnd);
        carousel.addEventListener("mousemove", drag);
      }

      const images = document.querySelectorAll(".carousel-3d-item img");
      images.forEach((img) => {
        img.addEventListener("dragstart", (e) => e.preventDefault());
      });
    } catch (error) {
      console.error("Error setting up event listeners:", error);
    }
  }

  function touchStart(e) {
    if (!carousel) return;

    try {
      startPos = e.touches[0].clientX;
      isDragging = true;
      carousel.style.transition = "none";
      cancelAnimationFrame(animationID);
    } catch (error) {
      console.error("Error in touchStart:", error);
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
      console.error("Error in touchMove:", error);
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
      console.error("Error in touchEnd:", error);
    }
  }

  function dragStart(e) {
    if (!carousel) return;

    try {
      e.preventDefault();
      startPos = e.clientX;
      isDragging = true;
      carousel.style.transition = "none";
      cancelAnimationFrame(animationID);
    } catch (error) {
      console.error("Error in dragStart:", error);
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
      console.error("Error in drag:", error);
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
      console.error("Error in dragEnd:", error);
    }
  }

  let autoRotate = null;
  if (carousel) {
    autoRotate = setInterval(nextSlide, 5000);

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

  initCarousel();
});

window.addEventListener("scroll", () => {
  const sections = document.querySelectorAll("section, footer");
  const navLinks = document.querySelectorAll(".center-nav a");

  let current = "";

  sections.forEach((section) => {
    const sectionTop = section.offsetTop - 100;
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

document.addEventListener("DOMContentLoaded", function () {
  const loginBtn = document.getElementById("loginDropdownBtn");
  const mobileLoginBtn = document.getElementById("mobileLoginDropdownBtn");

  // Use BASE_URL from HTML if available, otherwise auto-detect
  let BASE_URL_LOGIN;
  
  if (typeof BASE_URL !== 'undefined') {
    // BASE_URL is defined in the HTML head section
    BASE_URL_LOGIN = BASE_URL;
  } else {
    // Fallback: Auto-detect base URL for Railway vs Localhost
    const isLocalhost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
    const isInLandingPage = window.location.pathname.includes('/LandingPage/');
    BASE_URL_LOGIN = (isLocalhost && !isInLandingPage) ? '/ECADYB/' : '/';
  }

  function handleLoginClick(e) {
    e.preventDefault();
    e.stopPropagation();

    document.body.classList.add("page-transition-out");

    setTimeout(() => {
      window.location.href = BASE_URL_LOGIN + "Public/Components/Login.php";
    }, 1000);
  }

  if (loginBtn) {
    loginBtn.replaceWith(loginBtn.cloneNode(true));
    const newLoginBtn = document.getElementById("loginDropdownBtn");
    newLoginBtn.addEventListener("click", handleLoginClick);
    newLoginBtn.addEventListener("touchend", function(e) {
      e.preventDefault();
      handleLoginClick.call(this, e);
    }, { passive: false });

    newLoginBtn.onclick = handleLoginClick;
  }

  if (mobileLoginBtn) {
    mobileLoginBtn.replaceWith(mobileLoginBtn.cloneNode(true));
    const newMobileBtn = document.getElementById("mobileLoginDropdownBtn");
    newMobileBtn.addEventListener("click", handleLoginClick);
    newMobileBtn.addEventListener("touchend", function(e) {
      e.preventDefault();
      handleLoginClick.call(this, e);
    }, { passive: false });

    newMobileBtn.onclick = handleLoginClick;
  }
});

if ("serviceWorker" in navigator) {
  window.addEventListener("load", () => {
    // Detect service worker path based on current location
    const isInLandingPage = window.location.pathname.includes('/LandingPage/');
    const swPath = isInLandingPage ? './service-worker.js' : './LandingPage/service-worker.js';
    
    navigator.serviceWorker
      .register(swPath)
      .then((reg) => console.log("✅ Service Worker registered:", reg.scope))
      .catch((err) => console.log("❌ Service Worker failed:", err));
  });
}

document.addEventListener("DOMContentLoaded", function () {
  setTimeout(() => {
    initializeYearbookItems();
  }, 100);
});

function initializeYearbookItems() {
  const itemsContainer = document.querySelector(".yearbook-items-container");
  const sliderMain = document.querySelector(".yearbook-slider-main");

  if (!itemsContainer || !sliderMain) {
    console.warn("Required yearbook elements not found, retrying...");
    setTimeout(initializeYearbookItems, 200);
    return;
  }

  const existingListener = itemsContainer.getAttribute("data-listener");
  if (existingListener) {
    return;
  }

  itemsContainer.setAttribute("data-listener", "true");

  const yearBookItems = document.querySelectorAll(".yearbook-item");
  yearBookItems.forEach((item) => {
    if (item) {
      item.style.transform = "";

      item.style.willChange = "transform";
      item.style.backfaceVisibility = "hidden";
    }
  });

  console.log(
    "Yearbook items initialized successfully - hover should work smoothly now"
  );
}

function showYearbookBackground(clickedItem, imageUrl) {
  try {
    if (!clickedItem || !imageUrl) {
      console.error("Missing required parameters for showYearbookBackground");
      return;
    }

    const sliderMain = document.querySelector(".yearbook-slider-main");
    if (!sliderMain) {
      console.error("Yearbook slider main container not found");
      return;
    }

    const allItems = document.querySelectorAll(".yearbook-item");
    const introContent = document.querySelector(".yearbook-intro-content");
    const detailDisplay = document.querySelector(".yearbook-detail-display");
    const coverImage = document.querySelector(".yearbook-cover-image");
    const detailTitle = document.querySelector(".yearbook-detail-title");
    const detailDescription = document.querySelector(
      ".yearbook-detail-description"
    );

    allItems.forEach((item) => {
      if (item && item.classList) {
        item.classList.remove("active");
      }
    });

    if (clickedItem.classList) {
      clickedItem.classList.add("active");
    }

    const clickedItemStyle = clickedItem.getAttribute("style");
    const coverImageMatch = clickedItemStyle.match(
      /background-image:\s*url\(['\"]?([^'\"\)]+)['\"]?\)/
    );
    const coverImageUrl = coverImageMatch ? coverImageMatch[1] : "";

    const departmentInfo = getDepartmentInfo(coverImageUrl);

    sliderMain.style.backgroundImage = `url('${imageUrl}')`;
    sliderMain.classList.add("show-yearbook-bg");

    if (introContent) {
      introContent.style.display = "none";
    }

    if (detailDisplay && coverImage && detailTitle && detailDescription) {
      coverImage.src = coverImageUrl;
      coverImage.alt = departmentInfo.title + " Yearbook Cover";

      detailTitle.textContent = departmentInfo.title;
      detailDescription.textContent = departmentInfo.description;

      detailDisplay.style.display = "flex";
    }

    console.log("Background set successfully:", imageUrl);
    console.log("Department info:", departmentInfo);
  } catch (error) {
    console.error("Error in showYearbookBackground:", error);
  }
}

function getDepartmentInfo(coverImageUrl) {
  const departmentMap = {
    "MaritimeEducation.png": {
      title: "College of Maritime Education",
      description:
        "Dedicated to the seafarers who embraced discipline, courage, and determination. This yearbook captures the proud tradition of alumni who are now prepared to navigate not only the seas but also the challenges of life with strength and honor.",
    },
    "TourismManagement.png": {
      title: "College of Tourism Management",
      description:
        "This section honors the dreamers and storytellers of culture and travel. Alumni from this department will forever be remembered for their passion for hospitality, their creativity in connecting people, and their ability to make the world feel closer.",
    },
    "CriminalJusticeEducation.png": {
      title: "College of Criminal Justice and Education",
      description:
        "A tribute to the men and women who stood for justice, discipline, and service. Their journey reflects resilience and integrity, and as alumni, they carry forward the values of fairness, leadership, and lifelong learning.",
    },
    "InformationSystem.png": {
      title: "College of Information System",
      description:
        "This yearbook celebrates the innovators and problem-solvers who turned codes into solutions and ideas into systems. Alumni of this department leave behind a legacy of creativity and technological advancement, ready to shape the digital future.",
    },
    "Education.png": {
      title: "College of Education",
      description:
        "A heartfelt tribute to those who chose the noble path of teaching. Alumni of this college carry with them the memories of inspiration and hard work, ready to ignite curiosity and shape the minds of future generations.",
    },
    "BusinessAdministration.png": {
      title: "College of Business Administration",
      description:
        "A celebration of leaders, thinkers, and trailblazers in the making. Alumni of this college leave behind a legacy of ambition and innovation, ready to build businesses, inspire change, and create opportunities for the future.",
    },
    "Nursing.png": {
      title: "College of Nursing",
      description:
        "This yearbook honors the compassionate hearts and steady hands of those who trained to serve. Alumni from this department will always be remembered for their dedication to care, their selflessness, and their unwavering commitment to saving lives.",
    },
  };

  const filename = coverImageUrl.split("/").pop();

  return (
    departmentMap[filename] || {
      title: "Department Yearbook",
      description:
        "Explore memories, achievements, and milestones from our academic programs. A collection of moments that showcase the dedication, growth, and success of our students and faculty members.",
    }
  );
}

function closeYearbookView() {
  try {
    const sliderMain = document.querySelector(".yearbook-slider-main");
    if (!sliderMain) {
      console.error("Yearbook slider main container not found");
      return;
    }

    const allItems = document.querySelectorAll(".yearbook-item");
    const introContent = document.querySelector(".yearbook-intro-content");
    const detailDisplay = document.querySelector(".yearbook-detail-display");

    sliderMain.style.backgroundImage = "";
    sliderMain.classList.remove("show-yearbook-bg", "background-loaded");

    allItems.forEach((item) => {
      if (item && item.classList) {
        item.classList.remove("active");
      }
    });

    if (introContent) {
      introContent.style.display = "block";
    }

    if (detailDisplay) {
      detailDisplay.style.display = "none";
    }

    console.log("Yearbook view closed successfully");
  } catch (error) {
    console.error("Error in closeYearbookView:", error);
  }
}

// Mobile yearbook slider navigation
let currentYearbookIndex = 0;

function navigateYearbook(direction) {
  try {
    // Only apply navigation on mobile screens
    if (window.innerWidth > 480) {
      return;
    }

    const items = document.querySelectorAll('.yearbook-item');
    if (!items || items.length === 0) {
      return;
    }

    // Remove all classes from all items
    items.forEach(item => {
      item.classList.remove('mobile-active', 'mobile-prev', 'mobile-next');
    });

    // Calculate new index
    if (direction === 'next') {
      currentYearbookIndex = (currentYearbookIndex + 1) % items.length;
    } else if (direction === 'prev') {
      currentYearbookIndex = (currentYearbookIndex - 1 + items.length) % items.length;
    }

    // Add active class to current item
    items[currentYearbookIndex].classList.add('mobile-active');

    // Add prev class to previous item
    const prevIndex = (currentYearbookIndex - 1 + items.length) % items.length;
    items[prevIndex].classList.add('mobile-prev');

    // Add next class to next item
    const nextIndex = (currentYearbookIndex + 1) % items.length;
    items[nextIndex].classList.add('mobile-next');

    // Ensure click handlers are working for the new active item
    items.forEach((item, index) => {
      // Remove existing click listeners to avoid duplicates
      item.removeEventListener('click', handleMobileYearbookClick);
      // Add new click listener
      item.addEventListener('click', handleMobileYearbookClick);
    });

    console.log('Navigated to yearbook item:', currentYearbookIndex);
  } catch (error) {
    console.error('Error in navigateYearbook:', error);
  }
}

// Separate function for mobile yearbook click handling
function handleMobileYearbookClick(e) {
  if (this.classList.contains('mobile-active')) {
    // Only allow clicks on the active item
    const imageUrl = 'https://ECADYB.b-cdn.net/img/BGGRALLERY2.0.png';
    showYearbookBackground(this, imageUrl);
  }
}

// Initialize mobile slider on page load
function initMobileYearbookSlider() {
  try {
    if (window.innerWidth <= 480) {
      const items = document.querySelectorAll('.yearbook-item');
      items.forEach((item, index) => {
        item.classList.remove('mobile-active', 'mobile-prev', 'mobile-next');
      });
      
      if (items.length > 0) {
        currentYearbookIndex = 0;
        
        // Set active item (center)
        items[0].classList.add('mobile-active');
        
        // Set previous item (left side)
        const prevIndex = (items.length - 1) % items.length;
        items[prevIndex].classList.add('mobile-prev');
        
        // Set next item (right side)
        if (items.length > 1) {
          items[1].classList.add('mobile-next');
        }
        
        // Add click handlers for mobile
        items.forEach((item, index) => {
          item.addEventListener('click', handleMobileYearbookClick);
        });
      }
    }
  } catch (error) {
    console.error('Error initializing mobile yearbook slider:', error);
  }
}

// Reinitialize on window resize
window.addEventListener('resize', function() {
  initMobileYearbookSlider();
});

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', function() {
  initMobileYearbookSlider();
});

document.addEventListener("keydown", function (e) {
  try {
    if (e.key === "Escape") {
      const sliderMain = document.querySelector(".yearbook-slider-main");
      if (
        sliderMain &&
        sliderMain.classList &&
        sliderMain.classList.contains("show-yearbook-bg")
      ) {
        closeYearbookView();
      }
    }
  } catch (error) {
    console.error("Error in keyboard handler:", error);
  }
});

document.addEventListener("click", function (e) {
  try {
    const sliderMain = document.querySelector(".yearbook-slider-main");
    if (
      !sliderMain ||
      !sliderMain.classList ||
      !sliderMain.classList.contains("show-yearbook-bg")
    ) {
      return;
    }

    const itemsContainer = document.querySelector(".yearbook-items-container");
    const detailDisplay = document.querySelector(".yearbook-detail-display");

    const clickedInContainer =
      itemsContainer && itemsContainer.contains(e.target);
    const clickedInDetail = detailDisplay && detailDisplay.contains(e.target);
    const clickedIntroContent = e.target.closest(".yearbook-intro-content");

    if (
      !clickedInContainer &&
      !clickedInDetail &&
      !clickedIntroContent &&
      sliderMain.contains(e.target)
    ) {
      closeYearbookView();
    }
  } catch (error) {
    console.error("Error in click outside handler:", error);
  }
});
