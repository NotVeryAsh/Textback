// Minimal service worker: makes Textback installable ("Add to Home Screen")
// without caching app responses (avoids stale-page bugs). Network is always
// used; the empty fetch handler is only here to satisfy install criteria.
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (event) => event.waitUntil(self.clients.claim()));
self.addEventListener('fetch', () => {});
