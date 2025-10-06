function addPage(page, book) {
  var id,
    pages = book.turn("pages");

  var element = $("<div />", {});

  if (book.turn("addPage", element, page)) {
    element.html('<div class="loader"></div>');

    loadPage(page, element);
  }
}

function addStudentPages(book, studentData) {
  if (!studentData || !studentData.total_pages) return;

  var totalPages = studentData.total_pages;
  var studentsPerPage = studentData.students_per_page || 6;

  for (var i = 0; i < totalPages; i++) {
    var pageNum = 7 + i;
    addPage(pageNum, book);
  }
}

function loadPage(page, pageElement) {
  var img = $("<img />");

  img.on("mousedown", function (e) {
    e.preventDefault();
  });

  img.on("load", function () {
    $(this).css({ width: "100%", height: "100%" });

    $(this).appendTo(pageElement);

    // Remove the loader indicator
    pageElement.find(".loader").remove();
  });

  // Check if we're loading the first or last page and use cover data if available
  var totalPages = $(".magazine").turn("pages");

  // Wait for coverData to be available if it's not yet loaded, but don't wait indefinitely
  var maxWaitTime = 5000; // 5 seconds
  var waitStartTime = Date.now();

  if (
    (typeof coverData === "undefined" || coverData === null) &&
    Date.now() - waitStartTime < maxWaitTime
  ) {
    setTimeout(function () {
      loadPage(page, pageElement);
    }, 100);
    return;
  }

  // Debug: Log the coverData and page information
  console.log(
    "Loading page:",
    page,
    "Total pages:",
    totalPages,
    "Cover data:",
    coverData
  );

  if (
    page === 1 &&
    typeof coverData !== "undefined" &&
    coverData !== null &&
    coverData.front_url
  ) {
    // First page - use front cover
    console.log("Using front_url for page 1:", coverData.front_url);
    img.attr("src", coverData.front_url);
  } else if (
    page === totalPages &&
    typeof coverData !== "undefined" &&
    coverData !== null &&
    coverData.back_url
  ) {
    // Last page - use back cover
    console.log("Using back_url for page", page, ":", coverData.back_url);
    img.attr("src", coverData.back_url);
  } else if (
    page >= 2 &&
    page <= 6 &&
    typeof coverData !== "undefined" &&
    coverData !== null
  ) {
    // Pages 2-6 - Top Management pages
    console.log("Loading top management page:", page);

    // Use background image if available
    if (coverData.background_url) {
      console.log(
        "Using background_url for management page",
        page,
        ":",
        coverData.background_url
      );
      img.attr("src", coverData.background_url);
    } else {
      // Fallback to white background if no background image is available
      img.attr(
        "src",
        "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23ffffff'/%3E%3C/svg%3E"
      );
    }

    // Create top management page container
    var managementPage = $("<div/>", {
      class: "top-management-page",
    });

    // Create loading indicator
    var loadingIndicator = $("<div/>", {
      class: "management-loading",
      text: "Loading top management data...",
    });

    managementPage.append(loadingIndicator);
    pageElement.append(managementPage);

    // Fetch top management data from MongoDB
    var template = 1; // Default template
    if (coverData.template) {
      template = coverData.template;
    }

    // Calculate index for management data (0-4 for pages 2-6)
    var managementIndex = page - 2;

    // Fetch data from our API
    $.ajax({
      url: "../../Connection/Photos/FetchTopManagement.php",
      method: "GET",
      data: {
        template: template,
      },
      dataType: "json",
      success: function (response) {
        console.log("Top management data response:", response);

        // Remove loading indicator
        loadingIndicator.remove();

        if (response.success && response.data && response.data.length > 0) {
          // Check if we have enough data for this page
          if (managementIndex < response.data.length) {
            var currentManager = response.data[managementIndex];

            // Create photo container
            var photoContainer = $("<div/>", {
              class: "management-photo",
            });

            // Create info container
            var infoContainer = $("<div/>", {
              class: "management-info",
            });

            // Add photo with fallback for no image
            var photoUrl =
              currentManager.photo_url ||
              'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="270" height="270" viewBox="0 0 270 270"%3E%3Crect width="270" height="270" fill="%23f0f0f0"/%3E%3Ctext x="135" y="135" font-family="Arial" font-size="14" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3ENo Image Available%3C/text%3E%3C/svg%3E';
            var photo = $("<img/>", {
              src: photoUrl,
              alt: currentManager.name,
              onerror:
                'this.src=\'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="270" height="270" viewBox="0 0 270 270"%3E%3Crect width="270" height="270" fill="%23f0f0f0"/%3E%3Ctext x="135" y="135" font-family="Arial" font-size="14" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3ENo Image Available%3C/text%3E%3C/svg%3E\';',
            });
            photoContainer.append(photo);

            // Add name with default placeholder
            var name = $("<h2/>", {
              class: "management-name",
              text: currentManager.name || "Top Management Name",
            });

            // Add position with default placeholder
            var position = $("<h3/>", {
              class: "management-position",
              text: currentManager.position || "Position Title",
            });

            // Add message with quotation marks
            var messageContainer = $("<div/>", {
              class: "message-container",
            });

            var quoteOpen = $("<span/>", {
              class: "quote-mark open",
              text: "❝",
            });

            var quoteClose = $("<span/>", {
              class: "quote-mark close",
              text: "❞",
            });

            var messageText =
              currentManager.message ||
              "No message available for this top management position. Messages typically contain inspirational words, guidance, or congratulations for the graduating class.";
            var message = $("<div/>", {
              class: "management-message",
              text: messageText,
            });

            // Create message wrapper for better positioning
            var messageWrapper = $("<div/>", {
              class: "message-wrapper",
            });

            // Put quotes directly in the message text
            message.prepend(quoteOpen);
            message.append(quoteClose);

            // Assemble the message in container
            messageWrapper.append(message);
            messageContainer.append(messageWrapper);

            // Assemble the page
            infoContainer
              .append(name)
              .append(position)
              .append(messageContainer);

            managementPage.append(photoContainer).append(infoContainer);
          } else {
            // Not enough data for this page - create a placeholder with default content
            var placeholderContainer = $("<div/>", {
              class: "top-management-page",
            });

            // Create photo container with placeholder
            var photoContainer = $("<div/>", {
              class: "management-photo",
            });

            var photo = $("<img/>", {
              src: 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="270" height="270" viewBox="0 0 270 270"%3E%3Crect width="270" height="270" fill="%23f0f0f0"/%3E%3Ctext x="135" y="135" font-family="Arial" font-size="14" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3ENo Image Available%3C/text%3E%3C/svg%3E',
              alt: "Top Management Placeholder",
            });
            photoContainer.append(photo);

            // Create info container with placeholder text
            var infoContainer = $("<div/>", {
              class: "management-info",
            });

            var name = $("<h2/>", {
              class: "management-name",
              text: "Top Management Name",
            });

            var position = $("<h3/>", {
              class: "management-position",
              text: "Position Title",
            });

            var messageContainer = $("<div/>", {
              class: "message-container",
            });

            var messageWrapper = $("<div/>", {
              class: "message-wrapper",
            });

            var message = $("<div/>", {
              class: "management-message",
              text: "No top management data available for this page. Messages typically contain inspirational words, guidance, or congratulations for the graduating class.",
            });

            // Assemble the placeholder
            messageWrapper.append(message);
            messageContainer.append(messageWrapper);

            infoContainer
              .append(name)
              .append(position)
              .append(messageContainer);

            placeholderContainer.append(photoContainer).append(infoContainer);

            managementPage.append(placeholderContainer);
          }
        } else {
          // Error or no data
          var errorMessage = $("<div/>", {
            class: "management-error",
            text: response.message || "Failed to load top management data.",
          });
          managementPage.append(errorMessage);
        }
      },
      error: function (xhr, status, error) {
        console.log("Error fetching top management data:", error);

        // Remove loading indicator
        loadingIndicator.remove();

        // Show error message
        var errorMessage = $("<div/>", {
          class: "management-error",
          text: "Error connecting to server. Please try again later.",
        });
        managementPage.append(errorMessage);
      },
    });
  } else if (
    page >= 7 &&
    typeof coverData !== "undefined" &&
    coverData !== null &&
    coverData.background_url
  ) {
    // Dynamic student pages - start from page 7
    console.log(
      "Using background_url for page",
      page,
      ":",
      coverData.background_url
    );
    img.attr("src", coverData.background_url);

    // Create student cards container after image loads
    img.on("load", function () {
      var cardsContainer = $("<div/>", {
        class: "cards-container",
      });

      // Extract department from coverData or URL parameters
      var urlParams = new URLSearchParams(window.location.search);
      var department = urlParams.get("department") || "BSME"; // Default to Maritime

      // Get template from coverData or default to 1
      var template = coverData && coverData.template ? coverData.template : 1;

      console.log(
        "Fetching student data for department:",
        department,
        "template:",
        template
      );

      // Fetch student data from our API
      $.ajax({
        url: "../../Connection/Photos/FetchStudentData.php",
        method: "GET",
        data: {
          department: department,
          template: template,
        },
        dataType: "json",
        success: function (response) {
          console.log("Student data response:", response);

          if (response.success && response.data && response.data.students) {
            var students = response.data.students;
            var totalStudents = response.data.total_students;
            var studentsPerPage = response.data.students_per_page;

            // Calculate which students to show on this page
            // Pages 7+ correspond to student pages, so page 7 = first student page (students 1-6)
            var pageOffset = (page - 7) * studentsPerPage;

            // Add up to 6 student cards for this page
            for (var i = 0; i < studentsPerPage; i++) {
              var studentIndex = pageOffset + i;

              // Check if we have a student for this position
              if (studentIndex < students.length) {
                var student = students[studentIndex];

                var card = $("<div/>", {
                  class: "student-card",
                });

                // Add student image
                var studentImg = $("<div/>", {
                  class: "student-image",
                });

                // Use student photo or placeholder
                var photoUrl =
                  student.photo_url ||
                  'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="135" height="155" viewBox="0 0 135 155"%3E%3Crect width="135" height="155" fill="%23f0f0f0"/%3E%3Ctext x="67.5" y="77.5" font-family="Arial" font-size="12" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3ENo Photo%3C/text%3E%3C/svg%3E';
                var studentPhoto = $("<img/>", {
                  src: photoUrl,
                  alt: student.name,
                  onerror:
                    'this.src=\'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="135" height="155" viewBox="0 0 135 155"%3E%3Crect width="135" height="155" fill="%23f0f0f0"/%3E%3Ctext x="67.5" y="77.5" font-family="Arial" font-size="12" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3ENo Photo%3C/text%3E%3C/svg%3E\';',
                });
                studentImg.append(studentPhoto);

                // Add student name
                var studentName = $("<h3/>", {
                  text: student.name,
                });

                // Add honors/program text
                var honorsText = $("<p/>", {
                  text: student.program || "Honors and Achievements",
                });

                // Add click handler for the card
                card.on("click", function () {
                  var modal = $(".student-modal");
                  var closeBtn = $(".close-modal");
                  var clickedStudent = students[$(this).index()];

                  // Initialize modal content with student data
                  modal.find(".student-name").text(clickedStudent.name);
                  modal
                    .find(".academic-year span")
                    .text(clickedStudent.year || "N/A");
                  modal
                    .find(".motto p")
                    .text(clickedStudent.motto || "No motto provided");

                  // Process milestones
                  var milestonesList = modal.find(".milestones ul");
                  milestonesList.empty();

                  if (
                    clickedStudent.milestones &&
                    Array.isArray(clickedStudent.milestones) &&
                    clickedStudent.milestones.length > 0
                  ) {
                    clickedStudent.milestones.forEach(function (milestone) {
                      milestonesList.append("<li>" + milestone + "</li>");
                    });
                  } else {
                    milestonesList.append("<li>No milestones recorded</li>");
                  }

                  // Initialize images
                  var $largeImage = modal.find(".student-image-large img");
                  var $thumbnails = modal.find(
                    ".student-image-thumbnails .thumbnail"
                  );

                  // Use student photo or placeholder
                  var modalPhotoUrl =
                    clickedStudent.photo_url ||
                    'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewBox="0 0 300 300"%3E%3Crect width="300" height="300" fill="%23f0f0f0"/%3E%3Ctext x="150" y="150" font-family="Arial" font-size="14" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3ENo Student Photo%3C/text%3E%3C/svg%3E';

                  // Set initial large image
                  $largeImage.attr("src", modalPhotoUrl);

                  // Set thumbnail images and initial active state
                  $thumbnails.each(function (index) {
                    $(this).find("img").attr("src", modalPhotoUrl);

                    if (index === 0) {
                      $(this).addClass("active");
                    } else {
                      $(this).removeClass("active");
                    }
                  });

                  // Show modal
                  modal.addClass("active");

                  // Handle thumbnail clicks
                  $thumbnails.off("click").on("click", function (e) {
                    e.stopPropagation(); // Prevent modal from closing
                    e.preventDefault();

                    var $this = $(this);
                    var index = $this.index();

                    // Update active state
                    $thumbnails.removeClass("active");
                    $this.addClass("active");

                    // Update large image with fade effect
                    $largeImage.fadeOut(200, function () {
                      $(this).attr("src", modalPhotoUrl).fadeIn(200);
                    });
                  });

                  // Close modal when clicking close button or outside
                  closeBtn.on("click", function () {
                    modal.removeClass("active");
                  });

                  $(window).on("click", function (event) {
                    if ($(event.target).hasClass("student-modal")) {
                      modal.removeClass("active");
                    }
                  });
                });

                // Assemble the card
                card.append(studentImg).append(studentName).append(honorsText);

                cardsContainer.append(card);
              } else {
                // Add empty card placeholder
                var card = $("<div/>", {
                  class: "student-card empty",
                });

                // Add student image placeholder
                var studentImg = $("<div/>", {
                  class: "student-image",
                });

                // Add placeholder image
                var placeholderImg = $("<img/>", {
                  src: 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="135" height="155" viewBox="0 0 135 155"%3E%3Crect width="135" height="155" fill="%23f0f0f0"/%3E%3Ctext x="67.5" y="77.5" font-family="Arial" font-size="12" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3EEmpty%3C/text%3E%3C/svg%3E',
                  alt: "Empty slot",
                });
                studentImg.append(placeholderImg);

                // Add placeholder text
                var studentName = $("<h3/>", {
                  text: "Available Slot",
                });

                var honorsText = $("<p/>", {
                  text: "Student data pending",
                });

                // Assemble the card
                card.append(studentImg).append(studentName).append(honorsText);

                cardsContainer.append(card);
              }
            }
          } else {
            // Error or no data - create default cards
            for (var i = 0; i < 6; i++) {
              var card = $("<div/>", {
                class: "student-card",
              });

              // Add student image placeholder
              var studentImg = $("<div/>", {
                class: "student-image",
              });

              // Add placeholder image
              var placeholderImg = $("<img/>", {
                src: 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="135" height="155" viewBox="0 0 135 155"%3E%3Crect width="135" height="155" fill="%23f0f0f0"/%3E%3Ctext x="67.5" y="77.5" font-family="Arial" font-size="12" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3ENo Photo%3C/text%3E%3C/svg%3E',
                alt: "Student placeholder",
              });
              studentImg.append(placeholderImg);

              // Add student name (default placeholder)
              var studentName = $("<h3/>", {
                text: "Student Name",
              });

              // Add honors text (default placeholder)
              var honorsText = $("<p/>", {
                text: "Honors and Achievements",
              });

              // Add click handler for the card
              card.on("click", function () {
                var modal = $(".student-modal");
                var closeBtn = $(".close-modal");
                var studentName = $(this).find("h3").text();

                // Sample images for demonstration (replace with actual student photos)
                var studentPhotos = [
                  'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewBox="0 0 300 300"%3E%3Crect width="300" height="300" fill="%23f0f0f0"/%3E%3Ctext x="150" y="150" font-family="Arial" font-size="14" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3ENo Student Photo%3C/text%3E%3C/svg%3E',
                  'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewBox="0 0 300 300"%3E%3Crect width="300" height="300" fill="%23f0f0f0"/%3E%3Ctext x="150" y="150" font-family="Arial" font-size="14" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3ENo Student Photo%3C/text%3E%3C/svg%3E',
                  'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewBox="0 0 300 300"%3E%3Crect width="300" height="300" fill="%23f0f0f0"/%3E%3Ctext x="150" y="150" font-family="Arial" font-size="14" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3ENo Student Photo%3C/text%3E%3C/svg%3E',
                ];

                // Initialize modal content with default placeholder
                modal.find(".student-name").text("Student Name");
                modal.find(".motto p").text("No motto available.");
                modal
                  .find(".milestones ul")
                  .html("<li>Honors and Achievements</li>");

                // Initialize images
                var $largeImage = modal.find(".student-image-large img");
                var $thumbnails = modal.find(
                  ".student-image-thumbnails .thumbnail"
                );

                // Set initial large image
                $largeImage.attr("src", studentPhotos[0]);

                // Set thumbnail images and initial active state
                $thumbnails.each(function (index) {
                  $(this).find("img").attr("src", studentPhotos[index]);

                  if (index === 0) {
                    $(this).addClass("active");
                  } else {
                    $(this).removeClass("active");
                  }
                });

                // Show modal
                modal.addClass("active");

                // Handle thumbnail clicks
                $thumbnails.off("click").on("click", function (e) {
                  e.stopPropagation(); // Prevent modal from closing
                  e.preventDefault();

                  var $this = $(this);
                  var index = $this.index();

                  // Update active state
                  $thumbnails.removeClass("active");
                  $this.addClass("active");

                  // Update large image with fade effect
                  $largeImage.fadeOut(200, function () {
                    $(this).attr("src", studentPhotos[index]).fadeIn(200);
                  });

                  console.log("Switching to photo:", index + 1); // Debug log
                });

                // Close modal when clicking close button or outside
                closeBtn.on("click", function () {
                  modal.removeClass("active");
                });

                $(window).on("click", function (event) {
                  if ($(event.target).hasClass("student-modal")) {
                    modal.removeClass("active");
                  }
                });
              });

              // Assemble the card
              card.append(studentImg).append(studentName).append(honorsText);

              cardsContainer.append(card);
            }
          }

          // Add the cards container to the page
          pageElement.append(cardsContainer);
        },
        error: function (xhr, status, error) {
          console.log("Error fetching student data:", error);

          // Create default cards on error
          for (var i = 0; i < 6; i++) {
            var card = $("<div/>", {
              class: "student-card",
            });

            // Add student image placeholder
            var studentImg = $("<div/>", {
              class: "student-image",
            });

            // Add placeholder image
            var placeholderImg = $("<img/>", {
              src: 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="135" height="155" viewBox="0 0 135 155"%3E%3Crect width="135" height="155" fill="%23f0f0f0"/%3E%3Ctext x="67.5" y="77.5" font-family="Arial" font-size="12" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3ENo Photo%3C/text%3E%3C/svg%3E',
              alt: "Student placeholder",
            });
            studentImg.append(placeholderImg);

            // Add student name (default placeholder)
            var studentName = $("<h3/>", {
              text: "Student Name",
            });

            // Add honors text (default placeholder)
            var honorsText = $("<p/>", {
              text: "Honors and Achievements",
            });

            // Add click handler for the card
            card.on("click", function () {
              var modal = $(".student-modal");
              var closeBtn = $(".close-modal");
              var studentName = $(this).find("h3").text();

              // Sample images for demonstration (replace with actual student photos)
              var studentPhotos = [
                'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewBox="0 0 300 300"%3E%3Crect width="300" height="300" fill="%23f0f0f0"/%3E%3Ctext x="150" y="150" font-family="Arial" font-size="14" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3ENo Student Photo%3C/text%3E%3C/svg%3E',
                'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewBox="0 0 300 300"%3E%3Crect width="300" height="300" fill="%23f0f0f0"/%3E%3Ctext x="150" y="150" font-family="Arial" font-size="14" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3ENo Student Photo%3C/text%3E%3C/svg%3E',
                'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewBox="0 0 300 300"%3E%3Crect width="300" height="300" fill="%23f0f0f0"/%3E%3Ctext x="150" y="150" font-family="Arial" font-size="14" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3ENo Student Photo%3C/text%3E%3C/svg%3E',
              ];

              // Initialize modal content with default placeholder
              modal.find(".student-name").text("Student Name");
              modal.find(".motto p").text("No motto available.");
              modal
                .find(".milestones ul")
                .html("<li>Honors and Achievements</li>");

              // Initialize images
              var $largeImage = modal.find(".student-image-large img");
              var $thumbnails = modal.find(
                ".student-image-thumbnails .thumbnail"
              );

              // Set initial large image
              $largeImage.attr("src", studentPhotos[0]);

              // Set thumbnail images and initial active state
              $thumbnails.each(function (index) {
                $(this).find("img").attr("src", studentPhotos[index]);

                if (index === 0) {
                  $(this).addClass("active");
                } else {
                  $(this).removeClass("active");
                }
              });

              // Show modal
              modal.addClass("active");

              // Handle thumbnail clicks
              $thumbnails.off("click").on("click", function (e) {
                e.stopPropagation(); // Prevent modal from closing
                e.preventDefault();

                var $this = $(this);
                var index = $this.index();

                // Update active state
                $thumbnails.removeClass("active");
                $this.addClass("active");

                // Update large image with fade effect
                $largeImage.fadeOut(200, function () {
                  $(this).attr("src", studentPhotos[index]).fadeIn(200);
                });

                console.log("Switching to photo:", index + 1); // Debug log
              });

              // Close modal when clicking close button or outside
              closeBtn.on("click", function () {
                modal.removeClass("active");
              });

              $(window).on("click", function (event) {
                if ($(event.target).hasClass("student-modal")) {
                  modal.removeClass("active");
                }
              });
            });

            // Assemble the card
            card.append(studentImg).append(studentName).append(honorsText);

            cardsContainer.append(card);
          }

          // Add the cards container to the page
          pageElement.append(cardsContainer);
        },
      });
    });
  } else if (
    typeof coverData !== "undefined" &&
    coverData !== null &&
    coverData.background_url
  ) {
    // Other middle pages - use background image as fallback
    console.log(
      "Using background_url as fallback for page",
      page,
      ":",
      coverData.background_url
    );
    img.attr("src", coverData.background_url);
  } else {
    // Show a placeholder when no image is available
    console.log("No image available for page:", page, "Showing placeholder");
    // Create a placeholder with a colored background
    img.attr(
      "src",
      "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23f0f0f0'/%3E%3Ctext x='50' y='55' font-family='Arial' font-size='12' fill='%23999' text-anchor='middle'%3EPage " +
        page +
        "%3C/text%3E%3C/svg%3E"
    );
  }

  loadRegions(page, pageElement);
}

