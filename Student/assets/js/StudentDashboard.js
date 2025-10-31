document.addEventListener("DOMContentLoaded", function () {
  if (window.location.hash) {
    setTimeout(() => {
      if (window.history && window.history.replaceState) {
        window.history.replaceState(null, null, window.location.pathname);
      }
    }, 100);
  }

  const hamburgerMenu = document.getElementById("hamburgerMenu");
  const centerNav = document.querySelector(".center-nav");

  if (!hamburgerMenu || !centerNav) return;

  hamburgerMenu.addEventListener("click", function (e) {
    e.stopPropagation();
    this.classList.toggle("active");
    centerNav.classList.toggle("mobile-active");
  });

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
  const notificationIcon = document.getElementById("notificationIcon");
  const notificationDropdown = document.getElementById("notificationDropdown");
  const notificationBadge = document.getElementById("notificationBadge");
  const profileIcon = document.getElementById("profileIcon");
  const dropdownMenu = document.getElementById("profileDropdownMenu");

  if (!notificationIcon || !notificationDropdown) return;

  notificationIcon.addEventListener("click", function (e) {
    e.stopPropagation();
    this.classList.toggle("clicked");
    notificationDropdown.classList.toggle("show");

    if (profileIcon && dropdownMenu) {
      profileIcon.classList.remove("clicked");
      dropdownMenu.classList.remove("show");
    }
  });

  document.addEventListener("click", function (event) {
    if (
      !notificationIcon.contains(event.target) &&
      !notificationDropdown.contains(event.target)
    ) {
      notificationIcon.classList.remove("clicked");
      notificationDropdown.classList.remove("show");
    }
  });

  const notificationItems = document.querySelectorAll(".notification-item");
  notificationItems.forEach((item) => {
    item.addEventListener("click", function () {
      this.classList.remove("unread");
      updateNotificationBadge();
    });
  });

  function updateNotificationBadge() {
    const unreadCount = document.querySelectorAll(
      ".notification-item.unread"
    ).length;
    if (notificationBadge) {
      if (unreadCount > 0) {
        notificationBadge.textContent = unreadCount;
        notificationBadge.classList.remove("hidden");
      } else {
        notificationBadge.classList.add("hidden");
      }
    }
  }
});

document.addEventListener("DOMContentLoaded", function () {
  const profileIcon = document.getElementById("profileIcon");
  const dropdownMenu = document.getElementById("profileDropdownMenu");
  const notificationIcon = document.getElementById("notificationIcon");
  const notificationDropdown = document.getElementById("notificationDropdown");

  if (!profileIcon || !dropdownMenu) return;

  profileIcon.addEventListener("click", function (e) {
    e.stopPropagation();
    this.classList.toggle("clicked");
    dropdownMenu.classList.toggle("show");

    if (notificationIcon && notificationDropdown) {
      notificationIcon.classList.remove("clicked");
      notificationDropdown.classList.remove("show");
    }
  });

  document.addEventListener("click", function (event) {
    if (
      !profileIcon.contains(event.target) &&
      !dropdownMenu.contains(event.target)
    ) {
      profileIcon.classList.remove("clicked");
      dropdownMenu.classList.remove("show");
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
      const targetHref = this.getAttribute("href");

      if (targetHref && targetHref.startsWith("#")) {
        e.preventDefault();

        navLinks.forEach((navLink) => navLink.classList.remove("clicked"));
        this.classList.add("clicked");

        const targetSection = document.querySelector(targetHref);

        if (targetSection) {
          targetSection.scrollIntoView({
            behavior: "smooth",
          });

          setTimeout(() => {
            if (window.history && window.history.replaceState) {
              window.history.replaceState(null, null, window.location.pathname);
            }
          }, 1000);
        }
      }
    });
  });

  heroButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      const targetHref = this.getAttribute("href");

      if (targetHref && targetHref.startsWith("#")) {
        e.preventDefault();

        this.classList.add("clicked");

        setTimeout(() => {
          this.classList.remove("clicked");
        }, 300);

        const targetSection = document.querySelector(targetHref);

        if (targetSection) {
          targetSection.scrollIntoView({
            behavior: "smooth",
          });

          setTimeout(() => {
            if (window.history && window.history.replaceState) {
              window.history.replaceState(null, null, window.location.pathname);
            }
          }, 1000);
        }
      } else {
        this.classList.add("clicked");
        setTimeout(() => {
          this.classList.remove("clicked");
        }, 300);
      }
    });
  });
});

const track = document.getElementById("carousel-track");

