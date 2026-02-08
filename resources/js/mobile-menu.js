/**
 * モバイルメニュー制御
 * ハンバーガーメニューの開閉、オーバーレイ、ESCキー対応
 */

document.addEventListener('DOMContentLoaded', () => {
    const menuToggle = document.getElementById('mobile-menu-toggle');
    const menuClose = document.getElementById('mobile-menu-close');
    const mobileMenu = document.getElementById('mobile-menu');
    const overlay = document.getElementById('mobile-menu-overlay');
    const menuLinks = mobileMenu?.querySelectorAll('a');

    const openMenu = () => {
        if (!mobileMenu || !overlay) return;
        
        mobileMenu.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        menuToggle?.setAttribute('aria-expanded', 'true');
        mobileMenu.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden'; // スクロール無効
    };

    const closeMenu = () => {
        if (!mobileMenu || !overlay) return;
        
        mobileMenu.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        menuToggle?.setAttribute('aria-expanded', 'false');
        mobileMenu.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = ''; // スクロール有効
    };

    // ハンバーガーメニューボタンクリック
    menuToggle?.addEventListener('click', openMenu);

    // 閉じるボタンクリック
    menuClose?.addEventListener('click', closeMenu);

    // オーバーレイクリック
    overlay?.addEventListener('click', closeMenu);

    // メニュー内のリンククリックで自動的に閉じる
    menuLinks?.forEach(link => {
        link.addEventListener('click', closeMenu);
    });

    // ESCキーでメニューを閉じる
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && mobileMenu && !mobileMenu.classList.contains('-translate-x-full')) {
            closeMenu();
        }
    });
});
