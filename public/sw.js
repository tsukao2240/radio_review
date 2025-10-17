const CACHE_VERSION = 'v1.0.0';
const CACHE_NAME = `radio-review-${CACHE_VERSION}`;

// キャッシュするリソースのリスト
const STATIC_CACHE_URLS = [
  '/',
  '/css/app.css',
  '/js/app.js',
  '/favicon.ico',
  '/manifest.json'
];

// オフライン時に表示するページ
const OFFLINE_URL = '/offline.html';

// インストールイベント
self.addEventListener('install', (event) => {
  console.log('[ServiceWorker] Install');

  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('[ServiceWorker] Caching static resources');
      return cache.addAll(STATIC_CACHE_URLS).catch((error) => {
        console.error('[ServiceWorker] Failed to cache:', error);
      });
    })
  );

  // 新しいService Workerをすぐにアクティブ化
  self.skipWaiting();
});

// アクティベーションイベント
self.addEventListener('activate', (event) => {
  console.log('[ServiceWorker] Activate');

  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          // 古いキャッシュを削除
          if (cacheName !== CACHE_NAME) {
            console.log('[ServiceWorker] Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );

  // すべてのクライアントを即座に制御
  return self.clients.claim();
});

// フェッチイベント - ネットワーク優先戦略
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // 外部リクエスト（Radiko APIなど）はそのまま通す
  if (url.origin !== location.origin) {
    return;
  }

  // APIリクエストとGET以外のリクエストはService Workerを通さない
  if (url.pathname.startsWith('/api/') || request.method !== 'GET') {
    return;
  }

  // GETリクエストのみキャッシング戦略を適用
  event.respondWith(
    fetch(request)
      .then((response) => {
        // レスポンスが正常な場合、キャッシュに保存
        if (response && response.status === 200 && request.method === 'GET') {
          const responseClone = response.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(request, responseClone);
          });
        }
        return response;
      })
      .catch(() => {
        // ネットワークエラー時、キャッシュから取得
        return caches.match(request).then((cachedResponse) => {
          if (cachedResponse) {
            return cachedResponse;
          }

          // HTMLリクエストの場合、オフラインページを返す
          if (request.headers.get('accept') && request.headers.get('accept').includes('text/html')) {
            return caches.match(OFFLINE_URL);
          }

          // それ以外は失敗を返す
          return new Response('Network error', {
            status: 408,
            headers: { 'Content-Type': 'text/plain' }
          });
        });
      })
  );
});

// メッセージイベント - キャッシュのクリア
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }

  if (event.data && event.data.type === 'CLEAR_CACHE') {
    event.waitUntil(
      caches.keys().then((cacheNames) => {
        return Promise.all(
          cacheNames.map((cacheName) => {
            return caches.delete(cacheName);
          })
        );
      }).then(() => {
        return self.clients.matchAll();
      }).then((clients) => {
        clients.forEach((client) => {
          client.postMessage({ type: 'CACHE_CLEARED' });
        });
      })
    );
  }
});

// プッシュ通知イベント（将来の拡張用）
self.addEventListener('push', (event) => {
  if (!event.data) {
    return;
  }

  const data = event.data.json();
  const options = {
    body: data.body || '新しい通知があります',
    icon: '/images/icons/icon-192x192.png',
    badge: '/images/icons/icon-72x72.png',
    vibrate: [100, 50, 100],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: data.id
    }
  };

  event.waitUntil(
    self.registration.showNotification(data.title || 'RadioProgram Review', options)
  );
});

// 通知クリックイベント
self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  event.waitUntil(
    clients.openWindow('/')
  );
});
