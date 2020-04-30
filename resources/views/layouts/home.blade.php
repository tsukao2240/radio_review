<html>

<head>
    <!-- Fonts -->
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.6.3/css/all.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!--Bootstrap -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet" type="text/css">
</head>
<header class="sticky-top">
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
                <ul class="navbar-nav">
                    @if (Auth::check())
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                            aria-haspopup="true" aria-expanded="false">
                            {{ Auth::user()->name }}
                        </a>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="{{ route('myreview') }}">投稿したレビューを見る</a>
                            <a class="dropdown-item" href="{{ route('logout') }}">
                                {{ Form::open(['route' => 'logout','method' => 'POST']) }}
                                {{ Form::submit('ログアウト',['class' => 'btn btn-white']) }}
                                {{ Form::close() }}
                            </a>
                        </div>
                    </li>
                    @else
                    <li class="nav-item active">
                        @if (Route::has('register'))
                        <a class="nav-link" href="{{ route('register') }}">会員登録</a>
                        @endif
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link" href="{{ route('login') }}">ログイン <span class="sr-only">(current)</span></a>
                    </li>
                    @endif
                    <li class="nav-item active">
                        <a class="nav-link" href="{{ route('schedule') }}">放送中の番組</a>
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link" href="{{ route('view') }}">レビューを見る</a>
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link" href="{{ route('program') }}">レビューを投稿する</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div>

    </div>

</header>

<body>
    {{ csrf_field() }}
    {{ Form::open(['route' => 'search','method' => 'get']) }}
    <div class="mx-auto" style="width:400">
        <div class="input-group">
            {{ Form::text('title','',['class' => 'form-control','placeholder' => '番組名で検索する']) }}
            {{ Form::button('<i class="fas fa-search"></i>',['class' => 'btn','type' => 'submit']) }}
        </div>
    </div>
    {{ Form::close() }}
    @yield('content')
    <!--JavaScript-->
    <script src="{{ mix('js/app.js') }}" defer></script>
</body>

</html>
