(function () {
  const iframe = document.getElementById("preload-frame");
  const body = document.body;
  let redirectInProgress = false;

  const isLocalhost =
    window.location.hostname === "localhost" ||
    window.location.hostname === "127.0.0.1";
  const BASE_URL = isLocalhost ? "/ECADYB/" : "/";

  console.log("Loader.js - isLocalhost:", isLocalhost);
  console.log("Loader.js - BASE_URL:", BASE_URL);
  console.log("Loader.js - Will redirect to:", BASE_URL + "LandingPage");

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
      window.location.href = BASE_URL + "LandingPage";
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
    }, 3000);
  });
})();