// Zoom in / Zoom out

function zoomTo(event) {
  setTimeout(function () {
    // if ($(".magazine-viewport").data().regionClicked) {
    //   $(".magazine-viewport").data().regionClicked = false;
    // } else {
    //   if ($(".magazine-viewport").zoom("value") == 1) {
    //     $(".magazine-viewport").zoom("zoomIn", event);
    //   } else {
    //     $(".magazine-viewport").zoom("zoomOut");
    //   }
    // }
  }, 1);
}

// Load regions

function loadRegions(page, element) {
  // Skip loading regions for cover pages
  var totalPages = $(".magazine").turn("pages");
  if (page === 1 || page === totalPages) {
    return;
  }

  // Use absolute path for JSON files
  $.getJSON("pages/" + page + "-regions.json").done(function (data) {
    $.each(data, function (key, region) {
      addRegion(region, element);
    });
  });
}

// Add region

function addRegion(region, pageElement) {
  var reg = $("<div />", { class: "region  " + region["class"] }),
    options = $(".magazine").turn("options"),
    pageWidth = options.width / 2,
    pageHeight = options.height / 0;

  reg
    .css({
      top: Math.round((region.y / pageHeight) * 100) + "%",
      left: Math.round((region.x / pageWidth) * 100) + "%",
      width: Math.round((region.width / pageWidth) * 100) + "%",
      height: Math.round((region.height / pageHeight) * 100) + "%",
    })
    .attr("region-data", $.param(region.data || ""));

  reg.appendTo(pageElement);
}

