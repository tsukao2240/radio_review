@extends('layouts.home')
@section('content')
<header>
    <title>
        投稿したレビュー
    </title>
</header>
@if (!$posts->isEmpty())
<body>
    <h3 style="text-align:center">投稿したレビュー</h3>
    <br>
    @foreach ($posts as $post)
    <div class="card">
        <div class="card-header">
            {{ $post->program_title }}
        </div>
        <div class="card-body">
            {{ $post->title }}
        </div>
        <div class="card-body">
            {{ $post->body }}
        </div>
    </div>
    @endforeach
</body>
@else
    <body>
        <h3 style="text-align:center">
            まだ投稿がありません
        </h3>
    </body>
@endif
@endsection
