// service-worker.js

const CACHE_NAME = "grad-gallery-cache-v3";

// Files you want to cache for offline use
const urlsToCache = [
  "/", // index.html
  "/LandingPage/LandingPage.css",
  "/LandingPage/LandingPage.js",
  "/Login",
  "/Public/Components/Loader.html",
  "/Public/assets/css/Loader.css",

  // Logos
  "https://ECADYB.b-cdn.net/img/PREVIEWLOGO.png",
  "https://ECADYB.b-cdn.net/img/ECALOGO.png",
  "https://ECADYB.b-cdn.net/img/GRALLERYLOGO4.0.png",
  "https://ECADYB.b-cdn.net/img/ABOUTIMG.png",

  // Yearbook Covers
  "https://ECADYB.b-cdn.net/img/YB COVER/MaritimeEducation.png",
  "https://ECADYB.b-cdn.net/img/YB COVER/TourismManagement.png",
  "https://ECADYB.b-cdn.net/img/YB COVER/CriminalJusticeEducation.png",
  "https://ECADYB.b-cdn.net/img/YB COVER/InformationSystem.png",
  "https://ECADYB.b-cdn.net/img/YB COVER/BusinessAdministration.png",
  "https://ECADYB.b-cdn.net/img/YB COVER/Education.png",
  "https://ECADYB.b-cdn.net/img/YB COVER/Nursing.png",

  // Carousel images
  "https://ECADYB.b-cdn.net/img/CAROUSEL/sample1.jpg",
  "https://ECADYB.b-cdn.net/img/CAROUSEL/sample2.jpg",
  "https://ECADYB.b-cdn.net/img/CAROUSEL/sample3.jpg",
  "https://ECADYB.b-cdn.net/img/CAROUSEL/sample4.jpg",
  "https://ECADYB.b-cdn.net/img/CAROUSEL/sample5.jpg",
  "https://ECADYB.b-cdn.net/img/CAROUSEL/sample6.jpg",
  "https://ECADYB.b-cdn.net/img/CAROUSEL/sample7.jpg",
  "https://ECADYB.b-cdn.net/img/CAROUSEL/sample8.jpg",
  "https://ECADYB.b-cdn.net/img/CAROUSEL/sample9.jpg",
  "https://ECADYB.b-cdn.net/img/CAROUSEL/sample10.jpg",
  "https://ECADYB.b-cdn.net/img/CAROUSEL/sample11.jpg",
  "https://ECADYB.b-cdn.net/img/CAROUSEL/sample12.jpg",
  "https://ECADYB.b-cdn.net/img/CAROUSEL/sample13.jpg",
  "https://ECADYB.b-cdn.net/img/CAROUSEL/sample14.jpg",
  "https://ECADYB.b-cdn.net/img/CAROUSEL/sample15.jpg",
];

// Install: cache files
self.addEventListener("install", (event) => {
  console.log("[ServiceWorker] Installing…");
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return Promise.all(
        urlsToCache.map((url) =>
          cache.add(url).catch((err) => {
            console.warn("[ServiceWorker] Failed to cache:", url, err);
          })
        )
      );
    })
  );
  self.skipWaiting();
});

// Fetch: use cache first, then network
self.addEventListener("fetch", (event) => {
  const { request } = event;

  // Network-first strategy for CSS to always get the latest styles
  if (
    request.destination === "style" ||
    request.url.endsWith(".css")
  ) {
    event.respondWith(
      fetch(new Request(request, { cache: "no-store" }))
        .then((networkResponse) => {
          if (networkResponse && networkResponse.status === 200) {
            const responseClone = networkResponse.clone();
            caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
          }
          return networkResponse;
        })
        .catch(() => caches.match(request))
    );
    return;
  }

  // Default: cache-first with network update
  event.respondWith(
    caches.match(request).then((cachedResponse) => {
      if (cachedResponse) {
        return cachedResponse;
      }
      return fetch(request)
        .then((response) => {
          if (!response || response.status !== 200) {
            return response;
          }
          const responseClone = response.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
          return response;
        })
        .catch((error) => {
          console.warn("[ServiceWorker] Fetch failed for:", request.url, error);
          throw error;
        });
    })
  );
});

// Activate: clear old caches
self.addEventListener("activate", (event) => {
  console.log("[ServiceWorker] Activating new service worker…");
  const cacheWhitelist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then((cacheNames) =>
      Promise.all(
        cacheNames.map((cacheName) => {
          if (!cacheWhitelist.includes(cacheName)) {
            console.log("[ServiceWorker] Deleting old cache:", cacheName);
            return caches.delete(cacheName);
          }
        })
      )
    )
  );
  self.clients.claim();
});
