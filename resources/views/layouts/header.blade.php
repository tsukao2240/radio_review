<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="auto">
<script>
    // システムのダークモード設定を検出して適用
    (function() {
        const theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        document.documentElement.setAttribute('data-bs-theme', theme);
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
            document.documentElement.setAttribute('data-bs-theme', e.matches ? 'dark' : 'light');
        });
    })();
</script>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'RadioProgram Review') }}</title>

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#667eea">
    <meta name="color-scheme" content="light dark">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Radio Review">
    <meta name="description" content="ラジオ番組のレビューと録音管理アプリケーション">

    <!-- PWA Icons -->
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" sizes="72x72" href="/images/icons/icon-72x72.png">
    <link rel="apple-touch-icon" sizes="96x96" href="/images/icons/icon-96x96.png">
    <link rel="apple-touch-icon" sizes="128x128" href="/images/icons/icon-128x128.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/images/icons/icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/images/icons/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="192x192" href="/images/icons/icon-192x192.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/images/icons/icon-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/images/icons/icon-512x512.png">

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/css/custom.css', 'resources/js/app.jsx'])
</head>

<body>
    <div id="app">
        <!-- ヘッダーナビゲーション -->
        <nav class="fixed top-0 left-0 right-0 z-50 bg-gradient-to-r from-primary-500 to-primary-900 shadow-lg" role="navigation">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between h-16 md:h-20">
                    <!-- ロゴ -->
                    <a href="/" class="text-white font-bold text-xl md:text-2xl flex-shrink-0">
                        RadioProgram Review
                    </a>

                    <!-- ハンバーガーメニューボタン（モバイルのみ） -->
                    <button
                        id="mobile-menu-toggle"
                        class="md:hidden touch-target text-white focus:outline-none focus:ring-2 focus:ring-white/50 rounded-lg p-2"
                        aria-label="メニューを開く"
                        aria-expanded="false"
                        aria-controls="mobile-menu"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <!-- デスクトップメニュー -->
                    <div class="hidden md:flex md:items-center md:space-x-2">
                        <a href="{{ route('program.search') }}" class="touch-target text-white hover:bg-white/10 rounded-lg px-3 py-2 transition flex items-center text-sm">
                            <i class="fas fa-search mr-2"></i>番組検索
                        </a>
                        <a href="{{ route('program.schedule') }}" class="touch-target text-white hover:bg-white/10 rounded-lg px-3 py-2 transition flex items-center text-sm">
                            <i class="fas fa-broadcast-tower mr-2"></i>放送中
                        </a>
                        <a href="{{ route('schedule.twoweek') }}" class="touch-target text-white hover:bg-white/10 rounded-lg px-3 py-2 transition flex items-center text-sm">
                            <i class="fas fa-podcast mr-2"></i>録音
                        </a>
                        <a href="{{ route('recording.history') }}" class="touch-target text-white hover:bg-white/10 rounded-lg px-3 py-2 transition flex items-center text-sm">
                            <i class="fas fa-microphone mr-2"></i>履歴
                        </a>
                        
                        @auth
                        <a href="{{ route('favorites.index') }}" class="touch-target text-white hover:bg-white/10 rounded-lg px-3 py-2 transition flex items-center text-sm">
                            <i class="fas fa-heart mr-2"></i>お気に入り
                        </a>
                        <a href="{{ route('review.view') }}" class="touch-target text-white hover:bg-white/10 rounded-lg px-3 py-2 transition flex items-center text-sm">
                            <i class="fas fa-book-open mr-2"></i>レビュー
                        </a>
                        
                        <!-- 通知センター -->
                        <div id="notification-center-mount"></div>
                        
                        <!-- ユーザーメニュー（ドロップダウン） -->
                        <div class="relative ml-2 group">
                            <button class="touch-target text-white hover:bg-white/10 rounded-lg px-3 py-2 transition flex items-center text-sm">
                                <i class="fas fa-user-circle mr-2"></i>
                                {{ Auth::user()->name }}
                                <i class="fas fa-chevron-down ml-2"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-xl py-2 invisible group-hover:visible transition-all">
                                <a href="{{ route('myreview.view') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                    <i class="fas fa-user mr-2"></i>マイページ
                                </a>
                                <a href="{{ route('recording.schedules') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                    <i class="fas fa-calendar-check mr-2"></i>録音予約
                                </a>
                                <a href="{{ route('recommendations.index') }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                    <i class="fas fa-star mr-2"></i>おすすめ
                                </a>
                                <div class="border-t border-gray-200 dark:border-gray-700 my-2"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                        <i class="fas fa-sign-out-alt mr-2"></i>ログアウト
                                    </button>
                                </form>
                            </div>
                        </div>
                        @else
                        <a href="{{ route('review.view') }}" class="touch-target text-white hover:bg-white/10 rounded-lg px-3 py-2 transition flex items-center text-sm">
                            <i class="fas fa-book-open mr-2"></i>レビュー
                        </a>
                        <a href="{{ route('login') }}" class="touch-target text-white hover:bg-white/10 rounded-lg px-3 py-2 transition flex items-center text-sm">
                            <i class="fas fa-sign-in-alt mr-2"></i>ログイン
                        </a>
                        @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="touch-target bg-white/20 text-white hover:bg-white/30 rounded-lg px-4 py-2 transition flex items-center text-sm font-semibold">
                            <i class="fas fa-user-plus mr-2"></i>会員登録
                        </a>
                        @endif
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- モバイルメニュー（スライドイン） -->
        <div
            id="mobile-menu"
            class="fixed inset-y-0 left-0 w-72 bg-gradient-to-b from-primary-600 to-primary-900 transform -translate-x-full transition-transform duration-300 ease-in-out md:hidden z-50 overflow-y-auto"
            aria-hidden="true"
        >
            <div class="p-6 space-y-4">
                <!-- 閉じるボタン -->
                <button
                    id="mobile-menu-close"
                    class="touch-target text-white mb-4"
                    aria-label="メニューを閉じる"
                >
                    <i class="fas fa-times text-2xl"></i>
                </button>

                <!-- メニュー項目（縦配置） -->
                <nav class="space-y-2">
                    <a href="{{ route('program.search') }}" class="block touch-target text-white hover:bg-white/10 rounded-lg px-4 py-3 transition">
                        <i class="fas fa-search mr-3"></i>番組検索
                    </a>
                    <a href="{{ route('program.schedule') }}" class="block touch-target text-white hover:bg-white/10 rounded-lg px-4 py-3 transition">
                        <i class="fas fa-broadcast-tower mr-3"></i>放送中の番組
                    </a>
                    <a href="{{ route('schedule.twoweek') }}" class="block touch-target text-white hover:bg-white/10 rounded-lg px-4 py-3 transition">
                        <i class="fas fa-podcast mr-3"></i>タイムフリー録音
                    </a>
                    <a href="{{ route('recording.history') }}" class="block touch-target text-white hover:bg-white/10 rounded-lg px-4 py-3 transition">
                        <i class="fas fa-microphone mr-3"></i>録音履歴
                    </a>
                    <a href="{{ route('review.view') }}" class="block touch-target text-white hover:bg-white/10 rounded-lg px-4 py-3 transition">
                        <i class="fas fa-book-open mr-3"></i>レビューを見る
                    </a>

                    @auth
                    <div class="border-t border-white/20 pt-4 mt-4">
                        <p class="text-white/70 text-sm px-4 mb-2">{{ Auth::user()->name }}</p>
                        <a href="{{ route('myreview.view') }}" class="block touch-target text-white hover:bg-white/10 rounded-lg px-4 py-3 transition">
                            <i class="fas fa-user mr-3"></i>マイページ
                        </a>
                        <a href="{{ route('recording.schedules') }}" class="block touch-target text-white hover:bg-white/10 rounded-lg px-4 py-3 transition">
                            <i class="fas fa-calendar-check mr-3"></i>録音予約
                        </a>
                        <a href="{{ route('favorites.index') }}" class="block touch-target text-white hover:bg-white/10 rounded-lg px-4 py-3 transition">
                            <i class="fas fa-heart mr-3"></i>お気に入り番組
                        </a>
                        <a href="{{ route('recommendations.index') }}" class="block touch-target text-white hover:bg-white/10 rounded-lg px-4 py-3 transition">
                            <i class="fas fa-star mr-3"></i>おすすめ
                        </a>
                        <a href="{{ route('post.program') }}" class="block touch-target text-white hover:bg-white/10 rounded-lg px-4 py-3 transition">
                            <i class="fas fa-pen-square mr-3"></i>レビューを投稿
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left touch-target text-white hover:bg-white/10 rounded-lg px-4 py-3 transition">
                                <i class="fas fa-sign-out-alt mr-3"></i>ログアウト
                            </button>
                        </form>
                    </div>
                    @else
                    <div class="border-t border-white/20 pt-4 mt-4">
                        <a href="{{ route('login') }}" class="block touch-target text-white hover:bg-white/10 rounded-lg px-4 py-3 transition">
                            <i class="fas fa-sign-in-alt mr-3"></i>ログイン
                        </a>
                        @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="block touch-target bg-white/20 text-white hover:bg-white/30 rounded-lg px-4 py-3 transition font-semibold">
                            <i class="fas fa-user-plus mr-3"></i>会員登録
                        </a>
                        @endif
                    </div>
                    @endauth
                </nav>
            </div>
        </div>

        <!-- オーバーレイ（メニュー表示時に背景を暗くする） -->
        <div
            id="mobile-menu-overlay"
            class="fixed inset-0 bg-black/50 hidden md:hidden z-40"
            aria-hidden="true"
        ></div>

        <!-- メインコンテンツ -->
        <main class="main-content container mx-auto px-4 md:px-6">
            @yield('content')
        </main>

        @include('includes.footer')
    </div>
</body>

</html>
