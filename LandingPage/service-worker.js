// service-worker.js

/*
const CACHE_VERSION = "v8";
const CACHE_PREFIX = "ecadyb";
const STATIC_CACHE = `${CACHE_PREFIX}-static-${CACHE_VERSION}`;
const DYNAMIC_CACHE = `${CACHE_PREFIX}-dynamic-${CACHE_VERSION}`;
const IMAGE_CACHE = `${CACHE_PREFIX}-images-${CACHE_VERSION}`;
const CACHE_WHITELIST = [STATIC_CACHE, DYNAMIC_CACHE, IMAGE_CACHE];

// Critical files to cache for offline functionality
const STATIC_ASSETS = [
  "/",
  "/index.php",
  "/LandingPage/LandingPage.html",
  "/LandingPage/LandingPage.css",
  "/LandingPage/LandingPage.js",
  "/Public/Components/Login.html",
  "/Login",
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

// High-priority images that should be cached immediately on install
const PRIORITY_IMAGES = [
  // Core logos - loaded first for branding
  "https://ECADYB.b-cdn.net/img/ECALOGO.png",
  "https://ECADYB.b-cdn.net/img/PREVIEWLOGO.png",
  "https://ECADYB.b-cdn.net/img/GRALLERYLOGO4.0.png",
  "https://ecadyb.b-cdn.net/img/ECABG1.jpg",
  "https://ECADYB.b-cdn.net/img/ABOUTIMG.png",
  "https://ECADYB.b-cdn.net/img/ADMINGRALLERYLOGO.png",
];

// Secondary images that should be cached but with lower priority
const SECONDARY_IMAGES = [
  // Yearbook covers - loaded after priority images
  "https://ECADYB.b-cdn.net/img/YB COVER/MaritimeEducation.png",
  "https://ECADYB.b-cdn.net/img/YB COVER/TourismManagement.png",
  "https://ECADYB.b-cdn.net/img/YB COVER/CriminalJusticeEducation.png",
  "https://ECADYB.b-cdn.net/img/YB COVER/InformationSystem.png",
  "https://ECADYB.b-cdn.net/img/YB COVER/BusinessAdministration.png",
  "https://ECADYB.b-cdn.net/img/YB COVER/Education.png",
  "https://ECADYB.b-cdn.net/img/YB COVER/Nursing.png",
  
  // Additional carousel images
  "https://ECADYB.b-cdn.net/img/CAROUSEL/carousel1.jpg",
  "https://ECADYB.b-cdn.net/img/CAROUSEL/carousel2.jpg",
  "https://ECADYB.b-cdn.net/img/CAROUSEL/carousel3.jpg",
  "https://ECADYB.b-cdn.net/img/CAROUSEL/carousel4.jpg",
  "https://ECADYB.b-cdn.net/img/CAROUSEL/carousel5.jpg",
  
  // Department logos
  "https://ECADYB.b-cdn.net/img/SampleLogos/BSBALOGO.png",
  "https://ECADYB.b-cdn.net/img/SampleLogos/BSCJELOGO.png",
  "https://ECADYB.b-cdn.net/img/SampleLogos/BSELOGO.png",
  "https://ECADYB.b-cdn.net/img/SampleLogos/BSISLOGO.png",
  "https://ECADYB.b-cdn.net/img/SampleLogos/BSMELOGO.png",
  "https://ECADYB.b-cdn.net/img/SampleLogos/BSNLOGO.png",
  "https://ECADYB.b-cdn.net/img/SampleLogos/BSTMLOGO.png",
];

// Install event - cache critical resources with optimized image loading
self.addEventListener("install", (event) => {
  console.log(`[ServiceWorker] Installing version ${CACHE_VERSION}...`);

  // Skip waiting to activate the new service worker immediately
  self.skipWaiting();

  // Cache all critical resources in parallel for faster initial load
  event.waitUntil(
    Promise.all([
      // Cache static assets
      caches.open(STATIC_CACHE).then((cache) => {
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
      }),

      // Cache priority images immediately for instant display
      caches.open(IMAGE_CACHE).then((cache) => {
        console.log("[ServiceWorker] Caching priority images");
        return Promise.allSettled(
          PRIORITY_IMAGES.map((url) =>
            fetch(url, { 
              credentials: "omit",
              mode: "cors",
              cache: "force-cache" // Use browser cache if available
            })
              .then((response) => {
                if (response.ok) {
                  console.log(`[ServiceWorker] Cached priority image: ${url}`);
                  return cache.put(url, response);
                }
                console.warn(
                  `[ServiceWorker] Failed to cache priority image (${response.status}): ${url}`
                );
              })
              .catch((err) => {
                console.warn(
                  `[ServiceWorker] Failed to fetch priority image: ${url}`,
                  err
                );
              })
          )
        ).then((results) => {
          const succeeded = results.filter((r) => r.status === "fulfilled").length;
          console.log(
            `[ServiceWorker] Cached ${succeeded}/${PRIORITY_IMAGES.length} priority images`
          );
        });
      }),

      // Cache secondary images with slight delay to prioritize critical assets
      new Promise((resolve) => setTimeout(resolve, 500)).then(() => {
        return caches.open(IMAGE_CACHE).then((cache) => {
          console.log("[ServiceWorker] Caching secondary images");
          return Promise.allSettled(
            SECONDARY_IMAGES.map((url) =>
              fetch(url, { 
                credentials: "omit",
                mode: "cors",
                cache: "force-cache"
              })
                .then((response) => {
                  if (response.ok) {
                    return cache.put(url, response);
                  }
                  console.warn(
                    `[ServiceWorker] Failed to cache secondary image (${response.status}): ${url}`
                  );
                })
                .catch((err) => {
                  console.warn(
                    `[ServiceWorker] Failed to fetch secondary image: ${url}`,
                    err
                  );
                })
            )
          ).then((results) => {
            const succeeded = results.filter((r) => r.status === "fulfilled").length;
            console.log(
              `[ServiceWorker] Cached ${succeeded}/${SECONDARY_IMAGES.length} secondary images`
            );
          });
        });
      })
    ])
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

  // Strategy 2: Cache-first for images with dedicated image cache
  if (
    request.destination === "image" ||
    url.hostname === "ecadyb.b-cdn.net" ||
    url.pathname.includes("/img/") ||
    /\.(jpg|jpeg|png|gif|webp|svg)$/i.test(url.pathname)
  ) {
    event.respondWith(cacheFirstImage(request));
    return;
  }

  // Strategy 3: Cache-first for static assets (CSS, JS)
  if (
    request.destination === "style" ||
    request.destination === "script"
  ) {
    event.respondWith(cacheFirst(request));
    return;
  }

  // Strategy 4: Stale-while-revalidate for HTML and other content
  event.respondWith(staleWhileRevalidate(request));
});

// Cache-first strategy specifically optimized for images
async function cacheFirstImage(request) {
  try {
    // Try to get from cache first - images should load instantly if cached
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      console.log(`[ServiceWorker] Serving cached image: ${request.url}`);
      return cachedResponse;
    }

    // If not in cache, fetch from network
    console.log(`[ServiceWorker] Fetching image from network: ${request.url}`);
    const networkResponse = await fetch(request, {
      credentials: "omit",
      mode: "cors"
    });
    
    if (networkResponse.ok) {
      // Clone the response because it can only be consumed once
      const responseToCache = networkResponse.clone();
      caches
        .open(IMAGE_CACHE)
        .then((cache) => {
          cache.put(request, responseToCache);
          console.log(`[ServiceWorker] Cached new image: ${request.url}`);
        })
        .catch((err) =>
          console.warn(`[ServiceWorker] Failed to cache image: ${request.url}`, err)
        );
    }
    return networkResponse;
  } catch (error) {
    console.warn(
      `[ServiceWorker] Cache-first image failed for: ${request.url}`,
      error
    );

    // Try to return a fallback response if available
    const fallbackResponse =
      (await caches.match("/img/placeholder.png")) ||
      new Response("Image unavailable", {
        status: 503,
        statusText: "Service Unavailable",
        headers: { "Content-Type": "text/plain" },
      });

    return fallbackResponse;
  }
}

// Cache-first strategy for static assets
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
*/
