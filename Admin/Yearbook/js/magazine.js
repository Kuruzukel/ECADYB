window.studentDataCache = window.studentDataCache || {};
window.studentDataPendingRequests = window.studentDataPendingRequests || {};

window.allStudentsCache = window.allStudentsCache || {};
window.allStudentsLoading = window.allStudentsLoading || {};

window.studentPhotosCache = window.studentPhotosCache || {};

window.topManagementCache = window.topManagementCache || {};
window.topManagementPendingRequests = window.topManagementPendingRequests || {};

function fetchTopManagementCached(template, callback) {
  var cacheKey = "template_" + template;

  if (window.topManagementCache[cacheKey]) {
    console.log("Using cached top management data for template", template);
    callback(window.topManagementCache[cacheKey]);
    return;
  }

  if (window.topManagementPendingRequests[cacheKey]) {
    console.log(
      "Request already pending for template",
      template,
      "- waiting for it to complete"
    );
    window.topManagementPendingRequests[cacheKey].push(callback);
    return;
  }

  window.topManagementPendingRequests[cacheKey] = [callback];

  console.log("Fetching top management data for template", template);

  $.ajax({
    url: "../../Connection/Photos/FetchTopManagement.php",
    method: "GET",
    data: {
      template: template,
    },
    dataType: "json",
    success: function (response) {
      window.topManagementCache[cacheKey] = response;

      var callbacks = window.topManagementPendingRequests[cacheKey];
      delete window.topManagementPendingRequests[cacheKey];

      callbacks.forEach(function (cb) {
        cb(response);
      });
    },
    error: function (xhr, status, error) {
      console.log("Error fetching top management data:", error);

      var callbacks = window.topManagementPendingRequests[cacheKey];
      delete window.topManagementPendingRequests[cacheKey];

      var errorResponse = {
        success: false,
        message: "Failed to fetch top management data",
        data: [],
      };

      callbacks.forEach(function (cb) {
        cb(errorResponse);
      });
    },
  });
}

function fetchStudentPhotos(studentId, callback) {
  if (!studentId) {
    console.log("No student ID provided for photo fetch");
    callback([]);
    return;
  }

  if (window.studentPhotosCache[studentId]) {
    console.log("Using cached photos for student:", studentId);
    callback(window.studentPhotosCache[studentId]);
    return;
  }

  var template = 1;
  if (typeof coverData !== "undefined" && coverData && coverData.template) {
    template = coverData.template;
  } else {
    var savedTemplate = localStorage.getItem("selectedBatchTemplateNumber");
    if (savedTemplate) {
      template = parseInt(savedTemplate);
    }
  }

  console.log("=== FETCHING PHOTOS ===");
  console.log("Student ID:", studentId);
  console.log("Template:", template);
  console.log("Timestamp:", new Date().toISOString());

  $.ajax({
    url: "../../Connection/Photos/FetchStudentPhotos.php",
    method: "GET",
    data: {
      student_id: studentId,
      template: template,
    },
    dataType: "json",
    success: function (response) {
      console.log("=== PHOTOS RESPONSE ===");
      console.log("Requested ID:", studentId);
      console.log("Response:", response);
      if (response.success && response.data && response.data.length > 0) {
        console.log("Found photos for student ID", studentId);
        console.log("Photo data:", response.data[0]);
        window.studentPhotosCache[studentId] = response.data;
        callback(response.data);
      } else {
        console.log("No photos found for student ID:", studentId);
        callback([]);
      }
    },
    error: function (xhr, status, error) {
      console.log("=== PHOTOS ERROR ===");
      console.log("Student ID:", studentId);
      console.log("Error:", error);
      console.log("XHR:", xhr);
      callback([]);
    },
  });
}

