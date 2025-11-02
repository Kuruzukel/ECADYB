window.basePath = window.location.pathname.includes("/ECADYB/")
  ? "/ECADYB"
  : "";

(function () {
  var urlParams = new URLSearchParams(window.location.search);
  var batchYearFromUrl = urlParams.get("batchYear");

  if (batchYearFromUrl) {
    console.log("Setting batch year from URL parameter:", batchYearFromUrl);
    localStorage.setItem("selectedBatchYear", batchYearFromUrl);
    window.topManagementCache = {};
    window.topManagementPendingRequests = {};
    window.studentDataCache = {};
    window.studentDataPendingRequests = {};
    window.allStudentsCache = {};
    window.studentPhotosCache = {};
  } else {
    console.log(
      "No batch year in URL, using localStorage:",
      localStorage.getItem("selectedBatchYear")
    );
  }
})();

window.studentDataCache = window.studentDataCache || {};
window.studentDataPendingRequests = window.studentDataPendingRequests || {};

window.allStudentsCache = window.allStudentsCache || {};
window.allStudentsLoading = window.allStudentsLoading || {};

window.studentPhotosCache = window.studentPhotosCache || {};

window.topManagementCache = window.topManagementCache || {};
window.topManagementPendingRequests = window.topManagementPendingRequests || {};

window.currentYearbookDepartment = window.currentYearbookDepartment || null;

function isMaritimeContext(program, department) {
  var maritimeKeywords = [
    "BSME",
    "BSMT",
    "MARITIME",
    "MARINE TRANSPORTATION",
    "MARINE ENGINEERING",
  ];

  var programValue = (program || "").toString().toUpperCase();
  var departmentValue = (department || window.currentYearbookDepartment || "")
    .toString()
    .toUpperCase();

  return maritimeKeywords.some(function (keyword) {
    return (
      programValue.indexOf(keyword) !== -1 ||
      departmentValue.indexOf(keyword) !== -1
    );
  });
}

function getPreferredPhotoOrder(program, department) {
  if (isMaritimeContext(program, department)) {
    return ["dwhite", "khaki", "coverall", "toga", "uniform", "filipiniana"];
  }

  return ["toga", "uniform", "filipiniana", "dwhite", "khaki", "coverall"];
}

function buildOrderedPhotoList(rawPhotos, preferredOrder) {
  var DEFAULT_ORDER = [
    "toga",
    "uniform",
    "filipiniana",
    "dwhite",
    "khaki",
    "coverall",
  ];

  var order = (
    preferredOrder && preferredOrder.length ? preferredOrder : DEFAULT_ORDER
  ).map(function (type) {
    return type.toLowerCase();
  });

  DEFAULT_ORDER.forEach(function (type) {
    if (order.indexOf(type) === -1) {
      order.push(type);
    }
  });

  var photoMap = new Map();

  if (Array.isArray(rawPhotos)) {
    rawPhotos.forEach(function (record) {
      if (record && record.photos) {
        Object.keys(record.photos).forEach(function (key) {
          var photo = record.photos[key];
          if (photo && photo.url) {
            var photoType = (photo.type || "").toString().toLowerCase();
            if (!photoType) return;

            if (!photoMap.has(photoType)) {
              photoMap.set(photoType, {
                type: photoType,
                url: photo.url,
                filename: photo.filename || "",
                originalName: photo.original_name || "",
              });
            }
          }
        });
      }
    });
  }

  var ordered = [];
  var seen = new Set();

  order.forEach(function (type) {
    if (photoMap.has(type)) {
      var photo = photoMap.get(type);
      if (!seen.has(photo.url)) {
        ordered.push(photo);
        seen.add(photo.url);
      }
    }
  });

  photoMap.forEach(function (photo) {
    if (!seen.has(photo.url)) {
      ordered.push(photo);
      seen.add(photo.url);
    }
  });

  return ordered;
}

function clearTopManagementCache() {
  console.log("Clearing top management cache due to batch year change");
  window.topManagementCache = {};
  window.topManagementPendingRequests = {};
}

