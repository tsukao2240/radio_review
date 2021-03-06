<html>

<head>
    <!-- Fonts -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!--Style -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ mix('css/all.css') }}" rel="stylesheet" type="text/css">
</head>

<body>
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
                        <li class="nav-item dropdown active">
                            <a class="hover nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                                aria-haspopup="true" aria-expanded="false">
                                <span><i class="fas fa-user-circle fa-fw fa-lg"></i>{{ Auth::user()->name }}</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="{{ route('myreview.view') }}">投稿したレビューを見る</a>
                                @csrf
                                {{ Form::open(['route' => 'logout','method' => 'POST']) }}
                                {{ Form::submit('ログアウト',['class' => 'dropdown-item']) }}
                                {{ Form::close() }}
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
    @yield('content')
    @include('includes.footer')
    <!--JavaScript-->
    <script src="{{ mix('js/app.js') }}" defer></script>
</body>

</html>
