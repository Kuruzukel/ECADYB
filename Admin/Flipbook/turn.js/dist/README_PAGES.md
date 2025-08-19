# How to Add Pages to Turn.js Flipbook

## Method 1: Using Images (Recommended)

### Step 1: Prepare Your Images
Place your page images in the `YB COVER` folder. Images should be:
- Same dimensions (e.g., 500x600 pixels)
- Named sequentially (e.g., 20.png, 21.png, 22.png, etc.)
- In a supported format (PNG, JPG, JPEG)

### Step 2: Update the Configuration
In `script.js`, update the `FlipbookSettings`:

```javascript
FlipbookSettings = {
  options: {
    pageWidth: 500,
    pageHeight: 600,
    pages: 8, // Number of pages you have
  },
  shareMessage: "Your flipbook message here.",
  pageFolder: "/YB COVER",
  loadRegions: true,
  // Add your page sources
  pageSources: [
    "/YB COVER/20.png",
    "/YB COVER/21.png", 
    "/YB COVER/22.png",
    "/YB COVER/23.png",
    "/YB COVER/24.png",
    "/YB COVER/25.png",
    "/YB COVER/26.png",
    "/YB COVER/27.png"
  ]
};
```

### Step 3: Add More Images
To add more pages:
1. Add new images to the `YB COVER` folder (e.g., 28.png, 29.png)
2. Update the `pages` count in options
3. Add the new image paths to `pageSources`

## Method 2: Using HTML Content

### Step 1: Create HTML Pages
Create HTML files for each page in a folder (e.g., `pages/`):

```html
<!-- pages/page1.html -->
<div class="page-content">
  <h1>Page 1</h1>
  <p>This is the content of page 1.</p>
</div>
```

### Step 2: Load HTML Pages
Modify the `_getPageElement` function in `script.js`:

```javascript
_getPageElement: function (pageNumber) {
  var $el = $("<div />");
  
  // Load HTML content
  $.get('/pages/page' + pageNumber + '.html', function(data) {
    $el.html(data);
  });
  
  return $el;
}
```

## Method 3: Dynamic Page Addition

### Add Pages Programmatically
You can add pages dynamically using JavaScript:

```javascript
// Add a single page
$("#flipbook").turn("addPage", $("<div>New Page Content</div>"), pageNumber);

// Add multiple pages
for (var i = 1; i <= 5; i++) {
  $("#flipbook").turn("addPage", $("<div>Page " + i + "</div>"), i);
}
```

## Method 4: Using PDF Pages

### Step 1: Convert PDF to Images
Convert your PDF pages to images using tools like:
- Adobe Acrobat
- Online PDF to Image converters
- Command line tools like ImageMagick

### Step 2: Use the Image Method
Follow Method 1 above with your converted images.

## Important Notes

### Page Dimensions
- All pages should have the same dimensions
- Set `pageWidth` and `pageHeight` in options to match your images
- For responsive design, use percentages or viewport units

### Performance Tips
- Optimize images for web (compress, use appropriate formats)
- Use lazy loading for large flipbooks
- Consider using WebP format for better compression

### File Paths
- Use absolute paths from your web root
- Ensure paths are correct for your server setup
- Test paths in browser developer tools

### Adding Pages at Runtime
```javascript
// Add a page after the flipbook is loaded
$("#flipbook").turn("addPage", newPageElement, pageNumber);

// Remove a page
$("#flipbook").turn("removePage", pageNumber);

// Get total pages
var totalPages = $("#flipbook").turn("pages");
```

## Example: Complete Setup

Here's a complete example for your current setup:

```javascript
FlipbookSettings = {
  options: {
    pageWidth: 500,
    pageHeight: 600,
    pages: 8,
    responsive: true,
    animatedAutoCenter: true,
    smartFlip: true,
    autoScaleContent: true,
    swipe: true
  },
  shareMessage: "ECADYB Yearbook 2024",
  pageFolder: "/YB COVER",
  loadRegions: true,
  pageSources: [
    "/YB COVER/20.png",
    "/YB COVER/21.png", 
    "/YB COVER/22.png",
    "/YB COVER/23.png",
    "/YB COVER/24.png",
    "/YB COVER/25.png",
    "/YB COVER/26.png",
    "/YB COVER/27.png"
  ]
};
```

## Troubleshooting

### Pages Not Loading
1. Check file paths are correct
2. Verify images exist in the specified folder
3. Check browser console for errors
4. Ensure images are accessible via web server

### Performance Issues
1. Compress images
2. Use appropriate image formats
3. Consider lazy loading for large flipbooks
4. Optimize page dimensions

### Display Issues
1. Ensure all pages have same dimensions
2. Check CSS conflicts
3. Verify Turn.js is properly loaded
4. Test in different browsers 