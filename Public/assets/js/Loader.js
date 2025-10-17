(function () {
  const iframe = document.getElementById("preload-frame");
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

    body.classList.add("page-transition-out");

    setTimeout(() => {
      window.location.href = "/LandingPage";
    }, 1000);
  }

  function tryRedirect() {
    try {
      const doc = iframe.contentDocument || iframe.contentWindow.document;
      if (doc && allImagesLoaded(doc)) {
        startTransition();
      }
    } catch (_) {}
  }

  iframe.addEventListener("load", () => {
    tryRedirect();
    const interval = setInterval(tryRedirect, 300);

    setTimeout(() => {
      clearInterval(interval);
      if (!redirectInProgress) {
        startTransition();
      }
    }, 3000); // Changed from 10000ms (10 seconds) to 3000ms (3 seconds)
  });
})();
