<h1>現在の番組表</h1>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
<div>
    <a class="nav-link" href="{{ route('login') }}">ログイン</a>
</div>
<div>
    @if (Route::has('register'))
    <a class="nav-link" href="{{ route('register') }}">会員登録</a>
    @endif
</div>

<table class="table">
    <thead>
        <tr>
            <th>放送局</th>
            <th>番組名</th>
            <th>放送時間</th>
            <th>番組ホームページ</th>
        </tr>
    </thead>
    <tbody>
        @foreach($results as $result)
            <tr>
                <td>{{$result['station']}}</td>
                <td>{{$result['title']}}</td>
                <td>{{$result['start'] . ' ' . '-' . ' '. $result['end']}}</td>
                <a href="http://"></a>
                <td><a href="{{$result['url']}}">番組ホームページ</a></td>
            </tr>
    @endforeach
    </tbody>
</table>