if (track) {
  let carouselImageElements = Array.from(
    track.querySelectorAll(".carousel-img")
  );
  let carouselImages = carouselImageElements.map((img) => img.src);

  carouselImages = [...new Set(carouselImages)];

  let currentIndex = 0;
  let isTransitioning = false;

  function renderImages() {
    const images = [
      carouselImages[carouselImages.length - 1],
      ...carouselImages,
      carouselImages[0],
    ];

    track.innerHTML = images
      .map(
        (src, i) =>
          `<img src="${src}" class="carousel-img" data-index="${i - 1
          }" draggable="false" />`
      )
      .join("");

    carouselImageElements = Array.from(track.querySelectorAll(".carousel-img"));

    track.style.transition = "none";
    track.style.transform = `translateX(-100%)`;
    currentIndex = 0;
  }

  function moveToIndex(index) {
    if (isTransitioning) return;

    currentIndex = index;
    isTransitioning = true;
    track.style.transition = "transform 0.6s ease-in-out";
    track.style.transform = `translateX(-${(index + 1) * 100}%)`;
  }

  function handleTransitionEnd() {
    isTransitioning = false;

    if (currentIndex >= carouselImages.length) {
      track.style.transition = "none";
      track.style.transform = `translateX(-100%)`;
      currentIndex = 0;
    } else if (currentIndex < 0) {
      track.style.transition = "none";
      track.style.transform = `translateX(-${carouselImages.length * 100}%)`;
      currentIndex = carouselImages.length - 1;
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
  });

  track.addEventListener("touchmove", (e) => {
    if (!isDragging) return;
    const diff = e.touches[0].clientX - startX;
    track.style.transition = "none";
    track.style.transform = `translateX(calc(-${(currentIndex + 1) * 100
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

  renderImages();

  let autoSlideInterval = null;

  function startAutoSlide() {
    autoSlideInterval = setInterval(() => {
      nextImage();
    }, 3000);
  }

  function stopAutoSlide() {
    clearInterval(autoSlideInterval);
  }

  track.addEventListener("mouseenter", stopAutoSlide);
  track.addEventListener("mouseleave", startAutoSlide);

  startAutoSlide();
}

document.addEventListener("DOMContentLoaded", function () {
  const carousel = document.querySelector(".carousel-3d");
  const items = document.querySelectorAll(".carousel-3d-item");
  const prevBtn = document.querySelector(".carousel-3d-prev");
  const nextBtn = document.querySelector(".carousel-3d-next");
  const pagination = document.querySelector(".carousel-3d-pagination");

  if (!carousel || !items.length || !pagination) {
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

  function handleLoginClick(e) {
    e.preventDefault();
    e.stopPropagation();

    document.body.classList.add("page-transition-out");

    setTimeout(() => {
      window.location.href = basePath + "Login";
    }, 1000);
  }

  if (loginBtn) {
    loginBtn.replaceWith(loginBtn.cloneNode(true));
    const newLoginBtn = document.getElementById("loginDropdownBtn");
    newLoginBtn.addEventListener("click", handleLoginClick);

    newLoginBtn.onclick = handleLoginClick;
  }

  if (mobileLoginBtn) {
    mobileLoginBtn.replaceWith(mobileLoginBtn.cloneNode(true));
    const newMobileBtn = document.getElementById("mobileLoginDropdownBtn");
    newMobileBtn.addEventListener("click", handleLoginClick);

    newMobileBtn.onclick = handleLoginClick;
  }
});

document.addEventListener("DOMContentLoaded", function () {
  setTimeout(() => {
    initializeYearbookItems();
  }, 100);
});

function initializeYearbookItems() {
  const itemsContainer = document.querySelector(".yearbook-items-container");
  const sliderMain = document.querySelector(".yearbook-slider-main");

  if (!itemsContainer || !sliderMain) {
    if (!document.querySelector(".yearbooks-section")) {
      return;
    }
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

function markAllAsRead() {
  const notificationItems = document.querySelectorAll(".notification-item");
  const notificationBadge = document.getElementById("notificationBadge");

  notificationItems.forEach((item) => {
    item.classList.remove("unread");
  });

  if (notificationBadge) {
    notificationBadge.classList.add("hidden");
  }

  console.log("All notifications marked as read");
}

let originalFormValues = {};

function editProfile() {
  console.log("Edit Profile clicked");
  const modal = document.getElementById("editStudentModal");
  if (modal) {
    modal.classList.add("active");
    document.body.style.overflow = "hidden";

    const form = document.getElementById("edit-student-form");
    if (form) {
      originalFormValues = {
        email: form.querySelector("#email")?.value || "",
        motto: form.querySelector("#motto")?.value || "",
        milestone: form.querySelector("#milestone")?.value || "",
      };
    }
  }
}

function closeEditModal() {
  const modal = document.getElementById("editStudentModal");
  if (modal) {
    modal.classList.remove("active");
    document.body.style.overflow = "";

    const form = document.getElementById("edit-student-form");
    if (form) {
      form.reset();
    }
  }
}

document.addEventListener("click", function (event) {
  const modal = document.getElementById("editStudentModal");
  if (modal && event.target === modal && modal.classList.contains("active")) {
    closeEditModal();
  }
});

document.addEventListener("keydown", function (event) {
  const modal = document.getElementById("editStudentModal");
  if (event.key === "Escape" && modal && modal.classList.contains("active")) {
    closeEditModal();
  }
});

function allowOnlyLetters(input) {
  input.value = input.value.replace(/[^a-zA-Z\s]/g, "");
}

function removeSpaces(input) {
  input.value = input.value.replace(/\s/g, "");
}

function formatAcademicYear(input) {
  let value = input.value.replace(/[^0-9]/g, "");
  if (value.length > 4) {
    value = value.substring(0, 4) + "-" + value.substring(4, 8);
  }
  input.value = value;
}

async function submitStudentInfo(event) {
  event.preventDefault();

  const form = document.getElementById("edit-student-form");
  const formData = new FormData(form);

  const currentValues = {
    email: formData.get("email") || "",
    motto: formData.get("motto") || "",
    milestone: formData.get("milestone") || "",
  };

  const hasChanges =
    currentValues.email !== originalFormValues.email ||
    currentValues.motto !== originalFormValues.motto ||
    currentValues.milestone !== originalFormValues.milestone;

  if (!hasChanges) {
    showNotification(
      "No changes detected. Please modify at least one field before saving.",
      "info"
    );
    return;
  }

  const studentId = window.studentData?.studentId || formData.get("student_id");
  const academicYear =
    window.studentData?.studentAcademicYear || formData.get("academic_year");
  const department = window.studentData?.studentDepartment || "";

  // Validate student ID
  if (!studentId || studentId.trim() === "" || studentId === "0000-000000") {
    showNotification(
      "Error: Invalid student ID. Please log out and log in again.",
      "error"
    );
    console.error("Invalid student ID detected:", studentId);
    return;
  }

  const collectionMap = {
    "BS Marine Engineering": "bsme",
    "BS Marine Transportation": "bsmt",
    "BS Criminal Justice Education": "bscje",
    "BS Tourism Management": "bstm",
    "BS Technical-Vocational Teacher Education": "btvted",
    "BS Early Childhood Education": "beced",
    "BS Nursing": "bsn",
    "BS Information System": "bsis",
    "BS Management Accounting": "bsma",
    "BS Entrepreneurship": "bse",
  };

  const collection = collectionMap[department] || "bsme";

  const data = {
    original_student_id: studentId,
    collection: collection,
    academic_year: academicYear,
    "first name": formData.get("first_name"),
    "middle name": formData.get("middle_name"),
    "last name": formData.get("last_name"),
    email: formData.get("email"),
    motto: formData.get("motto"),
    honors: formData.get("honors"),
    milestone: formData.get("milestone"),
  };

  console.log("Submitting student data:", data);

  try {
    const response = await fetch(
      "/ECADYB/Connection/Student/UpdateStudent.php",
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
      }
    );

    const result = await response.json();

    if (result.success) {
      showNotification("Success! Student information updated.", "success");

      setTimeout(() => {
        closeEditModal();
      }, 1000);

      setTimeout(() => {
        location.reload();
      }, 2000);
    } else {
      showNotification(
        "Error: " + (result.message || "Failed to update student information."),
        "error"
      );
    }
  } catch (error) {
    console.error("Error updating student:", error);
    showNotification("Error: Failed to update student information.", "error");
  }
}

function showNotification(message, type = "info") {
  const existingNotification = document.querySelector(".notification");
  if (existingNotification) {
    existingNotification.remove();
  }

  const notification = document.createElement("div");
  notification.className = `notification ${type}`;

  let icon = "fa-info";
  if (type === "success") icon = "fa-check";
  if (type === "error") icon = "fa-times";
  if (type === "warning") icon = "fa-exclamation";

  notification.innerHTML = `
    <div class="notification-icon-wrapper">
      <i class="fas ${icon}"></i>
    </div>
    <div class="notification-message">${message}</div>
    <button class="notification-close" onclick="this.parentElement.remove()">
      <i class="fas fa-times"></i>
    </button>
  `;

  document.body.appendChild(notification);

  setTimeout(() => {
    notification.classList.add("show");
  }, 100);

  setTimeout(() => {
    notification.classList.remove("show");
    setTimeout(() => {
      notification.remove();
    }, 500);
  }, 3000);
}

function logout() {
  console.log("Logout clicked");
  const basePath = window.location.hostname === "localhost" ? "/ECADYB/" : "/";
  window.location.href = basePath + "Student/Components/Logout.php";
}

async function loadAnnouncements() {
  try {
    const notificationList = document.getElementById("notificationList");
    const notificationBadge = document.getElementById("notificationBadge");

    if (!notificationList || !notificationBadge) {
      return;
    }

    const response = await fetch(
      "/ECADYB/Connection/Announcement/FetchAnnouncements.php"
    );
    const result = await response.json();

    if (result.success && result.data.length > 0) {
      notificationBadge.textContent = result.count;
      notificationBadge.classList.remove("hidden");

      notificationList.innerHTML = "";

      result.data.forEach((announcement) => {
        const notificationItem = createNotificationItem(announcement);
        notificationList.appendChild(notificationItem);
      });
    } else {
      notificationList.innerHTML =
        '<div class="notification-item"><div class="notification-content"><p class="notification-text">No new announcements</p></div></div>';
      notificationBadge.classList.add("hidden");
    }
  } catch (error) {
    console.error("Error loading announcements:", error);
    const notificationList = document.getElementById("notificationList");
    if (notificationList) {
      notificationList.innerHTML =
        '<div class="notification-item"><div class="notification-content"><p class="notification-text">Error loading announcements</p></div></div>';
    }
  }
}

function createNotificationItem(announcement) {
  const item = document.createElement("div");
  item.className = "notification-item unread";

  let iconClass = "fa-solid fa-info-circle";
  if (announcement.type === "announcement") {
    iconClass = "fa-solid fa-bullhorn";
  } else if (announcement.type === "event") {
    iconClass = "fa-solid fa-calendar";
  }

  const formattedDate = formatAnnouncementDate(
    announcement.date,
    announcement.time
  );

  item.innerHTML = `
    <i class="${iconClass} notification-item-icon"></i>
    <div class="notification-content">
      <p class="notification-text"><strong>${announcement.title}</strong><br>${announcement.message}</p>
      <span class="notification-time">${formattedDate}</span>
    </div>
  `;

  return item;
}

function formatAnnouncementDate(date, time) {
  if (!date) return "Recently";

  const announcementDate = new Date(date + " " + time);

  const dateOptions = {
    year: "numeric",
    month: "short",
    day: "numeric",
  };

  const timeOptions = {
    hour: "numeric",
    minute: "2-digit",
    hour12: true,
  };

  const formattedDate = announcementDate.toLocaleDateString(
    "en-US",
    dateOptions
  );
  const formattedTime = announcementDate.toLocaleTimeString(
    "en-US",
    timeOptions
  );

  return `${formattedDate} at ${formattedTime}`;
}

document.addEventListener("DOMContentLoaded", function () {
  setTimeout(() => {
    loadAnnouncements();
  }, 100);
});

// Bottom Navigation Profile Dropdown Handler for Tablet
document.addEventListener("DOMContentLoaded", function () {
  const bottomProfileIcon = document.getElementById("bottomProfileIcon");
  const bottomDropdownMenu = document.getElementById("bottomProfileDropdownMenu");
  const notificationIcon = document.getElementById("notificationIcon");
  const notificationDropdown = document.getElementById("notificationDropdown");

  if (bottomProfileIcon && bottomDropdownMenu) {
    bottomProfileIcon.addEventListener("click", function (e) {
      e.stopPropagation();
      this.classList.toggle("clicked");
      bottomDropdownMenu.classList.toggle("show");

      // Close notification dropdown if open
      if (notificationIcon && notificationDropdown) {
        notificationIcon.classList.remove("clicked");
        notificationDropdown.classList.remove("show");
      }
    });

    document.addEventListener("click", function (event) {
      if (
        !bottomProfileIcon.contains(event.target) &&
        !bottomDropdownMenu.contains(event.target)
      ) {
        bottomProfileIcon.classList.remove("clicked");
        bottomDropdownMenu.classList.remove("show");
      }
    });
  }
});

// Active link highlighting for bottom navigation
document.addEventListener("DOMContentLoaded", function () {
  const bottomNavLinks = document.querySelectorAll(".bottom-nav a");
  const currentPath = window.location.pathname;

  bottomNavLinks.forEach((link) => {
    const linkPath = link.getAttribute("href");
    if (currentPath.includes(linkPath)) {
      link.classList.add("active");
    }
  });
});
