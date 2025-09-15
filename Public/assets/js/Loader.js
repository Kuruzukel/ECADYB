(function () {
  const iframe = document.getElementById('preload-frame');
  const body = document.body;
  let redirectInProgress = false;

  function allImagesLoaded(doc) {
    const imgs = Array.from(doc ? doc.images : []);
    if (imgs.length === 0) return true;
    return imgs.every((img) => img.complete && img.naturalWidth > 0);
  }

  function startTransition() {
    if (redirectInProgress) return;
    redirectInProgress = true;
    
    // Add the transition class to trigger the animation
    body.classList.add('page-transition-out');
    
    // Wait for the animation to complete before redirecting
    setTimeout(() => {
      window.location.href = '/LandingPage/LandingPage.html';
    }, 1000); // Match this with the animation duration (1s)
  }

  function tryRedirect() {
    try {
      const doc = iframe.contentDocument || iframe.contentWindow.document;
      if (doc && allImagesLoaded(doc)) {
        startTransition();
      }
    } catch (_) {}
  }

  iframe.addEventListener('load', () => {
    tryRedirect();
    const interval = setInterval(tryRedirect, 300);
    
    // Fallback in case the page takes too long to load
    setTimeout(() => {
      clearInterval(interval);
      if (!redirectInProgress) {
        startTransition();
      }
    }, 10000);
  });
})();