// Process click on a region

function regionClick(event) {
  //   var region = $(event.target);
  //   if (region.hasClass("region")) {
  //     $(".magazine-viewport").data().regionClicked = true;
  //     setTimeout(function () {
  //       $(".magazine-viewport").data().regionClicked = false;
  //     }, 100);
  //     var regionType = $.trim(region.attr("class").replace("region", ""));
  //     return processRegion(region, regionType);
  //   }
}

// Process the data of every region

function processRegion(region, regionType) {
  data = decodeParams(region.attr("region-data"));

  switch (regionType) {
    case "link":
      window.open(data.url);

      break;
    case "zoom":
      var regionOffset = region.offset(),
        viewportOffset = $(".magazine-viewport").offset(),
        pos = {
          x: regionOffset.left - viewportOffset.left,
          y: regionOffset.top - viewportOffset.top,
        };

      $(".magazine-viewport").zoom("zoomIn", pos);

      break;
    case "to-page":
      $(".magazine").turn("page", data.page);

      break;
  }
}

// Load large page

function loadLargePage(page, pageElement) {
  var img = $("<img />");

  img.on("load", function () {
    var prevImg = pageElement.find("img");
    $(this).css({ width: "100%", height: "100%" });
    $(this).appendTo(pageElement);
    prevImg.remove();
  });

  // Check if we're loading the first or last page and use cover data if available
  var totalPages = $(".magazine").turn("pages");

  // Wait for coverData to be available if it's not yet loaded, but don't wait indefinitely
  var maxWaitTime = 5000; // 5 seconds
  var waitStartTime = Date.now();

  if (
    (typeof coverData === "undefined" || coverData === null) &&
    Date.now() - waitStartTime < maxWaitTime
  ) {
    setTimeout(function () {
      loadLargePage(page, pageElement);
    }, 100);
    return;
  }

  if (
    page === 1 &&
    typeof coverData !== "undefined" &&
    coverData !== null &&
    coverData.front_url
  ) {
    // First page - use front cover
    img.attr("src", coverData.front_url);
  } else if (
    page === totalPages &&
    typeof coverData !== "undefined" &&
    coverData !== null &&
    coverData.back_url
  ) {
    // Last page - use back cover
    img.attr("src", coverData.back_url);
  } else if (
    page >= 2 &&
    page <= 6 &&
    typeof coverData !== "undefined" &&
    coverData !== null
  ) {
    // Pages 2-6 - Top Management pages
    if (coverData.background_url) {
      img.attr("src", coverData.background_url);
    } else {
      img.attr(
        "src",
        "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23ffffff'/%3E%3C/svg%3E"
      );
    }
  } else if (
    page >= 7 &&
    page <= 11 &&
    typeof coverData !== "undefined" &&
    coverData !== null &&
    coverData.background_url
  ) {
    // Pages 7-11 - use background image from database
    img.attr("src", coverData.background_url);
  } else if (
    typeof coverData !== "undefined" &&
    coverData !== null &&
    coverData.background_url
  ) {
    // Other middle pages - use background image as fallback
    img.attr("src", coverData.background_url);
  } else {
    // Show a placeholder when no image is available
    console.log("No image available for page:", page, "Showing placeholder");
    // Create a placeholder with a colored background
    img.attr(
      "src",
      "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23f0f0f0'/%3E%3Ctext x='50' y='55' font-family='Arial' font-size='12' fill='%23999' text-anchor='middle'%3ELarge Page " +
        page +
        "%3C/text%3E%3C/svg%3E"
    );
  }
}

