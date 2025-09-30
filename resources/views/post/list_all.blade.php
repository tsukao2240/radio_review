@extends('layouts.header')
@section('content')
<style>
/* レスポンシブ対応 */
@media (max-width: 768px) {
    #reviews {
        padding: 10px;
    }
    .card {
        margin-bottom: 15px;
    }
    .card-header {
        font-size: 16px;
        padding: 10px;
    }
    .card-body {
        padding: 10px;
        font-size: 14px;
    }
    .card-footer {
        padding: 8px;
        font-size: 12px;
    }
    h3.caption {
        font-size: 18px;
        padding: 10px;
    }
}
</style>
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
