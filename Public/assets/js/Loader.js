(function () {
  const iframe = document.getElementById('preload-frame');

  function allImagesLoaded(doc) {
    const imgs = Array.from(doc ? doc.images : []);
    if (imgs.length === 0) return true;
    return imgs.every((img) => img.complete && img.naturalWidth > 0);
  }

  function tryRedirect() {
    try {
      const doc = iframe.contentDocument || iframe.contentWindow.document;
      if (doc && allImagesLoaded(doc)) {
        window.location.replace('/LandingPage/LandingPage.html');
      }
    } catch (_) {}
  }

  iframe.addEventListener('load', () => {
    tryRedirect();
    const interval = setInterval(tryRedirect, 300);
    setTimeout(() => {
      clearInterval(interval);
      window.location.replace('/LandingPage/LandingPage.html');
    }, 10000);
  });
})();


