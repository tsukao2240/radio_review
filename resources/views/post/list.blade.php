@extends('layouts.home')
@section('content')
<header>
    <title>
        レビュー一覧
    </title>
</header>

<body>
    <h3 style="text-align:center">レビュー一覧</h3>
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

@endsection