// Load small page

function loadSmallPage(page, pageElement) {
  var img = pageElement.find("img");

  img.css({ width: "100%", height: "100%" });

  img.off("load");

  // Check if we're loading the first or last page and use cover data if available
  var totalPages = $(".magazine").turn("pages");

  // Wait for coverData to be available if it's not yet loaded, but don't wait indefinitely
  var maxWaitTime = 5000; // 5 seconds
  var waitStartTime = Date.now();

  if (
    (typeof coverData === "undefined" || coverData === null) &&
    Date.now() - waitStartTime < maxWaitTime
  ) {
    setTimeout(function () {
      loadSmallPage(page, pageElement);
    }, 100);
    return;
  }

  if (
    page === 1 &&
    typeof coverData !== "undefined" &&
    coverData !== null &&
    coverData.front_url
  ) {
    // First page - use front cover
    img.attr("src", coverData.front_url);
  } else if (
    page === totalPages &&
    typeof coverData !== "undefined" &&
    coverData !== null &&
    coverData.back_url
  ) {
    // Last page - use back cover
    img.attr("src", coverData.back_url);
  } else if (
    page >= 2 &&
    page <= 6 &&
    typeof coverData !== "undefined" &&
    coverData !== null
  ) {
    // Pages 2-6 - Top Management pages
    if (coverData.background_url) {
      img.attr("src", coverData.background_url);
    } else {
      img.attr(
        "src",
        "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23ffffff'/%3E%3C/svg%3E"
      );
    }
  } else if (
    page >= 7 &&
    page <= 11 &&
    typeof coverData !== "undefined" &&
    coverData !== null &&
    coverData.background_url
  ) {
    // Pages 7-11 - use background image from database
    img.attr("src", coverData.background_url);
  } else {
    // Show a placeholder when no image is available
    console.log("No image available for page:", page, "Showing placeholder");
    // Create a placeholder with a colored background
    img.attr(
      "src",
      "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23f0f0f0'/%3E%3Ctext x='50' y='55' font-family='Arial' font-size='12' fill='%23999' text-anchor='middle'%3ESmall Page " +
        page +
        "%3C/text%3E%3C/svg%3E"
    );
  }
}

