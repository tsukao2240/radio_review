@extends('layouts.header')
@section('content')
<title>
    レビュー一覧
</title>
@include('includes.search')
<span>
    {{ Breadcrumbs::render('review.list',$station_id,$program_title) }}
</span>
@if (!$posts->isEmpty())
<h3 class="caption">{{ $program_title }}</h3>
@foreach ($posts as $post)
<br>
<div class="card">
    <div class="card-body">
        {{ $post->title }}
    </div>
    <div class="card-body">
        {{ $post->body }}
    </div>
</div>
@endforeach
{{ $posts->links() }}
@else
<h3 class="caption">レビューがまだありません</h3>
@endif
@endsection
