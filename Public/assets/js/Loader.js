document.addEventListener('DOMContentLoaded', function() {
  const body = document.body;
  const loader = document.getElementById('loader');
  const loaderContent = document.createElement('div');
  loaderContent.className = 'loader-content';
  
  // Add loading spinner
  const spinner = document.createElement('div');
  spinner.className = 'loader-spinner';
  
  // Add loading text
  const loadingText = document.createElement('p');
  loadingText.textContent = 'Loading...';
  loadingText.style.color = '#333';
  loadingText.style.fontFamily = 'Arial, sans-serif';
  loadingText.style.marginTop = '20px';
  
  // Assemble the loader
  loaderContent.appendChild(spinner);
  loaderContent.appendChild(loadingText);
  loader.appendChild(loaderContent);
  
  // Make body visible
  body.classList.add('loaded');
  
  // Check if all images are loaded
  function allImagesLoaded() {
    const images = document.images;
    const totalImages = images.length;
    let loadedImages = 0;
    
    if (totalImages === 0) return Promise.resolve();
    
    return new Promise((resolve) => {
      const imageLoaded = () => {
        loadedImages++;
        if (loadedImages === totalImages) {
          resolve();
        }
      };
      
      for (let i = 0; i < totalImages; i++) {
        if (images[i].complete) {
          loadedImages++;
        } else {
          images[i].addEventListener('load', imageLoaded);
          images[i].addEventListener('error', imageLoaded); // Also resolve on error
        }
      }
      
      // In case some images don't fire load events
      if (loadedImages === totalImages) {
        resolve();
      }
    });
  }
  
  // Start the transition
  async function startTransition() {
    try {
      // Wait for all images to load with a timeout
      await Promise.race([
        allImagesLoaded(),
        new Promise(resolve => setTimeout(resolve, 3000)) // Max 3 seconds wait
      ]);
      
      // Add transition class to body
      body.classList.add('page-transition-out');
      loader.classList.add('fade-out');
      
      // Wait for the animation to complete before redirecting
      setTimeout(() => {
        window.location.href = '/LandingPage/LandingPage.html';
      }, 1000); // Match this with the CSS animation duration
      
    } catch (error) {
      console.error('Transition error:', error);
      // Fallback in case of errors
      window.location.href = '/LandingPage/LandingPage.html';
    }
  }
  
  // Start the transition after a short delay to ensure everything is ready
  setTimeout(startTransition, 500);
});