function disableControls(page) {
  if (page == 1) $(".previous-button").hide();
  else $(".previous-button").show();

  if (page == $(".magazine").turn("pages")) $(".next-button").hide();
  else $(".next-button").show();
}

// Set the width and height for the viewport

function resizeViewport() {
  var width = $(window).width(),
    height = $(window).height(),
    options = $(".magazine").turn("options");

  $(".magazine").removeClass("animated");

  $(".magazine-viewport")
    .css({
      width: width,
      height: height,
    })
    .zoom("resize");

  if ($(".magazine").turn("zoom") == 1) {
    var bound = calculateBound({
      width: options.width,
      height: options.height,
      boundWidth: Math.min(options.width, width),
      boundHeight: Math.min(options.height, height),
    });

    if (bound.width % 2 !== 0) bound.width -= 1;

    if (
      bound.width != $(".magazine").width() ||
      bound.height != $(".magazine").height()
    ) {
      $(".magazine").turn("size", bound.width, bound.height);

      if ($(".magazine").turn("page") == 1) $(".magazine").turn("peel", "br");

      $(".next-button").css({
        height: bound.height,
        backgroundPosition: "-38px " + (bound.height / 2 - 32 / 2) + "px",
      });
      $(".previous-button").css({
        height: bound.height,
        backgroundPosition: "-4px " + (bound.height / 2 - 32 / 2) + "px",
      });
    }

    $(".magazine").css({ top: -bound.height / 2, left: -bound.width / 2 });
  }

  var magazineOffset = $(".magazine").offset(),
    boundH = height - magazineOffset.top - $(".magazine").height(),
    marginTop = (boundH - $(".thumbnails > div").height()) / 2;

  if (marginTop < 0) {
    $(".thumbnails").css({ height: 1 });
  } else {
    $(".thumbnails").css({ height: boundH });
    $(".thumbnails > div").css({ marginTop: marginTop });
  }

  if (magazineOffset.top < $(".made").height()) $(".made").hide();
  else $(".made").show();

  $(".magazine").addClass("animated");
}

