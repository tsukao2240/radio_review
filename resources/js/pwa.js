/**
 * PWA Service Worker 登録スクリプト
 */

// Service Workerのサポートを確認
if ('serviceWorker' in navigator) {
  // ページ読み込み完了後に登録
  window.addEventListener('load', () => {
    registerServiceWorker();
  });
}

/**
 * Service Workerを登録
 */
async function registerServiceWorker() {
  try {
    const registration = await navigator.serviceWorker.register('/sw.js', {
      scope: '/'
    });

    console.log('[PWA] Service Worker registered:', registration.scope);

    // 更新があるかチェック
    registration.addEventListener('updatefound', () => {
      const newWorker = registration.installing;

      newWorker.addEventListener('statechange', () => {
        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
          // 新しいService Workerが利用可能
          showUpdateNotification();
        }
      });
    });

    // 定期的に更新をチェック
    setInterval(() => {
      registration.update();
    }, 60 * 60 * 1000); // 1時間ごと

  } catch (error) {
    console.error('[PWA] Service Worker registration failed:', error);
  }
}

/**
 * 更新通知を表示
 */
function showUpdateNotification() {
  // トースト通知が利用可能な場合は使用
  if (window.toast && typeof window.toast.info === 'function') {
    window.toast.info('新しいバージョンが利用可能です。ページを再読み込みしてください。', {
      position: 'bottom-center',
      autoClose: false,
      closeOnClick: false,
      onClick: () => {
        window.location.reload();
      }
    });
  } else {
    // フォールバック: カスタム通知バナーを表示
    const banner = document.createElement('div');
    banner.id = 'pwa-update-banner';
    banner.style.cssText = `
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 16px;
      text-align: center;
      z-index: 10000;
      box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.2);
      font-family: sans-serif;
    `;

    banner.innerHTML = `
      <div style="max-width: 800px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div style="flex: 1; min-width: 200px;">
          <strong>新しいバージョンが利用可能です</strong>
          <div style="font-size: 14px; margin-top: 4px;">ページを再読み込みして更新してください</div>
        </div>
        <button id="pwa-reload-button" style="background: white; color: #667eea; border: none; padding: 10px 24px; border-radius: 6px; font-weight: bold; cursor: pointer; transition: transform 0.2s;">
          更新する
        </button>
        <button id="pwa-dismiss-button" style="background: transparent; color: white; border: 1px solid white; padding: 10px 24px; border-radius: 6px; cursor: pointer; transition: opacity 0.2s;">
          後で
        </button>
      </div>
    `;

    document.body.appendChild(banner);

    // ボタンイベント
    document.getElementById('pwa-reload-button').addEventListener('click', () => {
      window.location.reload();
    });

    document.getElementById('pwa-dismiss-button').addEventListener('click', () => {
      banner.remove();
    });

    // ホバーエフェクト
    const reloadButton = document.getElementById('pwa-reload-button');
    reloadButton.addEventListener('mouseenter', () => {
      reloadButton.style.transform = 'scale(1.05)';
    });
    reloadButton.addEventListener('mouseleave', () => {
      reloadButton.style.transform = 'scale(1)';
    });
  }
}

/**
 * インストールプロンプトを処理
 */
let deferredPrompt;

window.addEventListener('beforeinstallprompt', (e) => {
  // デフォルトのインストールプロンプトを防止
  e.preventDefault();
  deferredPrompt = e;

  // カスタムインストールボタンを表示
  showInstallButton();
});

/**
 * インストールボタンを表示
 */
function showInstallButton() {
  const installButton = document.createElement('button');
  installButton.id = 'pwa-install-button';
  installButton.innerHTML = `
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
      <polyline points="7 10 12 15 17 10"></polyline>
      <line x1="12" y1="15" x2="12" y2="3"></line>
    </svg>
    <span>アプリをインストール</span>
  `;

  installButton.style.cssText = `
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 30px;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    z-index: 9999;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: transform 0.2s, box-shadow 0.2s;
    font-family: sans-serif;
  `;

  installButton.addEventListener('mouseenter', () => {
    installButton.style.transform = 'scale(1.05)';
    installButton.style.boxShadow = '0 6px 20px rgba(102, 126, 234, 0.6)';
  });

  installButton.addEventListener('mouseleave', () => {
    installButton.style.transform = 'scale(1)';
    installButton.style.boxShadow = '0 4px 15px rgba(102, 126, 234, 0.4)';
  });

  installButton.addEventListener('click', async () => {
    if (!deferredPrompt) {
      return;
    }

    // インストールプロンプトを表示
    deferredPrompt.prompt();

    // ユーザーの選択を待つ
    const { outcome } = await deferredPrompt.userChoice;
    console.log(`[PWA] User response to install prompt: ${outcome}`);

    // プロンプトを使用済みにする
    deferredPrompt = null;

    // ボタンを削除
    installButton.remove();
  });

  document.body.appendChild(installButton);
}

/**
 * インストール完了イベント
 */
window.addEventListener('appinstalled', () => {
  console.log('[PWA] App installed successfully');

  // インストールボタンを削除
  const installButton = document.getElementById('pwa-install-button');
  if (installButton) {
    installButton.remove();
  }

  // インストール完了通知
  if (window.toast && typeof window.toast.success === 'function') {
    window.toast.success('アプリがインストールされました！', {
      position: 'bottom-center',
      autoClose: 3000
    });
  }
});

/**
 * オンライン/オフライン状態の監視
 */
window.addEventListener('online', () => {
  console.log('[PWA] Back online');
  if (window.toast && typeof window.toast.success === 'function') {
    window.toast.success('インターネットに接続されました', {
      position: 'bottom-center',
      autoClose: 2000
    });
  }
});

window.addEventListener('offline', () => {
  console.log('[PWA] Offline');
  if (window.toast && typeof window.toast.warning === 'function') {
    window.toast.warning('オフラインモードです', {
      position: 'bottom-center',
      autoClose: 2000
    });
  }
});

// エクスポート（必要に応じて）
export { registerServiceWorker };
