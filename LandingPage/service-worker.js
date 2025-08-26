// service-worker.js

const CACHE_NAME = "grad-gallery-cache-v3";

// Files you want to cache for offline use
const urlsToCache = [
  "/", // index.html
  "/LandingPage/LandingPage.css",
  "/LandingPage/LandingPage.js",
  "/Public/Components/Login.php",
  "/Public/Components/Loader.html",
  "/Public/assets/css/Loader.css",

  // Logos
  "/img/PREVIEWLOGO.png",
  "/img/ECALOGO.png",
  "/img/GRALLERYLOGO4.0.png",
  "/img/ABOUTIMG.png",

  // Yearbook Covers
  "/img/YB COVER/MaritimeEducation.png",
  "/img/YB COVER/TourismManagement.png",
  "/img/YB COVER/CriminalJusticeEducation.png",
  "/img/YB COVER/InformationSystem.png",
  "/img/YB COVER/BusinessAdministration.png",
  "/img/YB COVER/Education.png",
  "/img/YB COVER/Nursing.png",

  // Carousel images
  "/img/CAROUSEL/sample1.jpg",
  "/img/CAROUSEL/sample2.jpg",
  "/img/CAROUSEL/sample3.jpg",
  "/img/CAROUSEL/sample4.jpg",
  "/img/CAROUSEL/sample5.jpg",
  "/img/CAROUSEL/sample6.jpg",
  "/img/CAROUSEL/sample7.jpg",
  "/img/CAROUSEL/sample8.jpg",
  "/img/CAROUSEL/sample9.jpg",
  "/img/CAROUSEL/sample10.jpg",
  "/img/CAROUSEL/sample11.jpg",
  "/img/CAROUSEL/sample12.jpg",
  "/img/CAROUSEL/sample13.jpg",
  "/img/CAROUSEL/sample14.jpg",
  "/img/CAROUSEL/sample15.jpg",
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
