// service-worker.js

const CACHE_VERSION = "v7";
const CACHE_PREFIX = "ecadyb";
const STATIC_CACHE = `${CACHE_PREFIX}-static-${CACHE_VERSION}`;
const DYNAMIC_CACHE = `${CACHE_PREFIX}-dynamic-${CACHE_VERSION}`;
const CACHE_WHITELIST = [STATIC_CACHE, DYNAMIC_CACHE];

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
  "/Public/assets/js/ForgotPassword.js",
  "/Public/assets/css/Loader.css",
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
  "https://ECADYB.b-cdn.net/img/YB COVER/Nursing.png",
  "https://ECADYB.b-cdn.net/img/ECABG.jpg",
  "https://ECADYB.b-cdn.net/img/ADMINGRALLERYLOGO.png",
];

// Install event - cache critical resources
self.addEventListener("install", (event) => {
  console.log(`[ServiceWorker] Installing version ${CACHE_VERSION}...`);

  // Skip waiting to activate the new service worker immediately
  self.skipWaiting();

  // Cache static assets
  event.waitUntil(
    caches
      .open(STATIC_CACHE)
      .then((cache) => {
        console.log("[ServiceWorker] Caching static assets");
        return Promise.allSettled(
          STATIC_ASSETS.map((asset) =>
            cache.add(asset).catch((err) => {
              console.warn(`[ServiceWorker] Failed to cache ${asset}:`, err);
              return Promise.reject(`${asset}: ${err.message}`);
            })
          )
        ).then((results) => {
          const failed = results.filter((r) => r.status === "rejected");
          if (failed.length > 0) {
            const errorDetails = failed.map((f) => f.reason).join("\n");
            console.warn(
              `[ServiceWorker] Failed to cache ${failed.length} out of ${STATIC_ASSETS.length} static assets. Failed items:\n${errorDetails}`
            );
          } else {
            console.log(
              "[ServiceWorker] All static assets cached successfully"
            );
          }
        });
      })
      .then(() => {
        // Cache CDN assets in the background
        caches.open(DYNAMIC_CACHE).then((cache) => {
          console.log("[ServiceWorker] Caching CDN assets");
          return Promise.allSettled(
            CDN_ASSETS.map((url) =>
              fetch(url, { credentials: "omit" })
                .then((response) => {
                  if (response.ok) {
                    return cache.put(url, response);
                  }
                  console.warn(
                    `[ServiceWorker] Failed to cache CDN asset (${response.status}): ${url}`
                  );
                })
                .catch((err) => {
                  console.warn(
                    `[ServiceWorker] Failed to fetch CDN asset: ${url}`,
                    err
                  );
                })
            )
          );
        });
      })
  );
});

// Activate event - clean up old caches
self.addEventListener("activate", (event) => {
  console.log("[ServiceWorker] Activating new service worker...");

  event.waitUntil(
    caches
      .keys()
      .then((cacheNames) => {
        return Promise.all(
          cacheNames
            .filter((cacheName) => {
              // Delete caches that aren't in our whitelist
              const shouldDelete = !CACHE_WHITELIST.includes(cacheName);
              if (shouldDelete) {
                console.log(`[ServiceWorker] Deleting old cache: ${cacheName}`);
              }
              return shouldDelete;
            })
            .map((cacheName) => caches.delete(cacheName))
        );
      })
      .then(() => {
        // Take control of all clients
        return self.clients.claim();
      })
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

  // Skip caching for admin dashboard URLs
  if (
    url.pathname.includes("/AdminDashboard/") ||
    url.pathname.includes("/admin/") ||
    url.pathname.includes("admin-theme")
  ) {
    // Use network-only strategy for admin content
    event.respondWith(fetch(request));
    return;
  }

  // Strategy 1: Network-first for API calls and dynamic content
  if (
    url.pathname.includes("/Connection/") ||
    url.pathname.includes("/Components/") ||
    url.pathname.endsWith(".php")
  ) {
    event.respondWith(networkFirst(request));
    return;
  }

  // Strategy 2: Cache-first for static assets and images
  if (
    request.destination === "image" ||
    request.destination === "style" ||
    request.destination === "script" ||
    url.hostname === "ecadyb.b-cdn.net"
  ) {
    event.respondWith(cacheFirst(request));
    return;
  }

  // Strategy 3: Stale-while-revalidate for HTML and other content
  event.respondWith(staleWhileRevalidate(request));
});

// Cache-first strategy
async function cacheFirst(request) {
  try {
    // Try to get from cache first
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      // Update cache in the background
      fetchAndCache(request);
      return cachedResponse;
    }

    // If not in cache, try network
    const networkResponse = await fetch(request);
    if (networkResponse.ok) {
      // Clone the response because it can only be consumed once
      const responseToCache = networkResponse.clone();
      caches
        .open(DYNAMIC_CACHE)
        .then((cache) => cache.put(request, responseToCache))
        .catch((err) =>
          console.warn(`[ServiceWorker] Failed to cache: ${request.url}`, err)
        );
    }
    return networkResponse;
  } catch (error) {
    console.warn(
      `[ServiceWorker] Cache-first failed for: ${request.url}`,
      error
    );

    // Try to return a fallback response if available
    const fallbackResponse =
      (await caches.match("/offline.html")) ||
      new Response("Offline content unavailable", {
        status: 503,
        statusText: "Service Unavailable",
        headers: { "Content-Type": "text/plain" },
      });

    return fallbackResponse;
  }
}