// Number of views in a flipbook

function numberOfViews(book) {
  return book.turn("pages") / 2 + 1;
}

// Current view in a flipbook

function getViewNumber(book, page) {
  return parseInt((page || book.turn("page")) / 2 + 1, 10);
}

function moveBar(yes) {
  $("#slider .ui-slider-handle").css({ zIndex: yes ? -1 : 10000 });
}

function setPreview(view) {
  var previewWidth = 112,
    previewHeight = 73,
    previewSrc = "pages/preview.jpg",
    preview = $(_thumbPreview.children(":first")),
    numPages =
      view == 1 || view == $("#slider").slider("option", "max") ? 1 : 2,
    width = numPages == 1 ? previewWidth / 2 : previewWidth;

  _thumbPreview.addClass("no-transition").css({
    width: width + 15,
    height: previewHeight + 15,
    top: -previewHeight - 30,
    left: ($($("#slider").children(":first")).width() - width - 15) / 2,
  });

  preview.css({
    width: width,
    height: previewHeight,
  });

  if (
    preview.css("background-image") === "" ||
    preview.css("background-image") == "none"
  ) {
    preview.css({ backgroundImage: "url(" + previewSrc + ")" });

    setTimeout(function () {
      _thumbPreview.removeClass("no-transition");
    }, 0);
  }

  preview.css({
    backgroundPosition: "0px -" + (view - 1) * previewHeight + "px",
  });
}

// Width of the flipbook when zoomed in

function largeMagazineWidth() {
  return 2214;
}

// decode URL Parameters

function decodeParams(data) {
  var parts = data.split("&"),
    d,
    obj = {};

  for (var i = 0; i < parts.length; i++) {
    d = parts[i].split("=");
    obj[decodeURIComponent(d[0])] = decodeURIComponent(d[1]);
  }

  return obj;
}

// Calculate the width and height of a square within another square

function calculateBound(d) {
  var bound = { width: d.width, height: d.height };

  if (bound.width > d.boundWidth || bound.height > d.boundHeight) {
    var rel = bound.width / bound.height;

    if (
      d.boundWidth / rel > d.boundHeight &&
      d.boundHeight * rel <= d.boundWidth
    ) {
      bound.width = Math.round(d.boundHeight * rel);
      bound.height = d.boundHeight;
    } else {
      bound.width = d.boundWidth;
      bound.height = Math.round(d.boundWidth / rel);
    }
  }

  return bound;
}
