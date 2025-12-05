// Service worker with improved caching strategy and offline support
const CACHE_NAME = 'career-connect-v2';
const ASSETS_TO_CACHE = [
  '/',
  '/index.php',
  '/css/global.css',
  '/css/base.css',
  '/css/form.css',
  '/css/student.css',
  '/css/student-profile.css',
  '/js/navbar.js',
  '/js/auth.js',
  '/js/profile.js',
  '/manifest.json',
  '/assets/icons/icon-192.png',
  '/assets/icons/icon-512.png',
  '/uploads/profile/default-avatar.png'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(ASSETS_TO_CACHE))
  );
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', event => {
  // Skip non-GET requests
  if (event.request.method !== 'GET') return;

  // Handle API calls differently
  if (event.request.url.includes('/api/')) {
    event.respondWith(
      fetch(event.request)
        .then(response => {
          return response;
        })
        .catch(() => {
          // For API calls that fail, return a custom offline response
          return new Response(JSON.stringify({
            error: 'You are currently offline. Please check your connection.'
          }), {
            headers: { 'Content-Type': 'application/json' }
          });
        })
    );
    return;
  }

  // For non-API requests, use cache-first strategy with network fallback
  event.respondWith(
    caches.match(event.request)
      .then(cachedResponse => {
        // Return cached response if found
        if (cachedResponse) {
          // Fetch from network in background to update cache
          fetch(event.request)
            .then(response => {
              if (response && response.status === 200) {
                caches.open(CACHE_NAME)
                  .then(cache => {
                    if (event.request.url.startsWith(self.location.origin)) {
                      cache.put(event.request, response);
                    }
                  });
              }
            })
            .catch(() => {/* Ignore background fetch errors */});
          
          return cachedResponse;
        }

        // If not in cache, fetch from network
        return fetch(event.request)
          .then(response => {
            // Don't cache if not a success response
            if (!response || response.status !== 200) {
              return response;
            }

            // Clone the response since it can only be consumed once
            const responseToCache = response.clone();

            // Add the new response to cache
            caches.open(CACHE_NAME)
              .then(cache => {
                // Only cache same-origin requests
                if (event.request.url.startsWith(self.location.origin)) {
                  cache.put(event.request, responseToCache);
                }
              });

            return response;
          })
          .catch(() => {
            // If both cache and network fail, return a fallback page
            if (event.request.mode === 'navigate') {
              return caches.match('/index.php');
            }
            throw new Error('Network error');
          });
      })
  );
});