// Helper function to fetch and cache responses
async function fetchAndCache(request) {
  try {
    const response = await fetch(request);
    if (response.ok) {
      const cache = await caches.open(DYNAMIC_CACHE);
      await cache.put(request, response.clone());
    }
    return response;
  } catch (error) {
    console.warn(
      `[ServiceWorker] Background fetch failed for: ${request.url}`,
      error
    );
    return null;
  }
}

// Network-first strategy
async function networkFirst(request) {
  try {
    // Try network first
    const networkResponse = await fetch(request);
    if (networkResponse.ok) {
      // Update cache in the background
      const responseToCache = networkResponse.clone();
      caches
        .open(DYNAMIC_CACHE)
        .then((cache) => cache.put(request, responseToCache))
        .catch((err) =>
          console.warn(`[ServiceWorker] Failed to cache: ${request.url}`, err)
        );
      return networkResponse;
    }
    // If network fails but we have a cached version, return that
    const cachedResponse = await caches.match(request);
    return cachedResponse || networkResponse;
  } catch (error) {
    console.warn(
      `[ServiceWorker] Network-first failed for: ${request.url}`,
      error
    );
    // Try to return cached version
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      return cachedResponse;
    }
    // Return offline page or error response
    return (
      caches.match("/offline.html") ||
      new Response("Offline - content unavailable", {
        status: 503,
        statusText: "Service Unavailable",
        headers: { "Content-Type": "text/plain" },
      })
    );
  }
}

// Stale-while-revalidate strategy
async function staleWhileRevalidate(request) {
  // Try to get from cache immediately
  const cachedResponse = await caches.match(request);

  // Always make the network request in the background
  const fetchPromise = fetch(request)
    .then((networkResponse) => {
      // Update cache if we get a valid response
      if (networkResponse.ok) {
        const responseToCache = networkResponse.clone();
        caches
          .open(STATIC_CACHE)
          .then((cache) => cache.put(request, responseToCache))
          .catch((err) =>
            console.warn(
              `[ServiceWorker] Failed to update cache: ${request.url}`,
              err
            )
          );
      }
      return networkResponse;
    })
    .catch((error) => {
      console.warn(
        `[ServiceWorker] Stale-while-revalidate failed for: ${request.url}`,
        error
      );
      // If we have a cached version, use it
      if (cachedResponse) {
        return cachedResponse;
      }
      // Otherwise return offline page or error
      return (
        caches.match("/offline.html") ||
        new Response("Offline - content unavailable", {
          status: 503,
          statusText: "Service Unavailable",
          headers: { "Content-Type": "text/plain" },
        })
      );
    });

  // Return cached response immediately if available, otherwise wait for network
  return cachedResponse || fetchPromise;
}

// Message event for manual cache updates
self.addEventListener("message", (event) => {
  if (event.data && event.data.type === "SKIP_WAITING") {
    self.skipWaiting();
  }

  if (event.data && event.data.type === "CLEAR_CACHE") {
    event.waitUntil(
      caches
        .keys()
        .then((cacheNames) =>
          Promise.all(cacheNames.map((cacheName) => caches.delete(cacheName)))
        )
        .then(() => {
          console.log("[ServiceWorker] All caches cleared");
          event.ports[0].postMessage({ success: true });
        })
    );
  }
});
