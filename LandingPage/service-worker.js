// service-worker.js

const CACHE_NAME = "ecadyb-yearbook-cache-v5";
const STATIC_CACHE = "ecadyb-static-v5";
const DYNAMIC_CACHE = "ecadyb-dynamic-v5";

// Critical files to cache for offline functionality
const STATIC_ASSETS = [
  "/",
  "/index.php",
  "/LandingPage/LandingPage.html",
  "/LandingPage/LandingPage.css",
  "/LandingPage/LandingPage.js",
  "/Public/Components/Login.html",
  "/Public/Components/Login.php",
  "/Public/Components/Loader.html",
  "/Public/Components/ForgotPassword.html",
  "/Public/assets/css/Login.css",
  "/Public/assets/css/Loader.css",
  "/Public/assets/css/ForgotPassword.css",
  "/Public/assets/js/Login.js",
  "/Public/assets/js/Loader.js",
  "/Public/assets/js/ForgotPassword.js"
];

// CDN assets that should be cached
const CDN_ASSETS = [
  // Core logos
  "https://ECADYB.b-cdn.net/img/PREVIEWLOGO.png",
  "https://ECADYB.b-cdn.net/img/ECALOGO.png",
  "https://ECADYB.b-cdn.net/img/GRALLERYLOGO4.0.png",
  "https://ECADYB.b-cdn.net/img/ABOUTIMG.png",

  // Yearbook covers
  "https://ECADYB.b-cdn.net/img/YB COVER/MaritimeEducation.png",
  "https://ECADYB.b-cdn.net/img/YB COVER/TourismManagement.png",
  "https://ECADYB.b-cdn.net/img/YB COVER/CriminalJusticeEducation.png",
  "https://ECADYB.b-cdn.net/img/YB COVER/InformationSystem.png",
  "https://ECADYB.b-cdn.net/img/YB COVER/BusinessAdministration.png",
  "https://ECADYB.b-cdn.net/img/YB COVER/Education.png",
  "https://ECADYB.b-cdn.net/img/YB COVER/Nursing.png"
];

// Install event - cache critical resources
self.addEventListener("install", (event) => {
  console.log("[ServiceWorker] Installing version 4...");
  
  event.waitUntil(
    Promise.all([
      // Cache static assets
      caches.open(STATIC_CACHE).then((cache) => {
        console.log("[ServiceWorker] Caching static assets");
        return Promise.allSettled(
          STATIC_ASSETS.map((url) =>
            cache.add(url).catch((err) => {
              console.warn(`[ServiceWorker] Failed to cache static asset: ${url}`, err);
            })
          )
        );
      }),
      
      // Cache CDN assets
      caches.open(DYNAMIC_CACHE).then((cache) => {
        console.log("[ServiceWorker] Caching CDN assets");
        return Promise.allSettled(
          CDN_ASSETS.map((url) =>
            cache.add(url).catch((err) => {
              console.warn(`[ServiceWorker] Failed to cache CDN asset: ${url}`, err);
            })
          )
        );
      })
    ])
  );
  
  // Force activation of new service worker
  self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener("activate", (event) => {
  console.log("[ServiceWorker] Activating new service worker...");
  
  const cacheWhitelist = [STATIC_CACHE, DYNAMIC_CACHE];
  
  event.waitUntil(
    Promise.all([
      // Clean up old caches
      caches.keys().then((cacheNames) =>
        Promise.all(
          cacheNames.map((cacheName) => {
            if (!cacheWhitelist.includes(cacheName)) {
              console.log(`[ServiceWorker] Deleting old cache: ${cacheName}`);
              return caches.delete(cacheName);
            }
          })
        )
      ),
      
      // Take control of all clients
      self.clients.claim()
    ])
  );
});

// Fetch event - implement caching strategies
self.addEventListener("fetch", (event) => {
  const { request } = event;
  const url = new URL(request.url);
  
  // Skip non-GET requests
  if (request.method !== "GET") {
    return;
  }
  
  // Skip Chrome extension requests
  if (url.protocol === "chrome-extension:") {
    return;
  }
  
  // Strategy 1: Network-first for API calls and dynamic content
  if (url.pathname.includes("/Connection/") || 
      url.pathname.includes("/Components/") ||
      url.pathname.endsWith(".php")) {
    event.respondWith(networkFirst(request));
    return;
  }
  
  // Strategy 2: Cache-first for static assets and images
  if (request.destination === "image" || 
      request.destination === "style" ||
      request.destination === "script" ||
      url.hostname === "ecadyb.b-cdn.net") {
    event.respondWith(cacheFirst(request));
    return;
  }
  
  // Strategy 3: Stale-while-revalidate for HTML and other content
  event.respondWith(staleWhileRevalidate(request));
});

// Cache-first strategy
async function cacheFirst(request) {
  try {
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      return cachedResponse;
    }
    
    const networkResponse = await fetch(request);
    if (networkResponse && networkResponse.status === 200) {
      const cache = await caches.open(DYNAMIC_CACHE);
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  } catch (error) {
    console.warn(`[ServiceWorker] Cache-first failed for: ${request.url}`, error);
    // Try to return cached version as fallback
    return caches.match(request) || new Response("Offline content unavailable", {
      status: 503,
      statusText: "Service Unavailable"
    });
  }
}

// Network-first strategy
async function networkFirst(request) {
  try {
    const networkResponse = await fetch(request);
    if (networkResponse && networkResponse.status === 200) {
      const cache = await caches.open(DYNAMIC_CACHE);
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  } catch (error) {
    console.warn(`[ServiceWorker] Network-first failed for: ${request.url}`, error);
    const cachedResponse = await caches.match(request);
    return cachedResponse || new Response("Offline - content unavailable", {
      status: 503,
      statusText: "Service Unavailable"
    });
  }
}

// Stale-while-revalidate strategy
async function staleWhileRevalidate(request) {
  const cachedResponse = await caches.match(request);
  
  const fetchPromise = fetch(request).then((networkResponse) => {
    if (networkResponse && networkResponse.status === 200) {
      const cache = caches.open(STATIC_CACHE);
      cache.then(c => c.put(request, networkResponse.clone()));
    }
    return networkResponse;
  }).catch((error) => {
    console.warn(`[ServiceWorker] Stale-while-revalidate fetch failed for: ${request.url}`, error);
  });
  
  return cachedResponse || fetchPromise;
}

// Message event for manual cache updates
self.addEventListener("message", (event) => {
  if (event.data && event.data.type === "SKIP_WAITING") {
    self.skipWaiting();
  }
  
  if (event.data && event.data.type === "CLEAR_CACHE") {
    event.waitUntil(
      caches.keys().then((cacheNames) =>
        Promise.all(cacheNames.map((cacheName) => caches.delete(cacheName)))
      ).then(() => {
        console.log("[ServiceWorker] All caches cleared");
        event.ports[0].postMessage({ success: true });
      })
    );
  }
});