function loadAllStudentsForDepartment(department, template, callback) {
  var cacheKey = department + "_" + template;

  if (window.allStudentsCache[cacheKey]) {
    console.log(
      "Using cached all students for",
      department,
      "template",
      template
    );
    callback(window.allStudentsCache[cacheKey]);
    return;
  }

  if (window.allStudentsLoading[cacheKey]) {
    console.log(
      "All students already loading for",
      department,
      "template",
      template
    );
    var checkLoading = setInterval(function () {
      if (window.allStudentsCache[cacheKey]) {
        clearInterval(checkLoading);
        callback(window.allStudentsCache[cacheKey]);
      }
    }, 100);
    return;
  }

  window.allStudentsLoading[cacheKey] = true;

  console.log("Loading all students for", department, "template", template);
  var allStudents = [];
  var currentPage = 1;
  var totalPages = 0;

  function fetchNextPage() {
    if (currentPage === 1) {
      fetchStudentDataCached(
        department,
        template,
        currentPage,
        function (response) {
          if (response.success && response.data && response.data.students) {
            var students = response.data.students;
            totalPages = response.data.total_pages;

            allStudents = allStudents.concat(students);
            console.log(
              "Loaded page",
              currentPage,
              "of",
              totalPages,
              "- total students so far:",
              allStudents.length
            );

            currentPage++;

            if (currentPage <= totalPages) {
              fetchNextPage();
            } else {
              console.log(
                "All students loaded for",
                department,
                "template",
                template,
                "- total:",
                allStudents.length
              );
              window.allStudentsCache[cacheKey] = allStudents;
              delete window.allStudentsLoading[cacheKey];
              callback(allStudents);
            }
          } else {
            console.error("Failed to load students for page", currentPage);
            delete window.allStudentsLoading[cacheKey];
            callback([]);
          }
        }
      );
    } else {
      if (currentPage <= totalPages) {
        fetchStudentDataCached(
          department,
          template,
          currentPage,
          function (response) {
            if (response.success && response.data && response.data.students) {
              var students = response.data.students;

              allStudents = allStudents.concat(students);
              console.log(
                "Loaded page",
                currentPage,
                "of",
                totalPages,
                "- total students so far:",
                allStudents.length
              );

              currentPage++;

              if (currentPage <= totalPages) {
                fetchNextPage();
              } else {
                console.log(
                  "All students loaded for",
                  department,
                  "template",
                  template,
                  "- total:",
                  allStudents.length
                );
                window.allStudentsCache[cacheKey] = allStudents;
                delete window.allStudentsLoading[cacheKey];
                callback(allStudents);
              }
            } else {
              console.warn(
                "Failed to load students for page",
                currentPage,
                "- continuing with available students"
              );
              currentPage++;
              if (currentPage <= totalPages) {
                fetchNextPage();
              } else {
                console.log(
                  "All students loaded for",
                  department,
                  "template",
                  template,
                  "- total:",
                  allStudents.length
                );
                window.allStudentsCache[cacheKey] = allStudents;
                delete window.allStudentsLoading[cacheKey];
                callback(allStudents);
              }
            }
          }
        );
      } else {
        console.log(
          "All students loaded for",
          department,
          "template",
          template,
          "- total:",
          allStudents.length
        );
        window.allStudentsCache[cacheKey] = allStudents;
        delete window.allStudentsLoading[cacheKey];
        callback(allStudents);
      }
    }
  }

  fetchNextPage();
}

function loadStudentsForPage(
  department,
  template,
  startIndex,
  count,
  callback
) {
  var studentsPerAPIPage = 50;
  var apiPage = Math.floor(startIndex / studentsPerAPIPage) + 1;
  var localStartIndex = startIndex % studentsPerAPIPage;

  console.log(
    "Loading students starting from index",
    startIndex,
    "needing",
    count,
    "students from API page",
    apiPage
  );

  fetchStudentDataCached(department, template, apiPage, function (response) {
    if (response.success && response.data && response.data.students) {
      var students = response.data.students;
      var totalStudents = response.data.total_students;

      var studentsFromThisPage = students.slice(
        localStartIndex,
        localStartIndex + count
      );
      var studentsNeeded = count - studentsFromThisPage.length;

      if (studentsNeeded > 0 && apiPage * studentsPerAPIPage < totalStudents) {
        var nextApiPage = apiPage + 1;
        var remainingCount = studentsNeeded;

        fetchStudentDataCached(
          department,
          template,
          nextApiPage,
          function (nextResponse) {
            if (
              nextResponse.success &&
              nextResponse.data &&
              nextResponse.data.students
            ) {
              var nextStudents = nextResponse.data.students;
              var studentsFromNextPage = nextStudents.slice(0, remainingCount);
              studentsFromThisPage =
                studentsFromThisPage.concat(studentsFromNextPage);
            }

            callback(studentsFromThisPage);
          }
        );
      } else {
        callback(studentsFromThisPage);
      }
    } else {
      console.warn(
        "Failed to load students for API page",
        apiPage,
        "- returning empty array"
      );
      callback([]);
    }
  });
}

function fetchStudentDataCached(department, template, apiPage, callback) {
  var cacheKey = department + "_" + template + "_" + apiPage;

  if (window.studentDataCache[cacheKey]) {
    console.log("Using cached student data for API page", apiPage);
    callback(window.studentDataCache[cacheKey]);
    return;
  }

  if (window.studentDataPendingRequests[cacheKey]) {
    console.log(
      "Request already pending for API page",
      apiPage,
      "- waiting for it to complete"
    );
    window.studentDataPendingRequests[cacheKey].push(callback);
    return;
  }

  window.studentDataPendingRequests[cacheKey] = [callback];

  var studentsPerAPIPage = 50;

  $.ajax({
    url: "../../Connection/Photos/FetchStudentData.php",
    method: "GET",
    data: {
      department: department,
      template: template,
      page: apiPage,
      limit: studentsPerAPIPage,
    },
    dataType: "json",
    success: function (response) {
      window.studentDataCache[cacheKey] = response;

      var callbacks = window.studentDataPendingRequests[cacheKey];
      delete window.studentDataPendingRequests[cacheKey];

      callbacks.forEach(function (cb) {
        cb(response);
      });
    },
    error: function (xhr, status, error) {
      console.log(
        "Error fetching student data for API page",
        apiPage,
        ":",
        error
      );

      var callbacks = window.studentDataPendingRequests[cacheKey];
      delete window.studentDataPendingRequests[cacheKey];

      var errorResponse = {
        success: false,
        message: "Failed to fetch student data",
        data: { students: [] },
      };

      callbacks.forEach(function (cb) {
        cb(errorResponse);
      });
    },
  });
}

