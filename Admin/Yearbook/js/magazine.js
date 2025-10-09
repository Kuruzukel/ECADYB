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

    pageElement.find(".loader").remove();
  });

  var totalPages = $(".magazine").turn("pages");

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
    console.log("Using front_url for page 1:", coverData.front_url);
    img.attr("src", coverData.front_url);
  } else if (
    page === totalPages &&
    typeof coverData !== "undefined" &&
    coverData !== null &&
    coverData.back_url
  ) {
    console.log("Using back_url for page", page, ":", coverData.back_url);
    img.attr("src", coverData.back_url);
  } else if (
    page >= 2 &&
    page <= 6 &&
    typeof coverData !== "undefined" &&
    coverData !== null
  ) {
    console.log("Loading top management page:", page);

    if (coverData.background_url) {
      console.log(
        "Using background_url for management page",
        page,
        ":",
        coverData.background_url
      );
      img.attr("src", coverData.background_url);
    } else {
      img.attr(
        "src",
        "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23ffffff'/%3E%3C/svg%3E"
      );
    }

    var managementPage = $("<div/>", {
      class: "top-management-page",
    });

    var loadingIndicator = $("<div/>", {
      class: "management-loading",
      text: "Loading top management data...",
    });

    managementPage.append(loadingIndicator);
    pageElement.append(managementPage);

    var template = 1;
    if (coverData.template) {
      template = coverData.template;
    }

    var managementIndex = page - 2;

    $.ajax({
      url: "../../Connection/Photos/FetchTopManagement.php",
      method: "GET",
      data: {
        template: template,
      },
      dataType: "json",
      success: function (response) {
        console.log("Top management data response:", response);

        loadingIndicator.remove();

        if (response.success && response.data && response.data.length > 0) {
          if (managementIndex < response.data.length) {
            var currentManager = response.data[managementIndex];

            var photoContainer = $("<div/>", {
              class: "management-photo",
            });

            var infoContainer = $("<div/>", {
              class: "management-info",
            });

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

            var name = $("<h2/>", {
              class: "management-name",
              text: currentManager.name || "Top Management Name",
            });

            var position = $("<h3/>", {
              class: "management-position",
              text: currentManager.position || "Position Title",
            });

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

            var messageWrapper = $("<div/>", {
              class: "message-wrapper",
            });

            message.prepend(quoteOpen);
            message.append(quoteClose);

            messageWrapper.append(message);
            messageContainer.append(messageWrapper);

            infoContainer
              .append(name)
              .append(position)
              .append(messageContainer);

            managementPage.append(photoContainer).append(infoContainer);

            setTimeout(function () {
              waitForImagesAndGenerateThumbnail(page, pageElement);
            }, 500);
          } else {
            var placeholderContainer = $("<div/>", {
              class: "top-management-page",
            });

            var photoContainer = $("<div/>", {
              class: "management-photo",
            });

            var photo = $("<img/>", {
              src: 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="270" height="270" viewBox="0 0 270 270"%3E%3Crect width="270" height="270" fill="%23f0f0f0"/%3E%3Ctext x="135" y="135" font-family="Arial" font-size="14" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3ENo Image Available%3C/text%3E%3C/svg%3E',
              alt: "Top Management Placeholder",
            });
            photoContainer.append(photo);

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

            messageWrapper.append(message);
            messageContainer.append(messageWrapper);

            infoContainer
              .append(name)
              .append(position)
              .append(messageContainer);

            placeholderContainer.append(photoContainer).append(infoContainer);

            managementPage.append(placeholderContainer);

            setTimeout(function () {
              waitForImagesAndGenerateThumbnail(page, pageElement);
            }, 500);
          }
        } else {
          var errorMessage = $("<div/>", {
            class: "management-error",
            text: response.message || "Failed to load top management data.",
          });
          managementPage.append(errorMessage);
        }
      },
      error: function (xhr, status, error) {
        console.log("Error fetching top management data:", error);

        loadingIndicator.remove();

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
    console.log(
      "Using background_url for page",
      page,
      ":",
      coverData.background_url
    );
    img.attr("src", coverData.background_url);

    img.on("load", function () {
      var cardsContainer = $("<div/>", {
        class: "cards-container",
      });

      var urlParams = new URLSearchParams(window.location.search);
      var department = urlParams.get("department") || "BSME";

      var template = coverData && coverData.template ? coverData.template : 1;

      console.log(
        "Fetching student data for department:",
        department,
        "template:",
        template
      );

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

            var pageOffset = (page - 7) * studentsPerPage;

            for (var i = 0; i < studentsPerPage; i++) {
              var studentIndex = pageOffset + i;

              if (studentIndex < students.length) {
                var student = students[studentIndex];

                var card = $("<div/>", {
                  class: "student-card",
                });

                var studentImg = $("<div/>", {
                  class: "student-image",
                });

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

                var studentName = $("<h3/>", {
                  text: student.name,
                });

                var honorsText = $("<p/>", {
                  text: student.program || "Honors and Achievements",
                });

                card.on("click", function () {
                  var modal = $(".student-modal");
                  var closeBtn = $(".close-modal");
                  var clickedStudent = students[$(this).index()];

                  modal.find(".student-name").text(clickedStudent.name);
                  modal
                    .find(".academic-year span")
                    .text(clickedStudent.year || "N/A");
                  modal
                    .find(".motto p")
                    .text(clickedStudent.motto || "No motto provided");

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

                  var $largeImage = modal.find(".student-image-large img");
                  var $thumbnails = modal.find(
                    ".student-image-thumbnails .thumbnail"
                  );

                  var modalPhotoUrl =
                    clickedStudent.photo_url ||
                    'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewBox="0 0 300 300"%3E%3Crect width="300" height="300" fill="%23f0f0f0"/%3E%3Ctext x="150" y="150" font-family="Arial" font-size="14" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3ENo Student Photo%3C/text%3E%3C/svg%3E';

                  $largeImage.attr("src", modalPhotoUrl);

                  $thumbnails.each(function (index) {
                    $(this).find("img").attr("src", modalPhotoUrl);

                    if (index === 0) {
                      $(this).addClass("active");
                    } else {
                      $(this).removeClass("active");
                    }
                  });

                  modal.addClass("active");

                  $thumbnails.off("click").on("click", function (e) {
                    e.stopPropagation(); // Prevent modal from closing
                    e.preventDefault();

                    var $this = $(this);
                    var index = $this.index();

                    $thumbnails.removeClass("active");
                    $this.addClass("active");

                    $largeImage.fadeOut(200, function () {
                      $(this).attr("src", modalPhotoUrl).fadeIn(200);
                    });
                  });

                  closeBtn.on("click", function () {
                    modal.removeClass("active");
                  });

                  $(window).on("click", function (event) {
                    if ($(event.target).hasClass("student-modal")) {
                      modal.removeClass("active");
                    }
                  });
                });

                card.append(studentImg).append(studentName).append(honorsText);

                cardsContainer.append(card);
              } else {
                var card = $("<div/>", {
                  class: "student-card empty",
                });

                var studentImg = $("<div/>", {
                  class: "student-image",
                });

                var placeholderImg = $("<img/>", {
                  src: 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="135" height="155" viewBox="0 0 135 155"%3E%3Crect width="135" height="155" fill="%23f0f0f0"/%3E%3Ctext x="67.5" y="77.5" font-family="Arial" font-size="12" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3EEmpty%3C/text%3E%3C/svg%3E',
                  alt: "Empty slot",
                });
                studentImg.append(placeholderImg);

                var studentName = $("<h3/>", {
                  text: "Available Slot",
                });

                var honorsText = $("<p/>", {
                  text: "Student data pending",
                });

                card.append(studentImg).append(studentName).append(honorsText);

                cardsContainer.append(card);
              }
            }
          } else {
            for (var i = 0; i < 6; i++) {
              var card = $("<div/>", {
                class: "student-card",
              });

              var studentImg = $("<div/>", {
                class: "student-image",
              });

              var placeholderImg = $("<img/>", {
                src: 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="135" height="155" viewBox="0 0 135 155"%3E%3Crect width="135" height="155" fill="%23f0f0f0"/%3E%3Ctext x="67.5" y="77.5" font-family="Arial" font-size="12" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3ENo Photo%3C/text%3E%3C/svg%3E',
                alt: "Student placeholder",
              });
              studentImg.append(placeholderImg);

              var studentName = $("<h3/>", {
                text: "Student Name",
              });

              var honorsText = $("<p/>", {
                text: "Honors and Achievements",
              });

              card.on("click", function () {
                var modal = $(".student-modal");
                var closeBtn = $(".close-modal");
                var studentName = $(this).find("h3").text();

                var studentPhotos = [
                  'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewBox="0 0 300 300"%3E%3Crect width="300" height="300" fill="%23f0f0f0"/%3E%3Ctext x="150" y="150" font-family="Arial" font-size="14" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3ENo Student Photo%3C/text%3E%3C/svg%3E',
                  'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewBox="0 0 300 300"%3E%3Crect width="300" height="300" fill="%23f0f0f0"/%3E%3Ctext x="150" y="150" font-family="Arial" font-size="14" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3ENo Student Photo%3C/text%3E%3C/svg%3E',
                  'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewBox="0 0 300 300"%3E%3Crect width="300" height="300" fill="%23f0f0f0"/%3E%3Ctext x="150" y="150" font-family="Arial" font-size="14" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3ENo Student Photo%3C/text%3E%3C/svg%3E',
                ];

                modal.find(".student-name").text("Student Name");
                modal.find(".motto p").text("No motto available.");
                modal
                  .find(".milestones ul")
                  .html("<li>Honors and Achievements</li>");

                var $largeImage = modal.find(".student-image-large img");
                var $thumbnails = modal.find(
                  ".student-image-thumbnails .thumbnail"
                );

                $largeImage.attr("src", studentPhotos[0]);

                $thumbnails.each(function (index) {
                  $(this).find("img").attr("src", studentPhotos[index]);

                  if (index === 0) {
                    $(this).addClass("active");
                  } else {
                    $(this).removeClass("active");
                  }
                });

                modal.addClass("active");

                $thumbnails.off("click").on("click", function (e) {
                  e.stopPropagation();
                  e.preventDefault();

                  var $this = $(this);
                  var index = $this.index();

                  $thumbnails.removeClass("active");
                  $this.addClass("active");

                  $largeImage.fadeOut(200, function () {
                    $(this).attr("src", studentPhotos[index]).fadeIn(200);
                  });

                  console.log("Switching to photo:", index + 1);
                });

                closeBtn.on("click", function () {
                  modal.removeClass("active");
                });

                $(window).on("click", function (event) {
                  if ($(event.target).hasClass("student-modal")) {
                    modal.removeClass("active");
                  }
                });
              });

              card.append(studentImg).append(studentName).append(honorsText);

              cardsContainer.append(card);
            }
          }

          pageElement.append(cardsContainer);

          setTimeout(function () {
            waitForImagesAndGenerateThumbnail(page, pageElement);
          }, 500);
        },
        error: function (xhr, status, error) {
          console.log("Error fetching student data:", error);

          for (var i = 0; i < 6; i++) {
            var card = $("<div/>", {
              class: "student-card",
            });

            var studentImg = $("<div/>", {
              class: "student-image",
            });

            var placeholderImg = $("<img/>", {
              src: 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="135" height="155" viewBox="0 0 135 155"%3E%3Crect width="135" height="155" fill="%23f0f0f0"/%3E%3Ctext x="67.5" y="77.5" font-family="Arial" font-size="12" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3ENo Photo%3C/text%3E%3C/svg%3E',
              alt: "Student placeholder",
            });
            studentImg.append(placeholderImg);

            var studentName = $("<h3/>", {
              text: "Student Name",
            });

            var honorsText = $("<p/>", {
              text: "Honors and Achievements",
            });

            card.on("click", function () {
              var modal = $(".student-modal");
              var closeBtn = $(".close-modal");
              var studentName = $(this).find("h3").text();

              var studentPhotos = [
                'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewBox="0 0 300 300"%3E%3Crect width="300" height="300" fill="%23f0f0f0"/%3E%3Ctext x="150" y="150" font-family="Arial" font-size="14" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3ENo Student Photo%3C/text%3E%3C/svg%3E',
                'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewBox="0 0 300 300"%3E%3Crect width="300" height="300" fill="%23f0f0f0"/%3E%3Ctext x="150" y="150" font-family="Arial" font-size="14" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3ENo Student Photo%3C/text%3E%3C/svg%3E',
                'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewBox="0 0 300 300"%3E%3Crect width="300" height="300" fill="%23f0f0f0"/%3E%3Ctext x="150" y="150" font-family="Arial" font-size="14" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3ENo Student Photo%3C/text%3E%3C/svg%3E',
              ];

              modal.find(".student-name").text("Student Name");
              modal.find(".motto p").text("No motto available.");
              modal
                .find(".milestones ul")
                .html("<li>Honors and Achievements</li>");

              var $largeImage = modal.find(".student-image-large img");
              var $thumbnails = modal.find(
                ".student-image-thumbnails .thumbnail"
              );

              $largeImage.attr("src", studentPhotos[0]);

              $thumbnails.each(function (index) {
                $(this).find("img").attr("src", studentPhotos[index]);

                if (index === 0) {
                  $(this).addClass("active");
                } else {
                  $(this).removeClass("active");
                }
              });

              modal.addClass("active");

              $thumbnails.off("click").on("click", function (e) {
                e.stopPropagation();
                e.preventDefault();

                var $this = $(this);
                var index = $this.index();

                $thumbnails.removeClass("active");
                $this.addClass("active");

                $largeImage.fadeOut(200, function () {
                  $(this).attr("src", studentPhotos[index]).fadeIn(200);
                });

                console.log("Switching to photo:", index + 1);
              });

              closeBtn.on("click", function () {
                modal.removeClass("active");
              });

              $(window).on("click", function (event) {
                if ($(event.target).hasClass("student-modal")) {
                  modal.removeClass("active");
                }
              });
            });

            card.append(studentImg).append(studentName).append(honorsText);

            cardsContainer.append(card);
          }

          pageElement.append(cardsContainer);

          setTimeout(function () {
            waitForImagesAndGenerateThumbnail(page, pageElement);
          }, 500);
        },
      });
    });
  } else if (
    typeof coverData !== "undefined" &&
    coverData !== null &&
    coverData.background_url
  ) {
    console.log(
      "Using background_url as fallback for page",
      page,
      ":",
      coverData.background_url
    );
    img.attr("src", coverData.background_url);
  } else {
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

function loadRegions(page, element) {
  var totalPages = $(".magazine").turn("pages");
  if (page === 1 || page === totalPages) {
    return;
  }

  $.getJSON("pages/" + page + "-regions.json").done(function (data) {
    $.each(data, function (key, region) {
      addRegion(region, element);
    });
  });
}

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

  // Disable thumbnail positioning - let CSS handle centering
  // if (marginTop < 0) {
  //   $(".thumbnails").css({ height: 1 });
  // } else {
  //   $(".thumbnails").css({ height: boundH });
  //   $(".thumbnails > div").css({ marginTop: marginTop });
  // }

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

// Generate thumbnail for a specific page
function generatePageThumbnail(page, pageElement) {
  try {
    console.log("Generating thumbnail for page", page);

    // Create a canvas to capture the page content
    var canvas = document.createElement("canvas");
    var ctx = canvas.getContext("2d");

    // Set thumbnail dimensions
    canvas.width = 76;
    canvas.height = 100;

    // Get the page content
    var pageImg = pageElement.find("img").first();
    var managementContent = pageElement.find(".top-management-page").first();
    var studentContent = pageElement.find(".cards-container").first();

    console.log("Page content found:", {
      page: page,
      hasImage: pageImg.length > 0,
      hasManagement: managementContent.length > 0,
      hasStudent: studentContent.length > 0,
      managementText: managementContent.text().substring(0, 50),
      studentCards: studentContent.find(".student-card").length,
    });

    // Start with background
    if (pageImg.length && pageImg[0].complete) {
      // Draw the background image scaled to fit
      ctx.drawImage(pageImg[0], 0, 0, canvas.width, canvas.height);
    } else {
      // Fallback background
      ctx.fillStyle = "#f8f9fa";
      ctx.fillRect(0, 0, canvas.width, canvas.height);
    }

    // Add content overlay for management pages (pages 2-6)
    if (page >= 2 && page <= 6 && managementContent.length) {
      // Create semi-transparent overlay
      ctx.fillStyle = "rgba(255, 255, 255, 0.95)";
      ctx.fillRect(2, 2, canvas.width - 4, canvas.height - 4);

      // Add border
      ctx.strokeStyle = "#007AFF";
      ctx.lineWidth = 2;
      ctx.strokeRect(2, 2, canvas.width - 4, canvas.height - 4);

      // Get management data
      var managementName =
        managementContent.find(".management-name").text().trim() ||
        "Management";
      var managementPosition =
        managementContent.find(".management-position").text().trim() ||
        "Position";
      var managementPhoto = managementContent
        .find(".management-photo img")
        .first();

      console.log("Management data extracted:", {
        name: managementName,
        position: managementPosition,
        hasPhoto: managementPhoto.length > 0,
      });

      // Draw management info
      ctx.fillStyle = "#1C1C1E";
      ctx.font = "bold 6px Arial";
      ctx.textAlign = "center";
      ctx.fillText("MANAGEMENT", canvas.width / 2, 12);

      ctx.fillStyle = "#007AFF";
      ctx.font = "5px Arial";
      ctx.fillText("Page " + page, canvas.width / 2, 20);

      // Draw management photo if available
      if (managementPhoto.length && managementPhoto[0].complete) {
        try {
          // Draw the actual management photo
          ctx.save();
          ctx.beginPath();
          ctx.arc(canvas.width / 2, 35, 10, 0, 2 * Math.PI);
          ctx.clip();
          ctx.drawImage(managementPhoto[0], canvas.width / 2 - 10, 25, 20, 20);
          ctx.restore();
        } catch (e) {
          // Fallback to placeholder circle
          ctx.fillStyle = "#E5E5EA";
          ctx.beginPath();
          ctx.arc(canvas.width / 2, 35, 10, 0, 2 * Math.PI);
          ctx.fill();
          ctx.strokeStyle = "#007AFF";
          ctx.lineWidth = 1;
          ctx.stroke();
        }
      } else {
        // Draw management photo placeholder
        ctx.fillStyle = "#E5E5EA";
        ctx.beginPath();
        ctx.arc(canvas.width / 2, 35, 10, 0, 2 * Math.PI);
        ctx.fill();
        ctx.strokeStyle = "#007AFF";
        ctx.lineWidth = 1;
        ctx.stroke();
      }

      // Draw name (truncated)
      var displayName =
        managementName.length > 10
          ? managementName.substring(0, 10) + "..."
          : managementName;
      ctx.fillStyle = "#1C1C1E";
      ctx.font = "4px Arial";
      ctx.fillText(displayName, canvas.width / 2, 50);

      // Draw position (truncated)
      var displayPosition =
        managementPosition.length > 12
          ? managementPosition.substring(0, 12) + "..."
          : managementPosition;
      ctx.fillStyle = "#8E8E93";
      ctx.font = "3px Arial";
      ctx.fillText(displayPosition, canvas.width / 2, 58);
    }

    // Add content overlay for student pages (page 7+)
    if (page >= 7 && studentContent.length) {
      // Create semi-transparent overlay
      ctx.fillStyle = "rgba(255, 255, 255, 0.95)";
      ctx.fillRect(2, 2, canvas.width - 4, canvas.height - 4);

      // Add border
      ctx.strokeStyle = "#34C759";
      ctx.lineWidth = 2;
      ctx.strokeRect(2, 2, canvas.width - 4, canvas.height - 4);

      // Count students and get student data
      var studentCards = studentContent.find(".student-card");
      var studentCount = studentCards.length;

      console.log("Student data extracted:", {
        studentCount: studentCount,
        cardsFound: studentCards.length,
      });

      // Draw student info
      ctx.fillStyle = "#1C1C1E";
      ctx.font = "bold 6px Arial";
      ctx.textAlign = "center";
      ctx.fillText("STUDENTS", canvas.width / 2, 12);

      ctx.fillStyle = "#34C759";
      ctx.font = "5px Arial";
      ctx.fillText("Page " + page, canvas.width / 2, 20);

      // Draw student count
      ctx.fillStyle = "#1C1C1E";
      ctx.font = "bold 7px Arial";
      ctx.fillText(studentCount.toString(), canvas.width / 2, 32);

      ctx.fillStyle = "#8E8E93";
      ctx.font = "4px Arial";
      ctx.fillText("students", canvas.width / 2, 40);

      // Draw mini student avatars from actual student photos
      var avatarSize = 6;
      var maxAvatars = Math.min(studentCount, 8);
      var startX = (canvas.width - maxAvatars * (avatarSize + 1)) / 2;

      studentCards.each(function (index) {
        if (index >= maxAvatars) return false; // Stop after maxAvatars

        var $card = $(this);
        var studentImg = $card.find(".student-image img").first();
        var studentName = $card.find("h3").text().trim();
        var x = startX + index * (avatarSize + 1);
        var y = 48;

        // Draw avatar circle
        ctx.fillStyle = "#E5E5EA";
        ctx.beginPath();
        ctx.arc(
          x + avatarSize / 2,
          y + avatarSize / 2,
          avatarSize / 2,
          0,
          2 * Math.PI
        );
        ctx.fill();

        ctx.strokeStyle = "#34C759";
        ctx.lineWidth = 1;
        ctx.stroke();

        // Try to draw actual student photo
        if (studentImg.length && studentImg[0].complete) {
          try {
            ctx.save();
            ctx.beginPath();
            ctx.arc(
              x + avatarSize / 2,
              y + avatarSize / 2,
              avatarSize / 2,
              0,
              2 * Math.PI
            );
            ctx.clip();
            ctx.drawImage(studentImg[0], x, y, avatarSize, avatarSize);
            ctx.restore();
          } catch (e) {
            // Fallback to initial
            ctx.fillStyle = "#1C1C1E";
            ctx.font = "3px Arial";
            ctx.textAlign = "center";
            ctx.fillText(
              (index + 1).toString(),
              x + avatarSize / 2,
              y + avatarSize / 2 + 1
            );
          }
        } else {
          // Draw student initial
          ctx.fillStyle = "#1C1C1E";
          ctx.font = "3px Arial";
          ctx.textAlign = "center";
          ctx.fillText(
            (index + 1).toString(),
            x + avatarSize / 2,
            y + avatarSize / 2 + 1
          );
        }
      });

      if (studentCount > maxAvatars) {
        ctx.fillStyle = "#8E8E93";
        ctx.font = "3px Arial";
        ctx.fillText("+" + (studentCount - maxAvatars), canvas.width / 2, 70);
      }
    }

    // Add page number for all pages
    ctx.fillStyle = "rgba(0, 0, 0, 0.8)";
    ctx.fillRect(canvas.width - 12, canvas.height - 10, 10, 8);

    ctx.fillStyle = "#FFFFFF";
    ctx.font = "5px Arial";
    ctx.textAlign = "center";
    ctx.fillText(page.toString(), canvas.width - 7, canvas.height - 4);

    // Convert canvas to data URL
    var thumbnailDataUrl = canvas.toDataURL("image/png");

    // Store thumbnail data for later use
    if (!window.pageThumbnails) {
      window.pageThumbnails = {};
    }
    window.pageThumbnails[page] = thumbnailDataUrl;

    // Update existing thumbnail if it exists
    var existingThumbnail = $(".thumbnails .page-" + page);
    if (existingThumbnail.length) {
      existingThumbnail.attr("src", thumbnailDataUrl);
      console.log("Updated thumbnail for page", page);
    }

    console.log(
      "Generated detailed thumbnail for page",
      page,
      "with content:",
      {
        hasManagement: managementContent.length > 0,
        hasStudent: studentContent.length > 0,
        hasImage: pageImg.length > 0,
      }
    );
  } catch (error) {
    console.log("Error generating thumbnail for page", page, ":", error);
  }
}

// Generate all thumbnails after pages are loaded
function generateAllThumbnails() {
  if (!window.pageThumbnails) {
    window.pageThumbnails = {};
  }

  console.log("Starting thumbnail generation for all pages...");

  // Wait a bit more to ensure all content is loaded
  setTimeout(function () {
    // Generate thumbnails for all loaded pages
    $(".magazine .page").each(function (index) {
      var pageElement = $(this);
      var pageNumber = index + 1;

      // Check if page has content before generating thumbnail
      var hasContent =
        pageElement.find("img").length > 0 ||
        pageElement.find(".top-management-page").length > 0 ||
        pageElement.find(".cards-container").length > 0;

      if (hasContent) {
        setTimeout(function () {
          console.log("Generating thumbnail for page", pageNumber);
          generatePageThumbnail(pageNumber, pageElement);
        }, index * 500); // Stagger thumbnail generation with more delay
      } else {
        console.log("Skipping page", pageNumber, "- no content found");
      }
    });
  }, 1000);
}

// Wait for images to load before generating thumbnails
function waitForImagesAndGenerateThumbnail(page, pageElement) {
  var images = pageElement.find("img");
  var loadedImages = 0;
  var totalImages = images.length;

  if (totalImages === 0) {
    // No images, generate thumbnail immediately
    generatePageThumbnail(page, pageElement);
    return;
  }

  images.each(function () {
    var img = this;
    if (img.complete) {
      loadedImages++;
    } else {
      $(img).on("load", function () {
        loadedImages++;
        if (loadedImages === totalImages) {
          generatePageThumbnail(page, pageElement);
        }
      });
    }
  });

  // If all images are already loaded
  if (loadedImages === totalImages) {
    generatePageThumbnail(page, pageElement);
  }
}

// Force regenerate all thumbnails with current content
function forceRegenerateThumbnails() {
  console.log("Force regenerating all thumbnails...");

  $(".magazine .page").each(function (index) {
    var pageElement = $(this);
    var pageNumber = index + 1;

    // Wait for images to load before generating thumbnail
    setTimeout(function () {
      waitForImagesAndGenerateThumbnail(pageNumber, pageElement);
    }, index * 300);
  });

  // Refresh thumbnails after generation
  setTimeout(function () {
    if (typeof refreshThumbnails === "function") {
      refreshThumbnails();
    }
  }, 3000);
}

// Update thumbnail generation functions to use actual page content
function createManagementThumbnail(managementIndex, dataToUse) {
  // Check if we have a generated thumbnail for this management page
  var pageNumber = managementIndex + 2;
  if (window.pageThumbnails && window.pageThumbnails[pageNumber]) {
    return window.pageThumbnails[pageNumber];
  }

  // Fallback to background image or placeholder
  var backgroundSrc =
    dataToUse.background_thumb_url || dataToUse.background_url;
  if (backgroundSrc) {
    return backgroundSrc;
  }

  // Create a placeholder that indicates management content
  return (
    'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="76" height="100" viewBox="0 0 76 100"%3E%3Crect width="76" height="100" fill="%23e8f4fd"/%3E%3Ccircle cx="38" cy="30" r="15" fill="%23ffffff" stroke="%23cccccc"/%3E%3Ctext x="38" y="35" font-family="Arial" font-size="8" fill="%23666" text-anchor="middle"%3EMGT%3C/text%3E%3Ctext x="38" y="60" font-family="Arial" font-size="6" fill="%23999" text-anchor="middle"%3EPage ' +
    pageNumber +
    "%3C/text%3E%3C/svg%3E"
  );
}

function createStudentThumbnail(studentPageIndex, dataToUse) {
  // Check if we have a generated thumbnail for this student page
  var pageNumber = studentPageIndex + 7;
  if (window.pageThumbnails && window.pageThumbnails[pageNumber]) {
    return window.pageThumbnails[pageNumber];
  }

  // Fallback to background image or placeholder
  var backgroundSrc =
    dataToUse.background_thumb_url || dataToUse.background_url;
  if (backgroundSrc) {
    return backgroundSrc;
  }

  // Create a placeholder that indicates student content
  return (
    'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="76" height="100" viewBox="0 0 76 100"%3E%3Crect width="76" height="100" fill="%23f0f8ff"/%3E%3Ccircle cx="38" cy="30" r="15" fill="%23ffffff" stroke="%23cccccc"/%3E%3Ctext x="38" y="35" font-family="Arial" font-size="8" fill="%23666" text-anchor="middle"%3ESTU%3C/text%3E%3Ctext x="38" y="60" font-family="Arial" font-size="6" fill="%23999" text-anchor="middle"%3EPage ' +
    pageNumber +
    "%3C/text%3E%3C/svg%3E"
  );
}
