function addPage(page, book) {
  var id,
    pages = book.turn("pages");

  // Create a new element for this page
  var element = $("<div />", {});

  // Add the page to the flipbook
  if (book.turn("addPage", element, page)) {
    // Add the initial HTML
    // Only show a loader indicator; gradient removed
    element.html('<div class="loader"></div>');

    // Load the page
    loadPage(page, element);
  }
}

function loadPage(page, pageElement) {
  // Create an image element
  var img = $("<img />");

  img.on("mousedown", function (e) {
    e.preventDefault();
  });

  img.on("load", function () {
    // Set the size
    $(this).css({ width: "100%", height: "100%" });

    // Add the image to the page after loaded
    $(this).appendTo(pageElement);

    // Remove the loader indicator
    pageElement.find(".loader").remove();
  });

  // Check if we're loading the first or last page and use cover data if available
  var totalPages = $(".magazine").turn("pages");
  
  // Wait for coverData to be available if it's not yet loaded, but don't wait indefinitely
  var maxWaitTime = 5000; // 5 seconds
  var waitStartTime = Date.now();
  
  if ((typeof coverData === 'undefined' || coverData === null) && (Date.now() - waitStartTime < maxWaitTime)) {
    setTimeout(function() {
      loadPage(page, pageElement);
    }, 100);
    return;
  }
  
  // Debug: Log the coverData and page information
  console.log('Loading page:', page, 'Total pages:', totalPages, 'Cover data:', coverData);
  
  if (page === 1 && typeof coverData !== 'undefined' && coverData !== null && coverData.front_url) {
    // First page - use front cover
    console.log('Using front_url for page 1:', coverData.front_url);
    img.attr("src", coverData.front_url);
  } else if (page === totalPages && typeof coverData !== 'undefined' && coverData !== null && coverData.back_url) {
    // Last page - use back cover
    console.log('Using back_url for page', page, ':', coverData.back_url);
    img.attr("src", coverData.back_url);
  } else if (page >= 2 && page <= 6 && typeof coverData !== 'undefined' && coverData !== null) {
    // Pages 2-6 - Top Management pages
    console.log('Loading top management page:', page);
    
    // Use background image if available
    if (coverData.background_url) {
      console.log('Using background_url for management page', page, ':', coverData.background_url);
      img.attr("src", coverData.background_url);
    } else {
      // Fallback to white background if no background image is available
      img.attr("src", "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23ffffff'/%3E%3C/svg%3E");
    }
    
    // Create top management page container
    var managementPage = $('<div/>', {
      class: 'top-management-page'
    });

    // Create photo container
    var photoContainer = $('<div/>', {
      class: 'management-photo'
    });

    // Create info container
    var infoContainer = $('<div/>', {
      class: 'management-info'
    });

    // Sample data - replace with actual data from database
    var managementData = {
      2: {
        name: 'Dr. John Smith',
        position: 'University President',
        photo: 'https://ECADYB.b-cdn.net/img/Profile.png',
        message: 'As we celebrate another milestone in our academic journey, I am filled with immense pride in our graduating class. Your dedication, resilience, and achievements have been truly remarkable. Remember that education is not just about acquiring knowledge; it\'s about developing character, critical thinking, and the ability to make a positive impact in the world.'
      },
      3: {
        name: 'Dr. Sarah Johnson',
        position: 'Vice President for Academic Affairs',
        photo: 'https://ECADYB.b-cdn.net/img/Profile.png',
        message: 'To our dear graduates, you have demonstrated exceptional academic excellence and personal growth throughout your journey. As you step into the next chapter of your lives, carry with you the values, knowledge, and skills you\'ve gained. Your success is our success, and we are confident that you will make significant contributions to your chosen fields.'
      },
      4: {
        name: 'Dr. Michael Chen',
        position: 'Dean of Student Affairs',
        photo: 'https://ECADYB.b-cdn.net/img/Profile.png',
        message: 'Watching you grow and develop into capable professionals has been one of the most rewarding experiences. Your enthusiasm, creativity, and determination have inspired us all. As you embark on your professional journeys, remember that learning is a lifelong process, and your alma mater will always be here to support you.'
      },
      5: {
        name: 'Prof. Emily Rodriguez',
        position: 'Director of Research and Development',
        photo: 'https://ECADYB.b-cdn.net/img/Profile.png',
        message: 'Your academic journey has been marked by innovation, curiosity, and a commitment to excellence. As you graduate, remember that research and continuous learning are key to professional growth. May you continue to push boundaries and make meaningful contributions to your fields of expertise.'
      },
      6: {
        name: 'Dr. Robert Wilson',
        position: 'Head of International Relations',
        photo: 'https://ECADYB.b-cdn.net/img/Profile.png',
        message: 'In today\'s interconnected world, your global perspective and cultural awareness will be invaluable assets. You have been part of a diverse academic community, and this experience will serve you well in your future endeavors. Go forth with confidence, knowing that you are prepared to make a difference on both local and global stages.'
      }
    };

    var currentManager = managementData[page];

    // Add photo
    var photo = $('<img/>', {
      src: currentManager.photo,
      alt: currentManager.name
    });
    photoContainer.append(photo);

    // Add name
    var name = $('<h2/>', {
      class: 'management-name',
      text: currentManager.name
    });

    // Add position
    var position = $('<h3/>', {
      class: 'management-position',
      text: currentManager.position
    });

    // Add decorative elements
    var topDecoration = $('<div/>', {
      class: 'yearbook-decoration top'
    });
    
    var bottomDecoration = $('<div/>', {
      class: 'yearbook-decoration bottom'
    });
    
    // Add message with quotation marks
    var messageContainer = $('<div/>', {
      class: 'message-container'
    });
    
    var quoteOpen = $('<span/>', {
      class: 'quote-mark open',
      text: '❝'
    });
    
    var quoteClose = $('<span/>', {
      class: 'quote-mark close',
      text: '❞'
    });
    
    var message = $('<div/>', {
      class: 'management-message',
      text: currentManager.message
    });
    
    // Assemble the message with quotes
    messageContainer.append(quoteOpen)
                   .append(message)
                   .append(quoteClose);
    
    // Assemble the page
    infoContainer.append(name)
                 .append(position)
                 .append(messageContainer);
                 
    // Add decorations
    pageElement.append(topDecoration)
               .append(bottomDecoration);

    managementPage.append(photoContainer)
                 .append(infoContainer);

    // Add the management page to the page element
    pageElement.append(managementPage);
  } else if (page >= 7 && page <= 11 && typeof coverData !== 'undefined' && coverData !== null && coverData.background_url) {
    // Pages 7-11 - Student pages
    console.log('Using background_url for page', page, ':', coverData.background_url);
    img.attr("src", coverData.background_url);
    
    // Create student cards container after image loads
    img.on('load', function() {
      var cardsContainer = $('<div/>', {
        class: 'cards-container'
      });

      // Add 6 student cards (3 per column)
      for (var i = 0; i < 6; i++) {
        var card = $('<div/>', {
          class: 'student-card'
        });

        // Add student image placeholder
        var studentImg = $('<div/>', {
          class: 'student-image'
        });

        // Add student name
        var studentName = $('<h3/>', {
          text: 'Student Name ' + (i + 1)
        });

        // Add honors text
        var honorsText = $('<p/>', {
          text: 'Honors and Achievements'
        });

        // Add click handler for the card
        card.on('click', function() {
          var modal = $('.student-modal');
          var closeBtn = $('.close-modal');
          var studentName = $(this).find('h3').text();
          
          // Sample images for demonstration (replace with actual student photos)
          var studentPhotos = [
            'https://ECADYB.b-cdn.net/img/Profile.png',
            'https://ECADYB.b-cdn.net/img/Profile.png',
            'https://ECADYB.b-cdn.net/img/Profile.png'
          ];
          
          // Initialize modal content
          modal.find('.student-name').text(studentName);
          
          // Initialize images
          var $largeImage = modal.find('.student-image-large img');
          var $thumbnails = modal.find('.student-image-thumbnails .thumbnail');
          
          // Set initial large image
          $largeImage.attr('src', studentPhotos[0]);
          
          // Set thumbnail images and initial active state
          $thumbnails.each(function(index) {
            $(this)
              .find('img')
              .attr('src', studentPhotos[index]);
            
            if (index === 0) {
              $(this).addClass('active');
            } else {
              $(this).removeClass('active');
            }
          });
          
          // Show modal
          modal.addClass('active');
          
          // Handle thumbnail clicks
          $thumbnails.off('click').on('click', function(e) {
            e.stopPropagation(); // Prevent modal from closing
            e.preventDefault();
            
            var $this = $(this);
            var index = $this.index();
            
            // Update active state
            $thumbnails.removeClass('active');
            $this.addClass('active');
            
            // Update large image with fade effect
            $largeImage.fadeOut(200, function() {
              $(this).attr('src', studentPhotos[index]).fadeIn(200);
            });
            
            console.log('Switching to photo:', index + 1); // Debug log
          });
          
          // Close modal when clicking close button or outside
          closeBtn.on('click', function() {
            modal.removeClass('active');
          });
          
          $(window).on('click', function(event) {
            if ($(event.target).hasClass('student-modal')) {
              modal.removeClass('active');
            }
          });
        });

        // Assemble the card
        card.append(studentImg)
            .append(studentName)
            .append(honorsText);

        cardsContainer.append(card);
      }

      // Add the cards container to the page
      pageElement.append(cardsContainer);
    });
  } else if (typeof coverData !== 'undefined' && coverData !== null && coverData.background_url) {
    // Other middle pages - use background image as fallback
    console.log('Using background_url as fallback for page', page, ':', coverData.background_url);
    img.attr("src", coverData.background_url);
  } else {
    // Show a placeholder when no image is available
    console.log('No image available for page:', page, 'Showing placeholder');
    // Create a placeholder with a colored background
    img.attr("src", "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23f0f0f0'/%3E%3Ctext x='50' y='55' font-family='Arial' font-size='12' fill='%23999' text-anchor='middle'%3EPage " + page + "%3C/text%3E%3C/svg%3E");
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
  
  if ((typeof coverData === 'undefined' || coverData === null) && (Date.now() - waitStartTime < maxWaitTime)) {
    setTimeout(function() {
      loadLargePage(page, pageElement);
    }, 100);
    return;
  }
  
  if (page === 1 && typeof coverData !== 'undefined' && coverData !== null && coverData.front_url) {
    // First page - use front cover
    img.attr("src", coverData.front_url);
  } else if (page === totalPages && typeof coverData !== 'undefined' && coverData !== null && coverData.back_url) {
    // Last page - use back cover
    img.attr("src", coverData.back_url);
  } else if (page >= 2 && page <= 11 && typeof coverData !== 'undefined' && coverData !== null && coverData.background_url) {
    // Pages 2-11 - use background image from database
    img.attr("src", coverData.background_url);
  } else if (typeof coverData !== 'undefined' && coverData !== null && coverData.background_url) {
    // Other middle pages - use background image as fallback
    img.attr("src", coverData.background_url);
  } else {
    // Show a placeholder when no image is available
    console.log('No image available for page:', page, 'Showing placeholder');
    // Create a placeholder with a colored background
    img.attr("src", "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23f0f0f0'/%3E%3Ctext x='50' y='55' font-family='Arial' font-size='12' fill='%23999' text-anchor='middle'%3ELarge Page " + page + "%3C/text%3E%3C/svg%3E");
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
  
  if ((typeof coverData === 'undefined' || coverData === null) && (Date.now() - waitStartTime < maxWaitTime)) {
    setTimeout(function() {
      loadSmallPage(page, pageElement);
    }, 100);
    return;
  }
  
  if (page === 1 && typeof coverData !== 'undefined' && coverData !== null && coverData.front_url) {
    // First page - use front cover
    img.attr("src", coverData.front_url);
  } else if (page === totalPages && typeof coverData !== 'undefined' && coverData !== null && coverData.back_url) {
    // Last page - use back cover
    img.attr("src", coverData.back_url);
  } else if (page >= 2 && page <= 11 && typeof coverData !== 'undefined' && coverData !== null && coverData.background_url) {
    // Pages 2-11 - use background image from database
    img.attr("src", coverData.background_url);
  } else {
    // Show a placeholder when no image is available
    console.log('No image available for page:', page, 'Showing placeholder');
    // Create a placeholder with a colored background
    img.attr("src", "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23f0f0f0'/%3E%3Ctext x='50' y='55' font-family='Arial' font-size='12' fill='%23999' text-anchor='middle'%3ESmall Page " + page + "%3C/text%3E%3C/svg%3E");
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