<head>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="col-12 clearfix">
            <div class="float-left">
                <ul class="navbar-nav">
                    <li class="nav-item active">
                        <h5><a class="nav-link" href="/">Home</a></h5>
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
                    <a class="nav-link" href="radioProgramList">放送中の番組</a>
                  </li>
                  {{ Form::open(['route' => 'search','method' => 'get']) }}
                      <form class="form-inline">
                          {{ Form::text('title','',['class' => 'form-control','placeholder' => '番組名']) }}
                      </form>
                      <form class="form-inline">
                          {{ Form::submit('検索',['class' => 'btn btn-primary']) }}
                      </form>
                  {{ Form::close() }}
                </ul>
            </div>
        </div>
    </nav>
</head>
@yield('content')