function addPage(page, book) {
  try {
    var id,
      pages = book.turn("pages");

    var element = $("<div />", {});

    if (book.turn("addPage", element, page)) {
      element.html('<div class="loader"></div>');

      loadPage(page, element);
    }
  } catch (e) {
    console.log("Error adding page", page, ":", e);
  }
}

function addStudentPages(book, studentData) {
  console.log(
    "Student pages are managed by turn.js 'missing' event - no manual addition needed"
  );
  return;
}

function loadPage(page, pageElement) {
  try {
    var img = $("<img />").attr("crossOrigin", "anonymous");

    img.on("mousedown", function (e) {
      e.preventDefault();
    });

    img.on("load", function () {
      $(this).css({ width: "100%", height: "100%" });

      $(this).appendTo(pageElement);

      pageElement.find(".loader").remove();
    });

    var totalPages = $(".magazine").turn("pages");

    var maxWaitTime = 5000;
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
      page <= 5 &&
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

      fetchTopManagementCached(template, function (response) {
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
              crossOrigin: "anonymous",
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

            var namePositionContainer = $("<div/>", {
              class: "management-name-position",
            });

            namePositionContainer.append(name).append(position);

            var messageContainer = $("<div/>", {
              class: "message-container",
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

            messageWrapper.append(message);
            messageContainer.append(messageWrapper);

            infoContainer.append(namePositionContainer);

            var photoAndInfoContainer = $("<div/>", {
              class: "photo-and-info-container",
            });

            photoAndInfoContainer.append(photoContainer).append(infoContainer);

            managementPage
              .append(photoAndInfoContainer)
              .append(messageContainer);

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

            var namePositionContainer = $("<div/>", {
              class: "management-name-position",
            });

            namePositionContainer.append(name).append(position);

            var messageContainer = $("<div/>", {
              class: "message-container",
            });

            var messageWrapper = $("<div/>", {
              class: "message-wrapper",
            });

            var message = $("<div/>", {
              class: "management-message modern-empty-state",
              html: `
                <div class="empty-state-container">
                  <div class="empty-state-icon">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                      <path d="M8 12h8M12 8v8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                  </div>
                  <h3 class="empty-state-title">Top Management Data Required</h3>
                  <p class="empty-state-description">Please upload CSV of the Top Management to the Batch Upload Section first.</p>
                </div>
              `
            });

            messageWrapper.append(message);
            messageContainer.append(messageWrapper);

            infoContainer.append(namePositionContainer);

            var photoAndInfoContainer = $("<div/>", {
              class: "photo-and-info-container",
            });

            photoAndInfoContainer.append(photoContainer).append(infoContainer);

            placeholderContainer
              .append(photoAndInfoContainer)
              .append(messageContainer);

            managementPage.append(placeholderContainer);

            setTimeout(function () {
              waitForImagesAndGenerateThumbnail(page, pageElement);
            }, 500);
          }
        } else {
          var errorMessage = $("<div/>", {
            class: "management-message modern-empty-state",
            html: `
              <div class="empty-state-container">
                <div class="empty-state-icon">
                  <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                    <path d="M8 12h8M12 8v8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                </div>
                <h3 class="empty-state-title">Top Management Data Required</h3>
                <p class="empty-state-description">${response.message || "Please upload CSV of the Top Management to the Batch Upload Section first."}</p>
              </div>
            `
          });
          managementPage.append(errorMessage);
        }
      });
    } else if (
      page >= 6 &&
      page < totalPages &&
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

        var studentsPerYearbookPage = 4;
        var studentStartIndex = (page - 6) * studentsPerYearbookPage;
        var studentEndIndex = studentStartIndex + studentsPerYearbookPage;

        var studentsPerAPIPage = 50;
        var apiPage = Math.floor(studentStartIndex / studentsPerAPIPage) + 1;

        console.log(
          "Loading yearbook page:",
          page,
          "Students needed:",
          studentStartIndex,
          "-",
          studentEndIndex - 1,
          "Fetching API page:",
          apiPage
        );

        var studentsPerPage = 4;
        loadStudentsForPage(
          department,
          template,
          studentStartIndex,
          studentsPerPage,
          function (studentsForThisPage) {
            console.log(
              "Students for page",
              page,
              ":",
              studentsForThisPage.length,
              "students starting from index",
              studentStartIndex
            );

            if (studentsForThisPage.length === 0) {
              var emptyMessage = $("<div/>", {
                class: "modern-empty-state",
                html: `
                  <div class="empty-state-container">
                    <div class="empty-state-icon">
                      <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                        <path d="M8 12h8M12 8v8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                      </svg>
                    </div>
                    <h3 class="empty-state-title">No Students Available</h3>
                    <p class="empty-state-description">There are no students registered for this page.</p>
                  </div>
                `
              });
              pageElement.append(emptyMessage);
              
              setTimeout(function () {
                waitForImagesAndGenerateThumbnail(page, pageElement);
              }, 500);
              return;
            }

            for (var i = 0; i < studentsForThisPage.length; i++) {
              var student = studentsForThisPage[i];
              var globalIndex = studentStartIndex + i;

              console.log("=== PROCESSING STUDENT ===");
              console.log("Global Index:", globalIndex);
              console.log("Student Name:", student.name);
              console.log("Student ID:", student.student_id);
              console.log("MongoDB ID:", student.id);
              console.log("Program:", student.program);
              console.log("Full Student Object:", student);

              var card = $("<div/>", {
                class: "student-card",
              });

              var studentImg = $("<div/>", {
                class: "student-image",
              });

              var defaultPhotoUrl =
                'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="135" height="155" viewBox="0 0 135 155"%3E%3Crect width="135" height="155" fill="%23f0f0f0"/%3E%3Ctext x="67.5" y="77.5" font-family="Arial" font-size="12" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3ENo Photo%3C/text%3E%3C/svg%3E';

              var studentPhoto = $("<img/>", {
                src: defaultPhotoUrl,
                alt: student.name,
                crossOrigin: "anonymous",
                onerror:
                  'this.src=\'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="135" height="155" viewBox="0 0 135 155"%3E%3Crect width="135" height="155" fill="%23f0f0f0"/%3E%3Ctext x="67.5" y="77.5" font-family="Arial" font-size="12" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3ENo Photo%3C/text%3E%3C/svg%3E\';',
              });

              studentImg.append(studentPhoto);

              var studentIdForPhotos = student.student_id;
              var studentNameForPhotos = student.name;
              console.log(
                "Fetching TOGA photo for student:",
                studentNameForPhotos,
                "with student_id:",
                studentIdForPhotos
              );

              if (studentIdForPhotos) {
                (function (
                  currentStudent,
                  currentPhotoElement,
                  currentStudentId,
                  currentStudentName
                ) {
                  fetchStudentPhotos(currentStudentId, function (photos) {
                    if (photos && photos.length > 0) {
                      var togaUrl = photos[0].photos.student_photo_1.url;
                      if (togaUrl) {
                        console.log(
                          "Setting TOGA photo for",
                          currentStudentName,
                          ":",
                          togaUrl
                        );
                        currentPhotoElement.attr("src", togaUrl);
                      }
                    }
                  });
                })(
                  student,
                  studentPhoto,
                  studentIdForPhotos,
                  studentNameForPhotos
                );
              }

              var studentName = $("<h3/>", {
                text: student.name,
              });

              var honorsText = $("<p/>", {
                text: student.program || "Honors and Achievements",
              });

              card.off("click").on("click", function (e) {
                e.preventDefault();
                e.stopPropagation();
                
                var modal = $(".student-modal");
                var clickedStudent = $(this).data("student");

                console.log("Card clicked for student:", clickedStudent);

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

                var defaultPhotoUrl =
                  'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewBox="0 0 300 300"%3E%3Crect width="300" height="300" fill="%23f0f0f0"/%3E%3Ctext x="150" y="150" font-family="Arial" font-size="14" fill="%23999" text-anchor="middle" dominant-baseline="middle"%3ENo Student Photo%3C/text%3E%3C/svg%3E';

                $largeImage.attr("src", defaultPhotoUrl);
                $thumbnails.each(function (index) {
                  $(this).find("img").attr("src", defaultPhotoUrl);
                  if (index === 0) {
                    $(this).addClass("active");
                  } else {
                    $(this).removeClass("active");
                  }
                });

                // Show modal
                modal.addClass("active");
                console.log("Modal should be visible now");

                var studentIdForModal = clickedStudent.student_id;
                console.log(
                  "Modal opened for student:",
                  clickedStudent.name,
                  "with student_id:",
                  studentIdForModal
                );

                if (studentIdForModal) {
                  fetchStudentPhotos(studentIdForModal, function (photos) {
                    var studentPhotos = [];

                    if (photos && photos.length > 0) {
                      var studentPhotoData = photos[0].photos;
                      studentPhotos = [
                        studentPhotoData.student_photo_1.url || defaultPhotoUrl,
                        studentPhotoData.student_photo_2.url || defaultPhotoUrl,
                        studentPhotoData.student_photo_3.url || defaultPhotoUrl,
                      ];
                    } else {
                      studentPhotos = [
                        defaultPhotoUrl,
                        defaultPhotoUrl,
                        defaultPhotoUrl,
                      ];
                    }

                    $largeImage.attr("src", studentPhotos[0]);

                    $thumbnails.each(function (index) {
                      if (studentPhotos[index]) {
                        $(this).find("img").attr("src", studentPhotos[index]);
                      }
                    });
                  });
                } else {
                  console.log("No student_id found for:", clickedStudent.name);
                }

                $thumbnails.off("click").on("click", function (e) {
                  e.stopPropagation();
                  e.preventDefault();

                  var $this = $(this);
                  var index = $this.index();

                  $thumbnails.removeClass("active");
                  $this.addClass("active");

                  var studentIdForThumbnail = clickedStudent.student_id;
                  if (studentIdForThumbnail) {
                    fetchStudentPhotos(
                      studentIdForThumbnail,
                      function (photos) {
                        var studentPhotos = [];

                        if (photos && photos.length > 0) {
                          var studentPhotoData = photos[0].photos;
                          studentPhotos = [
                            studentPhotoData.student_photo_1.url ||
                              defaultPhotoUrl,
                            studentPhotoData.student_photo_2.url ||
                              defaultPhotoUrl,
                            studentPhotoData.student_photo_3.url ||
                              defaultPhotoUrl,
                          ];
                        } else {
                          studentPhotos = [
                            defaultPhotoUrl,
                            defaultPhotoUrl,
                            defaultPhotoUrl,
                          ];
                        }

                        $largeImage.fadeOut(200, function () {
                          $(this)
                            .attr(
                              "src",
                              studentPhotos[index] || defaultPhotoUrl
                            )
                            .fadeIn(200);
                        });
                      }
                    );
                  } else {
                    console.log(
                      "No student_id found for thumbnail click:",
                      clickedStudent.name
                    );
                  }
                });
              });

              card.append(studentImg).append(studentName).append(honorsText);

              card.data("student", student);

              cardsContainer.append(card);
            }

            pageElement.append(cardsContainer);

            setTimeout(function () {
              waitForImagesAndGenerateThumbnail(page, pageElement);
            }, 500);
          }
        );
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
      img.attr(
        "src",
        "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23f0f0f0'/%3E%3Ctext x='50' y='55' font-family='Arial' font-size='12' fill='%23999' text-anchor='middle'%3EPage " +
          page +
          "%3C/text%3E%3C/svg%3E"
      );
    }

    loadRegions(page, pageElement);
  } catch (e) {
    console.log("Error loading page", page, ":", e);
  }
}

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

  if (page === 1 || page === totalPages || page >= 2) {
    return;
  }

  $.getJSON("pages/" + page + "-regions.json")
    .done(function (data) {
      $.each(data, function (key, region) {
        addRegion(region, element);
      });
    })
    .fail(function () {});
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
  var img = $("<img />").attr("crossOrigin", "anonymous");

  img.on("load", function () {
    var prevImg = pageElement.find("img");
    $(this).css({ width: "100%", height: "100%" });
    $(this).appendTo(pageElement);
    prevImg.remove();
  });

  var totalPages = $(".magazine").turn("pages");

  var maxWaitTime = 5000;
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
    img.attr("src", coverData.front_url);
  } else if (
    page === totalPages &&
    typeof coverData !== "undefined" &&
    coverData !== null &&
    coverData.back_url
  ) {
    img.attr("src", coverData.back_url);
  } else if (
    page >= 2 &&
    page <= 5 &&
    typeof coverData !== "undefined" &&
    coverData !== null
  ) {
    if (coverData.background_url) {
      img.attr("src", coverData.background_url);
    } else {
      img.attr(
        "src",
        "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23ffffff'/%3E%3C/svg%3E"
      );
    }
  } else if (
    page >= 6 &&
    page < totalPages &&
    typeof coverData !== "undefined" &&
    coverData !== null &&
    coverData.background_url
  ) {
    img.attr("src", coverData.background_url);
  } else {
    console.log("No image available for page:", page, "Showing placeholder");
    img.attr(
      "src",
      "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23f0f0f0'/%3E%3Ctext x='50' y='55' font-family='Arial' font-size='12' fill='%23999' text-anchor='middle'%3ELarge Page " +
        page +
        "%3C/text%3E%3C/svg%3E"
    );
  }
}

