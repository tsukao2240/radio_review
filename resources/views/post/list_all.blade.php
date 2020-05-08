@extends('layouts.header')
@section('content')
<title>
    レビュー一覧
</title>

@include('includes.search')
<span>
    {{ Breadcrumbs::render('review') }}
</span>
<h3 style="text-align:center">レビュー一覧</h3>
<br>
@foreach ($posts as $post)
<div class="card">
    <div class="card-header"><a
            href="{{ route('program.detail',['station_id' => $post->station_id,'title' => $post->program_title]) }}">{{ $post->program_title }}</a>
    </div>
    <div class="card-body">
        {{ $post->title }}
    </div>
    <div class="card-body">
        {{ $post->body }}
    </div>
</div>
@endforeach
{{ $posts->links() }}
@endsection
