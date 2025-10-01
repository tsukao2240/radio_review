@extends('layouts.app')
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
    <div class="card-header">
        {{ $post->title }}
    </div>
    <div class="card-body">
        {{ $post->body }}
    </div>
    <div class="card-footer card_review_footer">
        <span class="review_meta">
            投稿者: {{ $post->name }}/(投稿日:{{ date('Y年m月d日',strtotime($post->created_at)) }})
        </span>
    </div>
</div>
@endforeach
{{ $posts->links() }}
@else
<h3 class="caption">レビューがまだありません</h3>
@endif
@endsection