function loadSmallPage(page, pageElement) {
  var img = pageElement.find("img");

  img.css({ width: "100%", height: "100%" });

  img.attr("crossOrigin", "anonymous");

  img.off("load");

  var totalPages = $(".magazine").turn("pages");

  var maxWaitTime = 5000;
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
    img.attr("src", coverData.front_url);
  } else if (
    page === totalPages &&
    typeof coverData !== "undefined" &&
    coverData !== null &&
    coverData.back_url
  ) {
    img.attr("src", coverData.back_url);
  } else if (
    page >= 2 &&
    page <= 5 &&
    typeof coverData !== "undefined" &&
    coverData !== null
  ) {
    if (coverData.background_url) {
      img.attr("src", coverData.background_url);
    } else {
      img.attr(
        "src",
        "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23ffffff'/%3E%3C/svg%3E"
      );
    }
  } else if (
    page >= 6 &&
    page < totalPages &&
    typeof coverData !== "undefined" &&
    coverData !== null &&
    coverData.background_url
  ) {
    img.attr("src", coverData.background_url);
  } else {
    console.log("No image available for page:", page, "Showing placeholder");
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

  if (magazineOffset.top < $(".made").height()) $(".made").hide();
  else $(".made").show();

  $(".magazine").addClass("animated");
}

