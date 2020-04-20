<html>

<head>
    <!--Bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
        integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <!-- Fonts -->
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.6.3/css/all.min.css">
</head>
<header class="sticky-top">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="col-12 clearfix">
            <div class="float-left">
                <ul class="navbar-nav">
                    <li class="nav-item active">
                        <h5><a class="nav-link" href="/">RadioProgram Review</a></h5>
                    </li>
                </ul>
            </div>
            <div class="float-right">
                <ul class="navbar-nav">
                    <li class="nav-item active">
                        <a class="nav-link" href="{{ route('login') }}">ログイン <span class="sr-only">(current)</span></a>
                    </li>
                    <li class="nav-item active">
                        @if (Route::has('register'))
                        <a class="nav-link" href="{{ route('register') }}">会員登録</a>
                        @endif
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link" href="{{ route('schedule') }}">放送中の番組</a>
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link" href="http://">レビューを見る</a>
                    </li>
                    <li class="nav-item active">
                        <a class="nav-link" href="{{ route('program') }}">レビューを投稿する</a>
                    </li>
                    {{ csrf_field() }}
                    {{ Form::open(['route' => 'search','method' => 'get']) }}
                    <div class="input-group">
                        {{ Form::text('title','',['class' => 'form-control','placeholder' => '番組名を検索する']) }}
                        <div class="input-group-append">
                            {{ Form::button('<i class="fas fa-search"></i>',['class' => 'btn','type' => 'submit']) }}
                        </div>
                    </div>
                    {{ Form::close() }}
                </ul>
            </div>
        </div>
    </nav>

</header>


<body>
    @yield('content')
</body>

</html>
