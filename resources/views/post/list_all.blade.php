@extends('layouts.app')
@section('content')
<title>
    レビュー一覧
</title>

@include('includes.search')
<span>
    {{ Breadcrumbs::render('review') }}
</span>
@if (!$posts->isEmpty())
<h3 class="caption">レビュー一覧</h3>
<br>
<div id="reviews">
    <div class="card">
        @foreach ($posts as $post)
        <div class="card-header">
            <a href="{{ route('program.detail',['station_id' => $post->station_id,'title' => $post->program_title]) }}">{{ $post->program_title }}
            </a>
        </div>
        <div class="card-body card_review_body">
            <dt>
                {{ $post->title }}
            </dt>
            <dd>
                {!! nl2br(e($post->body)) !!}
            </dd>
        </div>
        <div class="card-footer card_review_footer">
            <span class="review_meta">
                投稿者: {{ $post->name }}/(投稿日:{{ date('Y年m月d日',strtotime($post->created_at)) }})
            </span>
        </div>
        @endforeach
    </div>
</div>
{{ $posts->links() }}
@else
<h3 class="caption">レビューがまだありません</h3>
@endif
@endsection