function numberOfViews(book) {
  return book.turn("pages") / 2 + 1;
}

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

function largeMagazineWidth() {
  return 2214;
}

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

function generatePageThumbnail(page, pageElement) {
  try {
    console.log("Generating thumbnail for page", page);

    var canvas = document.createElement("canvas");
    var ctx = canvas.getContext("2d");

    canvas.width = 76;
    canvas.height = 100;

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

    if (pageImg.length && pageImg[0].complete) {
      ctx.drawImage(pageImg[0], 0, 0, canvas.width, canvas.height);
    } else {
      ctx.fillStyle = "#f8f9fa";
      ctx.fillRect(0, 0, canvas.width, canvas.height);
    }

    if (page >= 2 && page <= 5 && managementContent.length) {
      ctx.fillStyle = "rgba(255, 255, 255, 0.95)";
      ctx.fillRect(2, 2, canvas.width - 4, canvas.height - 4);

      ctx.strokeStyle = "#007AFF";
      ctx.lineWidth = 2;
      ctx.strokeRect(2, 2, canvas.width - 4, canvas.height - 4);

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

      ctx.fillStyle = "#1C1C1E";
      ctx.font = "bold 6px Arial";
      ctx.textAlign = "center";
      ctx.fillText("MANAGEMENT", canvas.width / 2, 12);

      ctx.fillStyle = "#007AFF";
      ctx.font = "5px Arial";
      ctx.fillText("Page " + page, canvas.width / 2, 20);

      if (managementPhoto.length && managementPhoto[0].complete) {
        try {
          ctx.save();
          ctx.beginPath();
          ctx.arc(canvas.width / 2, 35, 10, 0, 2 * Math.PI);
          ctx.clip();
          ctx.drawImage(managementPhoto[0], canvas.width / 2 - 10, 25, 20, 20);
          ctx.restore();
        } catch (e) {
          ctx.fillStyle = "#E5E5EA";
          ctx.beginPath();
          ctx.arc(canvas.width / 2, 35, 10, 0, 2 * Math.PI);
          ctx.fill();
          ctx.strokeStyle = "#007AFF";
          ctx.lineWidth = 1;
          ctx.stroke();
        }
      } else {
        ctx.fillStyle = "#E5E5EA";
        ctx.beginPath();
        ctx.arc(canvas.width / 2, 35, 10, 0, 2 * Math.PI);
        ctx.fill();
        ctx.strokeStyle = "#007AFF";
        ctx.lineWidth = 1;
        ctx.stroke();
      }

      var displayName =
        managementName.length > 10
          ? managementName.substring(0, 10) + "..."
          : managementName;
      ctx.fillStyle = "#1C1C1E";
      ctx.font = "4px Arial";
      ctx.fillText(displayName, canvas.width / 2, 50);

      var displayPosition =
        managementPosition.length > 12
          ? managementPosition.substring(0, 12) + "..."
          : managementPosition;
      ctx.fillStyle = "#8E8E93";
      ctx.font = "3px Arial";
      ctx.fillText(displayPosition, canvas.width / 2, 58);
    }

    if (page >= 6 && studentContent.length) {
      ctx.fillStyle = "rgba(255, 255, 255, 0.95)";
      ctx.fillRect(2, 2, canvas.width - 4, canvas.height - 4);

      ctx.strokeStyle = "#34C759";
      ctx.lineWidth = 2;
      ctx.strokeRect(2, 2, canvas.width - 4, canvas.height - 4);

      var studentCards = studentContent.find(".student-card");
      var studentCount = studentCards.length;

      console.log("Student data extracted:", {
        studentCount: studentCount,
        cardsFound: studentCards.length,
      });

      ctx.fillStyle = "#1C1C1E";
      ctx.font = "bold 6px Arial";
      ctx.textAlign = "center";
      ctx.fillText("STUDENTS", canvas.width / 2, 12);

      ctx.fillStyle = "#34C759";
      ctx.font = "5px Arial";
      ctx.fillText("Page " + page, canvas.width / 2, 20);

      ctx.fillStyle = "#1C1C1E";
      ctx.font = "bold 7px Arial";
      ctx.fillText(studentCount.toString(), canvas.width / 2, 32);

      ctx.fillStyle = "#8E8E93";
      ctx.font = "4px Arial";
      ctx.fillText("students", canvas.width / 2, 40);

      var avatarSize = 6;
      var maxAvatars = Math.min(studentCount, 8);
      var startX = (canvas.width - maxAvatars * (avatarSize + 1)) / 2;

      studentCards.each(function (index) {
        if (index >= maxAvatars) return false;

        var $card = $(this);
        var studentImg = $card.find(".student-image img").first();
        var studentName = $card.find("h3").text().trim();
        var x = startX + index * (avatarSize + 1);
        var y = 48;

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

    ctx.fillStyle = "rgba(0, 0, 0, 0.8)";
    ctx.fillRect(canvas.width - 12, canvas.height - 10, 10, 8);

    ctx.fillStyle = "#FFFFFF";
    ctx.font = "5px Arial";
    ctx.textAlign = "center";
    ctx.fillText(page.toString(), canvas.width - 7, canvas.height - 4);

    var thumbnailDataUrl = canvas.toDataURL("image/png");

    if (!window.pageThumbnails) {
      window.pageThumbnails = {};
    }
    window.pageThumbnails[page] = thumbnailDataUrl;

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

function generateAllThumbnails() {
  if (!window.pageThumbnails) {
    window.pageThumbnails = {};
  }

  console.log("Starting optimized thumbnail generation...");

  setTimeout(function () {
    $(".magazine .page").each(function (index) {
      var pageElement = $(this);
      var pageNumber = index + 1;

      var hasContent =
        pageElement.find("img").length > 0 ||
        pageElement.find(".top-management-page").length > 0 ||
        pageElement.find(".cards-container").length > 0;

      if (hasContent) {
        var delay = pageNumber <= 3 ? index * 200 : index * 800;
        setTimeout(function () {
          console.log("Generating thumbnail for page", pageNumber);
          generatePageThumbnail(pageNumber, pageElement);
        }, delay);
      } else {
        console.log("Skipping page", pageNumber, "- no content found");
      }
    });
  }, 2000);
}

function waitForImagesAndGenerateThumbnail(page, pageElement) {
  var images = pageElement.find("img");
  var loadedImages = 0;
  var totalImages = images.length;

  if (totalImages === 0) {
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

  if (loadedImages === totalImages) {
    generatePageThumbnail(page, pageElement);
  }
}

function forceRegenerateThumbnails() {
  console.log("Force regenerating all thumbnails...");

  $(".magazine .page").each(function (index) {
    var pageElement = $(this);
    var pageNumber = index + 1;

    setTimeout(function () {
      waitForImagesAndGenerateThumbnail(pageNumber, pageElement);
    }, index * 300);
  });

  setTimeout(function () {
    if (typeof refreshThumbnails === "function") {
      refreshThumbnails();
    }
  }, 3000);
}

function createManagementThumbnail(managementIndex, dataToUse) {
  var pageNumber = managementIndex + 2;
  if (window.pageThumbnails && window.pageThumbnails[pageNumber]) {
    return window.pageThumbnails[pageNumber];
  }

  var backgroundSrc =
    dataToUse.background_thumb_url || dataToUse.background_url;
  if (backgroundSrc) {
    return backgroundSrc;
  }

  return (
    'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="76" height="100" viewBox="0 0 76 100"%3E%3Crect width="76" height="100" fill="%23e8f4fd"/%3E%3Ccircle cx="38" cy="30" r="15" fill="%23ffffff" stroke="%23cccccc"/%3E%3Ctext x="38" y="35" font-family="Arial" font-size="8" fill="%23666" text-anchor="middle"%3EMGT%3C/text%3E%3Ctext x="38" y="60" font-family="Arial" font-size="6" fill="%23999" text-anchor="middle"%3EPage ' +
    pageNumber +
    "%3C/text%3E%3C/svg%3E"
  );
}

function createStudentThumbnail(studentPageIndex, dataToUse) {
  var pageNumber = studentPageIndex + 6;
  if (window.pageThumbnails && window.pageThumbnails[pageNumber]) {
    return window.pageThumbnails[pageNumber];
  }

  var backgroundSrc =
    dataToUse.background_thumb_url || dataToUse.background_url;
  if (backgroundSrc) {
    return backgroundSrc;
  }

  return (
    'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="76" height="100" viewBox="0 0 76 100"%3E%3Crect width="76" height="100" fill="%23f0f8ff"/%3E%3Ccircle cx="38" cy="30" r="15" fill="%23ffffff" stroke="%23cccccc"/%3E%3Ctext x="38" y="35" font-family="Arial" font-size="8" fill="%23666" text-anchor="middle"%3ESTU%3C/text%3E%3Ctext x="38" y="60" font-family="Arial" font-size="6" fill="%23999" text-anchor="middle"%3EPage ' +
    pageNumber +
    "%3C/text%3E%3C/svg%3E"
  );
}

function initializeCornerHover() {
  var $magazine = $(".magazine");
  var cornerSize = 100;
  var currentPeelCorner = null;
  var peelTimer = null;
  var peelDuration = 600;
  var isPageTurning = false;

  $magazine.on("mousemove", function (e) {
    if (!$magazine.turn("is") || isPageTurning) return;

    var offset = $magazine.offset();
    var relX = e.pageX - offset.left;
    var relY = e.pageY - offset.top;
    var width = $magazine.width();
    var height = $magazine.height();
    var page = $magazine.turn("page");
    var pages = $magazine.turn("pages");

    var inCorner = false;
    var corner = null;

    if (relX > width - cornerSize && relY > height - cornerSize) {
      if (page < pages) {
        corner = "br";
        inCorner = true;
      }
    } else if (relX < cornerSize && relY > height - cornerSize) {
      if (page > 1) {
        corner = "bl";
        inCorner = true;
      }
    } else if (relX > width - cornerSize && relY < cornerSize) {
      if (page < pages) {
        corner = "tr";
        inCorner = true;
      }
    } else if (relX < cornerSize && relY < cornerSize) {
      if (page > 1) {
        corner = "tl";
        inCorner = true;
      }
    }

    if (inCorner && corner !== currentPeelCorner) {
      if (peelTimer) {
        clearTimeout(peelTimer);
      }

      $magazine.turn("peel", corner);
      currentPeelCorner = corner;

      peelTimer = setTimeout(function () {
        $magazine.turn("peel", false);
        currentPeelCorner = null;
        peelTimer = null;
      }, peelDuration);
    } else if (!inCorner && currentPeelCorner) {
      if (peelTimer) {
        clearTimeout(peelTimer);
        peelTimer = null;
      }
      $magazine.turn("peel", false);
      currentPeelCorner = null;
    }
  });

  $magazine.on("click", function (e) {
    if (!$magazine.turn("is") || isPageTurning) return;

    var offset = $magazine.offset();
    var relX = e.pageX - offset.left;
    var relY = e.pageY - offset.top;
    var width = $magazine.width();
    var height = $magazine.height();
    var page = $magazine.turn("page");
    var pages = $magazine.turn("pages");

    var inCorner = false;
    var shouldGoNext = false;
    var shouldGoPrevious = false;

    // Check which corner was clicked
    if (relX > width - cornerSize && relY > height - cornerSize) {
      if (page < pages) {
        inCorner = true;
        shouldGoNext = true;
      }
    } else if (relX < cornerSize && relY > height - cornerSize) {
      if (page > 1) {
        inCorner = true;
        shouldGoPrevious = true;
      }
    } else if (relX > width - cornerSize && relY < cornerSize) {
      if (page < pages) {
        inCorner = true;
        shouldGoNext = true;
      }
    } else if (relX < cornerSize && relY < cornerSize) {
      if (page > 1) {
        inCorner = true;
        shouldGoPrevious = true;
      }
    }

    if (inCorner) {
      // Clear peel effect
      if (peelTimer) {
        clearTimeout(peelTimer);
        peelTimer = null;
      }
      $magazine.turn("peel", false);
      currentPeelCorner = null;

      // Turn page
      isPageTurning = true;
      if (shouldGoNext) {
        $magazine.turn("next");
      } else if (shouldGoPrevious) {
        $magazine.turn("previous");
      }

      // Reset turning flag after animation
      setTimeout(function () {
        isPageTurning = false;
      }, 1000);
    }
  });

  $magazine.on("mouseleave", function () {
    if (peelTimer) {
      clearTimeout(peelTimer);
      peelTimer = null;
    }
    if (currentPeelCorner) {
      $magazine.turn("peel", false);
      currentPeelCorner = null;
    }
  });

  // Clear peel when page is turning
  $magazine.on("turning", function () {
    if (peelTimer) {
      clearTimeout(peelTimer);
      peelTimer = null;
    }
    if (currentPeelCorner) {
      $magazine.turn("peel", false);
      currentPeelCorner = null;
    }
    isPageTurning = true;
    
    // Hide book binding shadow during page turning
    $magazine.addClass("turning");
  });

  $magazine.on("turned", function () {
    isPageTurning = false;
    
    // Show book binding shadow after page turn is complete
    $magazine.removeClass("turning");
  });
}
