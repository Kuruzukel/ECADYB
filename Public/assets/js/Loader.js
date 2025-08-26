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
        // Inject preloaded HTML into current page to keep URL at root
        const html = doc.documentElement.outerHTML;
        document.open();
        document.write(html);
        document.close();
      }
    } catch (_) {}
  }

  iframe.addEventListener('load', () => {
    tryRedirect();
    const interval = setInterval(tryRedirect, 300);
    setTimeout(() => {
      clearInterval(interval);
      // Force transition even if not all images finished loading
      try {
        const doc = iframe.contentDocument || iframe.contentWindow.document;
        if (doc) {
          const html = doc.documentElement.outerHTML;
          document.open();
          document.write(html);
          document.close();
        }
      } catch (_) {}
    }, 10000);
  });
})();


