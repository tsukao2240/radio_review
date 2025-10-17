<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#f8f9fa">
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
        <header class="head-animation">
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="col-12 clearfix">
                    <div class="float-left">
                        <ul class="navbar-nav">
                            <li class="nav-item active">
                                <h3><a class="nav-link" href="/">RadioProgram Review</a></h3>
                            </li>
                        </ul>
                    </div>
                    <div class="float-right">
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item active">
                                <a class="hover nav-link" href="{{ route('program.schedule') }}">
                                    <i class="fas fa-broadcast-tower fa-fw fa-lg"></i>
                                    <span class="text-center">
                                        放送中の番組
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item active">
                                <a class="hover nav-link" href="{{ route('recording.history') }}">
                                    <i class="fas fa-microphone fa-fw fa-lg"></i>
                                    <span class="text-center">
                                        録音履歴
                                    </span>
                                </a>
                            </li>
                            @if (Auth::check())
                            <li class="nav-item active">
                                <a class="hover nav-link" href="{{ route('recording.schedules') }}">
                                    <i class="fas fa-calendar-check fa-fw fa-lg"></i>
                                    <span class="text-center">
                                        録音予約
                                    </span>
                                </a>
                            </li>
                            @endif
                            @if (Auth::check())
                            <li class="nav-item active">
                                <a class="hover nav-link" href="{{ route('favorites.index') }}">
                                    <i class="fas fa-heart fa-fw fa-lg"></i>
                                    <span class="text-center">
                                        お気に入り番組
                                    </span>
                                </a>
                            </li>
                            @endif
                            <li class="nav-item active">
                                <a class="hover nav-link" href="{{ route('review.view') }}">
                                    <i class="fas fa-book-open fa-fw fa-lg"></i>
                                    <span class="text-center">
                                        レビューを見る
                                    </span>
                                </a>
                            </li>
                            @if (Auth::check())
                            <li class="nav-item active">
                                <a class="hover nav-link" href="{{ route('post.program') }}">
                                    <i class="fas fa-pen-square fa-fw fa-lg"></i>
                                    <span class="text-center"> レビューを投稿する
                                    </span>
                                </a>
                            </li>
                            @else
                            <li class="nav-item active">
                                <a class="hover nav-link" href="{{ route('login') }}">
                                    <i class="fas fa-pen-square fa-fw fa-lg"></i>
                                    <span class="text-center"> レビューを投稿する
                                    </span>
                                </a>
                            </li>
                            @endif
                            @if (Auth::check())
                            <li class="nav-item active">
                                <!-- 通知センター -->
                                <div id="notification-center-mount"></div>
                            </li>
                            <li class="nav-item dropdown active">
                                <a class="hover nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                                    aria-haspopup="true" aria-expanded="false">
                                    <span><i class="fas fa-user-circle fa-fw fa-lg"></i>{{ Auth::user()->name }}</span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="{{ route('myreview.view') }}">投稿したレビューを見る</a>
                                    @csrf
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">ログアウト</button>
                                    </form>
                                </div>
                            </li>
                            @else
                            <li class="nav-item active">
                                @if (Route::has('register'))
                                <a class="hover nav-link" href="{{ route('register') }}">
                                    <i class="fas fa-registered fa-fw fa-lg"></i>
                                    <span class="text-center">
                                        会員登録
                                    </span>
                                </a>
                                @endif
                            </li>
                            <li class="nav-item active">
                                <a class="hover nav-link" href="{{ route('login') }}">
                                    <i class="fas fa-sign-in-alt fa-fw fa-lg"></i>
                                    <span class="text-center">ログイン</span></a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </nav>
        </header>

        <main class="py-4">
            @yield('content')
        </main>

        @include('includes.footer')
    </div>
</body>

</html>