function fetchTopManagementCached(template, callback) {
  var batchYear = localStorage.getItem("selectedBatchYear");
  var cacheKey = "template_" + template + "_" + (batchYear || "default");

  if (window.topManagementCache[cacheKey]) {
    console.log(
      "Using cached top management data for template",
      template,
      "batch year",
      batchYear
    );
    callback(window.topManagementCache[cacheKey]);
    return;
  }

  if (window.topManagementPendingRequests[cacheKey]) {
    console.log(
      "Request already pending for template",
      template,
      "batch year",
      batchYear,
      "- waiting for it to complete"
    );
    window.topManagementPendingRequests[cacheKey].push(callback);
    return;
  }

  window.topManagementPendingRequests[cacheKey] = [callback];

  console.log(
    "Fetching top management data for template",
    template,
    "batch year",
    batchYear
  );

  // Build request data
  var requestData = {
    template: template,
  };
  if (batchYear) {
    requestData.batch_year = batchYear;
  }

  console.log("=== FETCHING TOP MANAGEMENT ===");
  console.log("Template:", template);
  console.log("Batch Year:", batchYear);
  console.log("Request Data:", requestData);
  console.log(
    "Request URL:",
    window.basePath + "/Connection/Photos/FetchTopManagement.php"
  );

  $.ajax({
    url: window.basePath + "/Connection/Photos/FetchTopManagement.php",
    method: "GET",
    data: requestData,
    dataType: "json",
    success: function (response) {
      console.log("=== TOP MANAGEMENT RESPONSE ===");
      console.log("Response:", response);
      console.log("Success:", response.success);
      console.log(
        "Data length:",
        response.data ? response.data.length : "no data"
      );
      console.log("Data:", response.data);

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

function fetchStudentPhotos(studentId, options, callback) {
  if (typeof options === "function") {
    callback = options;
    options = {};
  }

  options = options || {};

  var department = options.department || window.currentYearbookDepartment;
  var preferredOrder = options.preferredOrder;

  if (!preferredOrder || !preferredOrder.length) {
    preferredOrder = getPreferredPhotoOrder(options.program || "", department);
  }

  function respond(rawPhotos, success, message) {
    var ordered = buildOrderedPhotoList(rawPhotos, preferredOrder);
    if (typeof callback === "function") {
      callback({
        success: success,
        photos: ordered,
        raw: rawPhotos,
        message: message || "",
      });
    }
  }

  if (!studentId) {
    console.log("No student ID provided for photo fetch");
    respond([], false, "Missing student ID");
    return;
  }

  if (window.studentPhotosCache[studentId]) {
    console.log("Using cached photos for student:", studentId);
    respond(window.studentPhotosCache[studentId], true);
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

  var batchYear = localStorage.getItem("selectedBatchYear");

  console.log("=== FETCHING PHOTOS ===");
  console.log("Student ID:", studentId);
  console.log("Template:", template);
  console.log("Batch Year:", batchYear);
  console.log("Timestamp:", new Date().toISOString());

  var requestData = {
    student_id: studentId,
    template: template,
  };
  if (batchYear) {
    requestData.batch_year = batchYear;
  }

  $.ajax({
    url: window.basePath + "/Connection/Photos/FetchStudentPhotos.php",
    method: "GET",
    data: requestData,
    dataType: "json",
    timeout: 10000,
    success: function (response) {
      console.log("=== PHOTOS RESPONSE ===");
      console.log("Requested ID:", studentId);
      console.log("Response:", response);

      if (!response || typeof response !== "object") {
        console.error(
          "Invalid or empty response received from FetchStudentPhotos.php"
        );
        respond([], false, "Invalid response");
        return;
      }

      if (!response.hasOwnProperty("success")) {
        console.error("Response missing 'success' property");
        respond([], false, "Malformed response");
        return;
      }

      var rawPhotos =
        response.data && Array.isArray(response.data) ? response.data : [];

      if (response.success && rawPhotos.length > 0) {
        console.log("Found photos for student ID", studentId);
        console.log("Photo data:", rawPhotos[0]);
        window.studentPhotosCache[studentId] = rawPhotos;
        respond(rawPhotos, true, response.message);
      } else {
        console.log(
          "No photos found for student ID:",
          studentId,
          "Response message:",
          response.message || "No message"
        );
        window.studentPhotosCache[studentId] = rawPhotos;
        respond(rawPhotos, false, response.message);
      }
    },
    error: function (xhr, status, error) {
      console.log("=== PHOTOS ERROR ===");
      console.log("Student ID:", studentId);
      console.log("Error:", error);
      console.log("Status:", status);
      console.log("XHR:", xhr);
      console.log("XHR Response Text:", xhr.responseText);
      respond([], false, error || status || "Network error");
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
    // Enhanced error handling and logging
    if (
      response &&
      response.success &&
      response.data &&
      Array.isArray(response.data.students)
    ) {
      var students = response.data.students;
      var totalStudents = response.data.total_students || 0;

      console.log(
        "Successfully loaded",
        students.length,
        "students for API page",
        apiPage
      );

      var studentsFromThisPage = students.slice(
        localStartIndex,
        localStartIndex + count
      );
      var studentsNeeded = count - studentsFromThisPage.length;

      if (studentsNeeded > 0 && apiPage * studentsPerAPIPage < totalStudents) {
        var nextApiPage = apiPage + 1;
        var remainingCount = studentsNeeded;

        console.log(
          "Need additional",
          studentsNeeded,
          "students from next API page",
          nextApiPage
        );

        fetchStudentDataCached(
          department,
          template,
          nextApiPage,
          function (nextResponse) {
            // Enhanced error handling for next page
            if (
              nextResponse &&
              nextResponse.success &&
              nextResponse.data &&
              Array.isArray(nextResponse.data.students)
            ) {
              var nextStudents = nextResponse.data.students;
              var studentsFromNextPage = nextStudents.slice(0, remainingCount);
              studentsFromThisPage =
                studentsFromThisPage.concat(studentsFromNextPage);

              console.log(
                "Successfully loaded additional",
                studentsFromNextPage.length,
                "students from API page",
                nextApiPage
              );
            } else {
              console.warn(
                "Failed to load additional students for API page",
                nextApiPage,
                "Response:",
                nextResponse
              );
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
        "- returning empty array. Response received:",
        response
      );

      // Try to provide more context about the failure
      if (response) {
        if (!response.success) {
          console.warn(
            "API returned success=false with message:",
            response.message
          );
        } else if (!response.data) {
          console.warn("API returned no data object");
        } else if (!Array.isArray(response.data.students)) {
          console.warn(
            "API returned data.students but it's not an array:",
            typeof response.data.students
          );
        }
      } else {
        console.warn("No response received from API");
      }

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

  // Get batch year from localStorage
  var batchYear = localStorage.getItem("selectedBatchYear");

  // Build request data
  var requestData = {
    department: department,
    template: template,
    page: apiPage,
    limit: studentsPerAPIPage,
  };
  if (batchYear) {
    requestData.batch_year = batchYear;
  }

  console.log(
    "Making request to FetchStudentData.php with parameters:",
    requestData
  );

  $.ajax({
    url: window.basePath + "/Connection/Photos/FetchStudentData.php",
    method: "GET",
    data: requestData,
    dataType: "json",
    timeout: 10000, // 10 second timeout
    success: function (response) {
      console.log("=== STUDENT DATA RESPONSE ===");
      console.log("API Page:", apiPage);
      console.log("Department:", department);
      console.log("Template:", template);
      console.log("Batch Year:", batchYear);
      console.log("Response:", response);

      // Validate response structure
      if (!response) {
        console.error("Empty response received from FetchStudentData.php");
        response = {
          success: false,
          message: "Empty response received from server",
          data: { students: [] },
        };
      } else if (typeof response !== "object") {
        console.error(
          "Invalid response format received from FetchStudentData.php:",
          typeof response
        );
        response = {
          success: false,
          message: "Invalid response format received from server",
          data: { students: [] },
        };
      } else if (!response.hasOwnProperty("success")) {
        console.error("Response missing 'success' property");
        response = {
          success: false,
          message: "Response missing required properties",
          data: { students: [] },
        };
      }

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
      console.log("XHR Response:", xhr.responseText);
      console.log("XHR Status:", xhr.status);
      console.log(
        "Request URL:",
        window.basePath + "/Connection/Photos/FetchStudentData.php"
      );
      console.log("Request Data:", requestData);
      console.log("XHR Object:", xhr);

      var callbacks = window.studentDataPendingRequests[cacheKey];
      delete window.studentDataPendingRequests[cacheKey];

      var errorResponse = {
        success: false,
        message:
          "Failed to fetch student data - " +
          status +
          (error ? ": " + error : ""),
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

    // Add page number to bottom of page (excluding front and back covers)
    if (page > 1 && page < totalPages) {
      var pageNumberDiv = $("<div/>", {
        class: "page-number",
        text: page,
      });
      pageElement.append(pageNumberDiv);
    }

    if (
      page === 1 &&
      typeof coverData !== "undefined" &&
      coverData !== null &&
      coverData.front_url
    ) {
      console.log("Using front_url for page 1:", coverData.front_url);
      img.attr("src", coverData.front_url);
    } else if (page === 1) {
      console.log("Using default front cover for page 1");
      img.attr("src", "https://ECADYB.b-cdn.net/img/YBCOVERFRONT%20.png");
    } else if (
      page === totalPages &&
      typeof coverData !== "undefined" &&
      coverData !== null &&
      coverData.back_url
    ) {
      console.log("Using back_url for page", page, ":", coverData.back_url);
      img.attr("src", coverData.back_url);
    } else if (page === totalPages) {
      console.log("Using default back cover for page", page);
      img.attr("src", "https://ECADYB.b-cdn.net/img/YBCOVERFRONT%20.png");
    } else if (
      page >= 2 &&
      page < totalPages &&
      typeof coverData !== "undefined" &&
      coverData !== null
    ) {
      // Determine if this should be a management page or student page
      var template = 1;
      if (coverData.template) {
        template = coverData.template;
      }

      var batchYear = localStorage.getItem("selectedBatchYear");
      var cacheKey = "template_" + template + "_" + (batchYear || "default");
      var managementPages = 2; // Default placeholder pages

      if (window.topManagementCache && window.topManagementCache[cacheKey]) {
        var topManagementData = window.topManagementCache[cacheKey];
        if (
          topManagementData &&
          topManagementData.success &&
          topManagementData.data &&
          topManagementData.data.length > 0
        ) {
          managementPages = topManagementData.data.length;
        }
      }

      var isManagementPage = page < 2 + managementPages;

      if (isManagementPage) {
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
          console.log("Using default background for management page", page);
          img.attr("src", "https://ECADYB.b-cdn.net/img/YB%20BG..png");
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

        var managementIndex = page - 2;

        fetchTopManagementCached(template, function (response) {
          console.log("=== TOP MANAGEMENT PAGE LOADING ===");
          console.log("Page:", page);
          console.log("Management Index:", managementIndex);
          console.log("Template:", template);
          console.log("Response:", response);
          console.log("Response Success:", response.success);
          console.log("Response Data:", response.data);
          console.log(
            "Data Length:",
            response.data ? response.data.length : "no data"
          );

          loadingIndicator.remove();

          if (response.success && response.data && response.data.length > 0) {
            console.log(
              "Top management data available, checking index:",
              managementIndex
            );
            if (managementIndex < response.data.length) {
              var currentManager = response.data[managementIndex];
              console.log("Current Manager:", currentManager);

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

              photoAndInfoContainer
                .append(photoContainer)
                .append(infoContainer);

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
              `,
              });

              messageWrapper.append(message);
              messageContainer.append(messageWrapper);

              infoContainer.append(namePositionContainer);

              var photoAndInfoContainer = $("<div/>", {
                class: "photo-and-info-container",
              });

              photoAndInfoContainer
                .append(photoContainer)
                .append(infoContainer);

              placeholderContainer
                .append(photoAndInfoContainer)
                .append(messageContainer);

              managementPage.append(placeholderContainer);

              setTimeout(function () {
                waitForImagesAndGenerateThumbnail(page, pageElement);
              }, 500);
            }
          } else {
            console.log("No top management data available");
            console.log("Response success:", response.success);
            console.log("Response message:", response.message);
            console.log("Response data:", response.data);

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
                <p class="empty-state-description">${
                  response.message ||
                  "Please upload CSV of the Top Management to the Batch Upload Section first."
                }</p>
              </div>
            `,
            });
            managementPage.append(errorMessage);
          }
        });
      } else {
        // This is a student page
        console.log("Loading student page:", page);

        if (coverData.background_url) {
          console.log(
            "Using background_url for student page",
            page,
            ":",
            coverData.background_url
          );
          img.attr("src", coverData.background_url);
        } else {
          console.log("Using default background for student page", page);
          img.attr("src", "https://ECADYB.b-cdn.net/img/YB%20BG..png");
        }

        img.on("load", function () {
          var cardsContainer = $("<div/>", {
            class: "cards-container",
          });

          var urlParams = new URLSearchParams(window.location.search);
          var department = urlParams.get("department") || "BSME";

          var template =
            coverData && coverData.template ? coverData.template : 1;

          var studentsPerYearbookPage = 4;
          var studentStartIndex =
            (page - (2 + managementPages)) * studentsPerYearbookPage;
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
                studentsForThisPage ? studentsForThisPage.length : "undefined",
                "students starting from index",
                studentStartIndex
              );

              // Enhanced error handling for student data
              if (!studentsForThisPage || !Array.isArray(studentsForThisPage)) {
                console.error(
                  "Invalid student data received for page",
                  page,
                  ":",
                  studentsForThisPage
                );
                var errorMessage = $("<div/>", {
                  class: "modern-empty-state",
                  html: `
                  <div class="empty-state-container">
                    <div class="empty-state-icon">
                      <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                        <path d="M8 12h8M12 8v8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                      </svg>
                    </div>
                    <h3 class="empty-state-title">Data Loading Error</h3>
                    <p class="empty-state-description">Failed to load student data for this page.</p>
                  </div>
                `,
                });
                pageElement.append(errorMessage);

                setTimeout(function () {
                  waitForImagesAndGenerateThumbnail(page, pageElement);
                }, 500);
                return;
              }

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
                    <h3 class="empty-state-title">Student Data Required</h3>
                    <p class="empty-state-description">No student data found for academic year ${getCurrentBatchYear()}. Please upload CSV of the Students to the Batch Upload Section first.</p>
                  </div>
                `,
                });
                pageElement.append(emptyMessage);

                setTimeout(function () {
                  waitForImagesAndGenerateThumbnail(page, pageElement);
                }, 500);
                return;
              }

              for (var i = 0; i < studentsForThisPage.length; i++) {
                var student = studentsForThisPage[i];

                // Validate student object
                if (!student || typeof student !== "object") {
                  console.warn(
                    "Invalid student object at index",
                    i,
                    "for page",
                    page,
                    ":",
                    student
                  );
                  continue;
                }

                var globalIndex = studentStartIndex + i;

                console.log("=== PROCESSING STUDENT ===");
                console.log("Global Index:", globalIndex);
                console.log("Student Name:", student.name || "Unknown");
                console.log("Student ID:", student.student_id || "Unknown");
                console.log("MongoDB ID:", student.id || "Unknown");
                console.log("Program:", student.program || "Unknown");
                console.log("Full Student Object:", student);

                var card = $("<div/>", {
                  class: "student-card",
                });

                var studentImg = $("<div/>", {
                  class: "student-image",
                });

                var defaultPhotoUrl =
                  'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="100%25" height="100%25" viewBox="0 0 135 155" preserveAspectRatio="xMidYMid slice"%3E%3Crect width="135" height="155" fill="%23f0f0f0"/%3E%3Ctext x="67.5" y="50" font-family="Arial" font-size="10" fill="%23666" text-anchor="middle" font-weight="600"%3ENo Photo%3C/text%3E%3Ctext x="67.5" y="70" font-family="Arial" font-size="8" fill="%23999" text-anchor="middle"%3EUpload via%3C/text%3E%3Ctext x="67.5" y="85" font-family="Arial" font-size="8" fill="%23999" text-anchor="middle"%3EBatch Upload%3C/text%3E%3C/svg%3E';

                var studentPhoto = $("<img/>", {
                  src: defaultPhotoUrl,
                  alt: student.name || "Unknown Student",
                  crossOrigin: "anonymous",
                  onerror:
                    'this.src=\'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="100%25" height="100%25" viewBox="0 0 135 155" preserveAspectRatio="xMidYMid slice"%3E%3Crect width="135" height="155" fill="%23f0f0f0"/%3E%3Ctext x="67.5" y="50" font-family="Arial" font-size="10" fill="%23666" text-anchor="middle" font-weight="600"%3ENo Photo%3C/text%3E%3Ctext x="67.5" y="70" font-family="Arial" font-size="8" fill="%23999" text-anchor="middle"%3EUpload via%3C/text%3E%3Ctext x="67.5" y="85" font-family="Arial" font-size="8" fill="%23999" text-anchor="middle"%3EBatch Upload%3C/text%3E%3C/svg%3E\';',
                });

                studentImg.append(studentPhoto);

                var studentIdForPhotos = student.student_id;
                var studentNameForPhotos = student.name || "Unknown Student";
                var studentStatus = (student.status || "pending").toLowerCase();

                console.log(
                  "Fetching TOGA photo for student:",
                  studentNameForPhotos,
                  "with student_id:",
                  studentIdForPhotos,
                  "status:",
                  studentStatus
                );

                // Fetch photos for both active and pending students
                if (studentIdForPhotos) {
                  (function (
                    currentStudent,
                    currentPhotoElement,
                    currentStudentId,
                    currentStudentName,
                    currentStatus
                  ) {
                    fetchStudentPhotos(
                      currentStudentId,
                      {
                        program: currentStudent.program,
                        department: currentStudent.section,
                      },
                      function (result) {
                        if (
                          result &&
                          result.photos &&
                          result.photos.length > 0
                        ) {
                          var primaryPhoto = result.photos[0];
                          if (primaryPhoto && primaryPhoto.url) {
                            console.log(
                              "Setting primary photo (",
                              primaryPhoto.type,
                              ") for",
                              currentStudentName,
                              ":",
                              primaryPhoto.url,
                              "Status:",
                              currentStatus
                            );
                            currentPhotoElement.attr("src", primaryPhoto.url);

                            // Apply blur effect if status is pending
                            if (currentStatus === "pending") {
                              currentPhotoElement.css({
                                filter: "blur(8px)",
                                "-webkit-filter": "blur(8px)",
                              });
                              var $parent = currentPhotoElement.parent();
                              $parent.css({
                                position: "relative",
                                overflow: "hidden",
                              });

                              console.log(
                                "Applied blur filter to pending student photo:",
                                currentStudentName
                              );
                            }
                          }
                        } else {
                          console.log(
                            "No valid photo data found for student",
                            currentStudentName,
                            result && result.message ? result.message : ""
                          );
                        }
                      }
                    );
                  })(
                    student,
                    studentPhoto,
                    studentIdForPhotos,
                    studentNameForPhotos,
                    studentStatus
                  );
                }

                var studentName = $("<h3/>", {
                  text: student.name || "Unknown Student",
                });

                // Store student data in data attributes for event delegation
                card.attr("data-student-id", student.student_id || "");
                card.attr(
                  "data-student-name",
                  student.name || "Unknown Student"
                );
                card.attr("data-student-year", student.year || "N/A");
                card.attr(
                  "data-student-motto",
                  student.motto || "No motto provided"
                );
                card.attr(
                  "data-student-milestones",
                  JSON.stringify(student.milestones || [])
                );
                card.attr(
                  "data-student-honors",
                  JSON.stringify(student.honors || [])
                );
                card.attr("data-student-program", student.program || "");
                card.attr("data-student-section", student.section || "");
                card.attr("data-student-status", studentStatus);

                card.append(studentImg).append(studentName);

                cardsContainer.append(card);
              }

              pageElement.append(cardsContainer);

              setTimeout(function () {
                waitForImagesAndGenerateThumbnail(page, pageElement);
              }, 500);
            }
          );
        });
      }
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

  // Add page number to bottom of page (excluding front and back covers)
  if (page > 1 && page < totalPages) {
    pageElement.find(".page-number").remove();
    var pageNumberDiv = $("<div/>", {
      class: "page-number",
      text: page,
    });
    pageElement.append(pageNumberDiv);
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
    page >= 4 &&
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

  // Add page number to bottom of page (excluding front and back covers)
  if (page > 1 && page < totalPages) {
    pageElement.find(".page-number").remove();
    var pageNumberDiv = $("<div/>", {
      class: "page-number",
      text: page,
    });
    pageElement.append(pageNumberDiv);
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
    page >= 4 &&
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
    // Check if we're in fullscreen mode (either actual fullscreen or via URL parameter)
    var isActualFullscreen =
      document.fullscreenElement ||
      document.webkitFullscreenElement ||
      document.mozFullScreenElement ||
      document.msFullscreenElement;

    // Check URL parameter for fullscreen mode (for iframe usage)
    var urlParams = new URLSearchParams(window.location.search);
    var isFullscreenParam = urlParams.get("fullscreen") === "true";

    var isFullscreen = isActualFullscreen || isFullscreenParam;

    // Use different base dimensions for fullscreen vs normal mode
    var baseWidth = isFullscreen ? 1200 : options.width;
    var baseHeight = isFullscreen ? 750 : options.height;

    console.log(
      "ResizeViewport - isFullscreen:",
      isFullscreen,
      "baseWidth:",
      baseWidth,
      "baseHeight:",
      baseHeight
    );

    var bound = calculateBound({
      width: baseWidth,
      height: baseHeight,
      boundWidth: Math.min(baseWidth, width),
      boundHeight: Math.min(baseHeight, height),
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

  // Use requestAnimationFrame for better performance
  requestAnimationFrame(function () {
    var pages = $(".magazine .page").toArray();
    var currentIndex = 0;

    function processBatch() {
      var batchSize = 3; // Process 3 pages at a time
      var endIndex = Math.min(currentIndex + batchSize, pages.length);

      for (var i = currentIndex; i < endIndex; i++) {
        var pageElement = $(pages[i]);
        var pageNumber = i + 1;

        var hasContent =
          pageElement.find("img").length > 0 ||
          pageElement.find(".top-management-page").length > 0 ||
          pageElement.find(".cards-container").length > 0;

        if (hasContent) {
          (function (pNum, pElem) {
            var delay = pNum <= 3 ? (pNum - 1) * 150 : (pNum - 1) * 400;
            setTimeout(function () {
              console.log("Generating thumbnail for page", pNum);
              generatePageThumbnail(pNum, pElem);
            }, delay);
          })(pageNumber, pageElement);
        } else {
          console.log("Skipping page", pageNumber, "- no content found");
        }
      }

      currentIndex = endIndex;

      // Continue processing if there are more pages
      if (currentIndex < pages.length) {
        requestAnimationFrame(processBatch);
      }
    }

    setTimeout(processBatch, 1500);
  });
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

  // Use requestAnimationFrame and batch processing for better performance
  requestAnimationFrame(function () {
    var pages = $(".magazine .page").toArray();
    var batchSize = 4; // Process 4 pages at a time

    function processBatch(startIndex) {
      var endIndex = Math.min(startIndex + batchSize, pages.length);

      for (var i = startIndex; i < endIndex; i++) {
        var pageElement = $(pages[i]);
        var pageNumber = i + 1;

        (function (pNum, pElem) {
          setTimeout(function () {
            waitForImagesAndGenerateThumbnail(pNum, pElem);
          }, (pNum - 1) * 200);
        })(pageNumber, pageElement);
      }

      if (endIndex < pages.length) {
        requestAnimationFrame(function () {
          processBatch(endIndex);
        });
      } else {
        // All pages processed, refresh thumbnails
        setTimeout(function () {
          if (typeof refreshThumbnails === "function") {
            refreshThumbnails();
          }
        }, 2000);
      }
    }

    processBatch(0);
  });
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
  var isModalActive = false;
  var mouseMoveHandler = null;
  var clickHandler = null;

  window.clearAllPeels = function () {
    if (peelTimer) {
      clearTimeout(peelTimer);
      peelTimer = null;
    }
    if (currentPeelCorner) {
      $magazine.turn("peel", false);
      currentPeelCorner = null;
    }
  };

  window.disableMagazineInteractions = disableMagazineInteractions;
  window.enableMagazineInteractions = enableMagazineInteractions;

  function disableMagazineInteractions() {
    if (mouseMoveHandler) {
      $magazine.off("mousemove", mouseMoveHandler);
    }
    if (clickHandler) {
      $magazine.off("click", clickHandler);
    }
    window.clearAllPeels();
    console.log("Magazine interactions disabled");
  }

  function enableMagazineInteractions() {
    if (mouseMoveHandler) {
      $magazine.on("mousemove", mouseMoveHandler);
    }
    if (clickHandler) {
      $magazine.on("click", clickHandler);
    }
    console.log("Magazine interactions enabled");
  }

  $(document).on("DOMSubtreeModified", function () {
    var modalActive = $(".student-modal").hasClass("active");
    if (modalActive !== isModalActive) {
      isModalActive = modalActive;
      if (isModalActive) {
        disableMagazineInteractions();
        console.log("Modal opened - disabling page interactions");
      } else {
        enableMagazineInteractions();
        console.log("Modal closed - enabling page interactions");
      }
    }
  });

  function checkModalState() {
    var modalActive = $(".student-modal").hasClass("active");
    if (modalActive !== isModalActive) {
      isModalActive = modalActive;
      if (isModalActive) {
        disableMagazineInteractions();
      } else {
        enableMagazineInteractions();
      }
    }
    return isModalActive;
  }

  mouseMoveHandler = function (e) {
    if (checkModalState()) {
      window.clearAllPeels();
      return;
    }

    if (!$magazine.turn("is") || isPageTurning) {
      window.clearAllPeels();
      return;
    }

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
      if ($(".student-modal").hasClass("active")) {
        if (currentPeelCorner) {
          $magazine.turn("peel", false);
          currentPeelCorner = null;
        }
        if (peelTimer) {
          clearTimeout(peelTimer);
          peelTimer = null;
        }
        return;
      }

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
  };

  clickHandler = function (e) {
    if (checkModalState()) {
      e.preventDefault();
      e.stopPropagation();
      return false;
    }

    if (!$magazine.turn("is") || isPageTurning) {
      e.preventDefault();
      e.stopPropagation();
      return false;
    }

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
      if (peelTimer) {
        clearTimeout(peelTimer);
        peelTimer = null;
      }
      $magazine.turn("peel", false);
      currentPeelCorner = null;

      isPageTurning = true;
      if (shouldGoNext) {
        $magazine.turn("next");
      } else if (shouldGoPrevious) {
        $magazine.turn("previous");
      }

      setTimeout(function () {
        isPageTurning = false;
      }, 1000);
    }
  };

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

  $magazine.on("mousemove", mouseMoveHandler);
  $magazine.on("click", clickHandler);

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

    $magazine.addClass("turning");
  });

  $magazine.on("turned", function () {
    isPageTurning = false;

    $magazine.removeClass("turning");
  });
}

// ========== STUDENT SEARCH NAVIGATION AND HIGHLIGHTING ==========

function navigateToSearchedStudent() {
  try {
    var urlParams = new URLSearchParams(window.location.search);
    var department = urlParams.get("department");
    var urlStudentId = urlParams.get("student_id");
    var urlStudentName = urlParams.get("student_name");

    var studentData = null;

    if (urlStudentId && urlStudentName) {
      studentData = {
        student_id: urlStudentId,
        name: decodeURIComponent(urlStudentName),
      };
      console.log("Student data from URL parameters:", studentData);
    } else {
      var searchSelectedStudent = sessionStorage.getItem(
        "searchSelectedStudent"
      );
      if (!searchSelectedStudent) {
        return;
      }
      studentData = JSON.parse(searchSelectedStudent);
      console.log("Student data from sessionStorage:", studentData);
      sessionStorage.removeItem("searchSelectedStudent");
    }

    if (!department) {
      console.log("No department in URL, cannot navigate to student");
      return;
    }

    console.log("=== NAVIGATING TO SEARCHED STUDENT ===");
    console.log("Student Data:", studentData);
    console.log("Department:", department);

    var template = 1;
    if (
      typeof window.coverData !== "undefined" &&
      window.coverData &&
      window.coverData.template
    ) {
      template = window.coverData.template;
      console.log("Got template from window.coverData:", template);
    } else if (
      typeof coverData !== "undefined" &&
      coverData &&
      coverData.template
    ) {
      template = coverData.template;
      console.log("Got template from coverData:", template);
    } else {
      var savedTemplate = localStorage.getItem("selectedBatchTemplateNumber");
      if (savedTemplate) {
        template = parseInt(savedTemplate);
        console.log("Got template from localStorage:", template);
      } else {
        console.log("Using default template: 1");
      }
    }

    console.log("=== TEMPLATE INFO ===");
    console.log("Final template for navigation:", template);

    var batchYear = localStorage.getItem("selectedBatchYear");
    console.log("Batch year for navigation:", batchYear);

    console.log("=== FORCING STUDENT DATA LOAD ===");
    console.log(
      "Calling loadAllStudentsForDepartment with:",
      department,
      template
    );

    loadAllStudentsForDepartment(department, template, function (allStudents) {
      console.log("=== ALL STUDENTS LOADED BY CALLBACK ===");
      console.log(
        "Total students loaded:",
        allStudents ? allStudents.length : 0
      );
      findAndNavigateToStudent(department, template, studentData, allStudents);
    });
  } catch (e) {
    console.error("Error navigating to searched student:", e);
  }
}

function findAndNavigateToStudent(
  department,
  template,
  studentData,
  allStudents
) {
  try {
    if (!allStudents || allStudents.length === 0) {
      console.error("=== NO STUDENTS DATA ===");
      console.error("Cannot navigate - no students loaded");
      return;
    }

    console.log("=== SEARCHING FOR STUDENT ===");
    console.log("Student to find:", studentData);
    console.log("Searching in", allStudents.length, "students");

    var studentIndex = -1;
    console.log("Searching through students...");

    for (var i = 0; i < allStudents.length; i++) {
      var student = allStudents[i];
      console.log(
        "Checking student",
        i,
        ":",
        student.name,
        "ID:",
        student.student_id
      );

      if (
        student.student_id === studentData.student_id ||
        student.id === studentData.id ||
        student.name === studentData.name
      ) {
        studentIndex = i;
        console.log("✓ MATCH FOUND at index", i);
        break;
      }
    }

    if (studentIndex >= 0) {
      console.log("=== STUDENT FOUND ===");
      console.log("Student Index:", studentIndex);
      console.log("Student:", allStudents[studentIndex]);

      var managementPages = 4;
      var topMgmtCacheKey =
        "template_" +
        template +
        "_" +
        localStorage.getItem("selectedBatchYear");
      if (window.topManagementCache[topMgmtCacheKey]) {
        var topMgmtData = window.topManagementCache[topMgmtCacheKey];
        if (
          topMgmtData.success &&
          topMgmtData.data &&
          Array.isArray(topMgmtData.data)
        ) {
          managementPages = topMgmtData.data.length;
          console.log(
            "Using actual top management pages count:",
            managementPages
          );
        }
      }

      var studentsPerPage = 4;
      var frontCoverPages = 1;
      var studentPageOffset = Math.floor(studentIndex / studentsPerPage);
      var yearbookPage =
        frontCoverPages + managementPages + studentPageOffset + 1;

      console.log("=== NAVIGATION CALCULATION ===");
      console.log("Front Cover Pages:", frontCoverPages);
      console.log("Management Pages:", managementPages);
      console.log("Students Per Page:", studentsPerPage);
      console.log("Student Index:", studentIndex);
      console.log("Calculated Yearbook Page:", yearbookPage);

      setTimeout(function () {
        var $magazine = $(".magazine");
        console.log("Magazine element found:", $magazine.length > 0);
        console.log("Magazine has turn:", typeof $magazine.turn);

        if ($magazine.length > 0 && $magazine.turn) {
          console.log("Turning to page:", yearbookPage);

          // Listen for the page turn completion
          $magazine.one("turned", function (event, page, view) {
            console.log("✓ Page turn completed to page:", page);

            // Wait a bit more for content to render
            setTimeout(function () {
              console.log("=== FINDING STUDENT IN VISIBLE CARDS ===");
              console.log("Student Index (global):", studentIndex);
              console.log("Student ID to find:", studentData.student_id);
              console.log("Student Name to find:", studentData.name);
              console.log("Current magazine page:", $magazine.turn("page"));

              console.log(
                "🧹 Clearing any existing yellow borders and timeouts..."
              );

              // Clear any existing border removal timeouts
              if (window.borderRemovalTimeouts) {
                console.log(
                  "Clearing " +
                    window.borderRemovalTimeouts.length +
                    " existing timeouts"
                );
                window.borderRemovalTimeouts.forEach(function (timeoutId) {
                  clearTimeout(timeoutId);
                  console.log("Cleared timeout:", timeoutId);
                });
              }
              window.borderRemovalTimeouts = [];

              // Remove all existing borders
              var bordersCleared = 0;
              $(".student-image").each(function () {
                if ($(this).attr("style")) {
                  bordersCleared++;
                }
                $(this).removeAttr("style");
              });
              console.log(
                "Cleared borders from " + bordersCleared + " student images"
              );

              var currentPage = $magazine.turn("page");
              console.log("Current magazine page:", currentPage);

              var $visiblePages = $magazine.find(".page").filter(function () {
                return $(this).is(":visible");
              });
              console.log("Visible pages found:", $visiblePages.length);

              function highlightStudent(retryCount) {
                retryCount = retryCount || 0;

                // Refresh visible pages on each retry
                $visiblePages = $magazine.find(".page").filter(function () {
                  return $(this).is(":visible");
                });

                var $studentCards = $visiblePages.find(".student-card");
                console.log(
                  "Student cards found on visible pages:",
                  $studentCards.length
                );

                // Log all student names in visible cards for debugging
                $studentCards.each(function (idx) {
                  var name = $(this).find("h3").text();
                  console.log("  Card " + idx + ":", name);
                });

                if ($studentCards.length === 0 && retryCount < 20) {
                  console.log(
                    "⏳ Student cards not loaded yet, retrying... (attempt " +
                      (retryCount + 1) +
                      "/20)"
                  );
                  setTimeout(function () {
                    highlightStudent(retryCount + 1);
                  }, 400);
                  return;
                } else if ($studentCards.length === 0) {
                  console.error(
                    "❌ Failed to find student cards after " +
                      retryCount +
                      " attempts"
                  );
                  notifyNavigationComplete();
                  return;
                }

                // Find the card by student ID instead of using card index
                var $targetCard = null;
                var targetCardIndex = -1;
                $studentCards.each(function (index) {
                  var cardStudentId = $(this).attr("data-student-id");
                  if (cardStudentId === studentData.student_id) {
                    $targetCard = $(this);
                    targetCardIndex = index;
                    return false; // break the loop
                  }
                });

                console.log(
                  "Target card index in visible cards:",
                  targetCardIndex
                );
                console.log(
                  "Target student card found:",
                  $targetCard && $targetCard.length > 0
                );

                if ($targetCard && $targetCard.length > 0) {
                  var $studentImageArea = $targetCard.find(".student-image");
                  console.log(
                    "Student image area in target card:",
                    $studentImageArea.length > 0
                  );

                  var $imgElement = $studentImageArea.find("img");
                  var hasImageContent =
                    $imgElement.length > 0 ||
                    $studentImageArea.css("background-image") !== "none";

                  console.log("Image element found:", $imgElement.length > 0);
                  console.log(
                    "Has background image:",
                    $studentImageArea.css("background-image") !== "none"
                  );
                  console.log("Has image content:", hasImageContent);

                  if ($studentImageArea.length && hasImageContent) {
                    console.log(
                      "✅ Adding yellow border to student image area"
                    );
                    console.log("Target element:", $studentImageArea[0]);
                    console.log(
                      "Current style before:",
                      $studentImageArea.attr("style")
                    );

                    $studentImageArea.attr(
                      "style",
                      "border: 2px solid #fcda15 !important; " +
                        "box-shadow: 0 0 10px rgba(252, 218, 21, 0.8) !important; " +
                        "border-radius: 8px !important; " +
                        "outline: 2px solid #ffd700 !important; " +
                        "outline-offset: 2px !important; " +
                        "transition: all 0.3s ease !important;"
                    );

                    console.log(
                      "Current style after:",
                      $studentImageArea.attr("style")
                    );
                    console.log("🎨 Applied BOLD yellow border");

                    // Use requestAnimationFrame for smoother animations
                    if (!window.borderRemovalTimeouts) {
                      window.borderRemovalTimeouts = [];
                    }

                    requestAnimationFrame(function () {
                      $studentImageArea.attr(
                        "style",
                        "border: 2px solid #fcda15 !important; " +
                          "box-shadow: 0 0 10px rgba(252, 218, 21, 0.8) !important; " +
                          "border-radius: 8px !important; " +
                          "outline: 2px solid #ffd700 !important; " +
                          "outline-offset: 2px !important; " +
                          "transition: all 3s ease !important;"
                      );

                      var timeout2 = setTimeout(function () {
                        requestAnimationFrame(function () {
                          $studentImageArea.removeAttr("style");
                        });
                      }, 3000);

                      window.borderRemovalTimeouts.push(timeout2);
                    });

                    notifyNavigationComplete();
                  } else if ($studentImageArea.length && retryCount < 15) {
                    console.log(
                      "⏳ Student image area exists but no image content yet, retrying... (attempt " +
                        (retryCount + 1) +
                        "/15)"
                    );
                    setTimeout(function () {
                      highlightStudent(retryCount + 1);
                    }, 500);
                    return;
                  } else {
                    console.warn(
                      "⚠️ Student image area not found or no image content in target card"
                    );
                    notifyNavigationComplete();
                  }
                } else {
                  console.warn(
                    "⚠️ Target student card not found for student ID: " +
                      studentData.student_id
                  );
                  notifyNavigationComplete();
                }
              }

              function notifyNavigationComplete() {
                console.log(
                  "📍 Sending navigation complete signal to parent window"
                );
                if (window.parent && window.parent !== window) {
                  window.parent.postMessage(
                    {
                      type: "yearbook-navigation-complete",
                      timestamp: new Date().toISOString(),
                    },
                    "*"
                  );
                }
              }

              highlightStudent();

              var imageLoadListener = function () {
                console.log(
                  "🖼️ Image loaded, checking if we need to highlight..."
                );
                requestAnimationFrame(function () {
                  highlightStudent();
                });
              };

              $visiblePages.find("img").on("load", imageLoadListener);

              setTimeout(function () {
                $visiblePages.find("img").off("load", imageLoadListener);
              }, 10000);
            }, 2000);
          });

          // Now actually turn the page
          $magazine.turn("page", yearbookPage);
          console.log("✓ Page turn initiated to:", yearbookPage);
        } else {
          console.error("Magazine not initialized or turn not available");
        }
      }, 2000);
    } else {
      console.error("=== STUDENT NOT FOUND ===");
      console.error("Searched for:", studentData);
      console.error("Total students available:", allStudents.length);
    }
  } catch (e) {
    console.error("Error navigating to searched student:", e);
  }
}

window.navigateToSearchedStudent = navigateToSearchedStudent;

var navigationAttempts = 0;
var maxNavigationAttempts = 10;
var lastSearchedStudentId = null;
var hasInitialNavigationRun = false;

function tryNavigateToStudent() {
  navigationAttempts++;
  console.log("=== NAVIGATION ATTEMPT", navigationAttempts, "===");

  var $magazine = $(".magazine");
  var magazineExists = $magazine.length > 0;
  var turnFunctionExists = typeof $magazine.turn === "function";

  console.log("Magazine element exists:", magazineExists);
  console.log("Turn function exists:", turnFunctionExists);

  if (magazineExists && turnFunctionExists) {
    console.log("✓ Magazine is ready, starting navigation");
    navigateToSearchedStudent();
    hasInitialNavigationRun = true;
  } else if (navigationAttempts < maxNavigationAttempts) {
    console.log(
      "✗ Magazine not ready yet, retrying in 1 second... (attempt",
      navigationAttempts,
      "of",
      maxNavigationAttempts + ")"
    );
    setTimeout(tryNavigateToStudent, 1000);
  } else {
    console.error(
      "✗ Magazine failed to initialize after",
      maxNavigationAttempts,
      "attempts"
    );
    hasInitialNavigationRun = true;
  }
}

function checkIfNavigationNeeded() {
  var urlParams = new URLSearchParams(window.location.search);
  var urlStudentId = urlParams.get("student_id");
  var urlStudentName = urlParams.get("student_name");
  var searchSelectedStudent = sessionStorage.getItem("searchSelectedStudent");

  if (!urlStudentId && !urlStudentName && !searchSelectedStudent) {
    console.log("📍 No student navigation required");
    hasInitialNavigationRun = true;
    return false;
  }
  if (urlStudentId && !lastSearchedStudentId) {
    lastSearchedStudentId = urlStudentId;
    console.log("📝 Initial student ID stored:", lastSearchedStudentId);
  }
  return true;
}

console.log("Navigation will start in 4 seconds...");
setTimeout(function () {
  if (checkIfNavigationNeeded()) {
    tryNavigateToStudent();
  }
}, 4000);

function checkForNewStudentSearch() {
  var urlParams = new URLSearchParams(window.location.search);
  var currentStudentId = urlParams.get("student_id");
  var currentStudentName = urlParams.get("student_name");

  if (!currentStudentId) {
    return;
  }

  if (currentStudentId !== lastSearchedStudentId) {
    console.log(
      "🔄 New student search detected:",
      currentStudentId,
      currentStudentName
    );
    console.log("Previous student:", lastSearchedStudentId);
    console.log("Has initial navigation run:", hasInitialNavigationRun);

    lastSearchedStudentId = currentStudentId;

    if (hasInitialNavigationRun) {
      console.log(
        "🎯 This is a subsequent search - triggering navigation immediately!"
      );
      navigationAttempts = 0;

      setTimeout(function () {
        console.log("📍 Starting navigation for student:", currentStudentId);
        navigateToSearchedStudent();
      }, 1000);
    }
  }
}

setInterval(checkForNewStudentSearch, 300);

window.triggerStudentNavigation = function (
  studentId,
  studentName,
  department
) {
  console.log("🎯 Manual navigation triggered:", {
    studentId: studentId,
    studentName: studentName,
    department: department,
  });
  navigationAttempts = 0;
  lastSearchedStudentId = studentId;
  setTimeout(function () {
    navigateToSearchedStudent();
  }, 500);
};
