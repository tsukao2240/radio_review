@extends('layouts.header')
@section('content')
<title>
    {{ Auth::user()->name }}さんが投稿したレビュー
</title>
@include('includes.search')
@if (Session::has('message'))
<div id="app">
    <toast-component message="{{ session('message') }}" type="success"></toast-component>
</div>
@endif
@if (!$posts->isEmpty())
<span>
    {{ Breadcrumbs::render('my_review') }}
</span>
<div class="mypage-header">
    <h3>{{ Auth::user()->name }}さんが投稿したレビュー</h3>
</div>

<div id="reviews">
    @foreach ($posts as $post)
    <div class="review-card">
        <div class="review-card-header">
            <a href="{{ route('program.detail',['station_id' => $post->station_id,'title' => $post->program_title, 'from' => 'mypage']) }}">
                {{ $post->program_title }}
            </a>
            <div class="review-actions">
                <a href="{{ route('myreview.edit',$post->id) }}" class="btn-edit">
                    <i class="fas fa-edit"></i> 編集
                </a>
                <form method="POST" action="{{ route('myreview.delete') }}" style="display: inline;">
                    @csrf
                    <input type="hidden" name="id" value="{{ $post->id }}">
                    <button type="submit" class="btn-delete-review" onclick="return confirm('このレビューを削除しますか？')">
                        <i class="fas fa-trash"></i> 削除
                    </button>
                </form>
            </div>
        </div>
        <div class="review-card-body">
            <div class="review-title">{{ $post->title }}</div>
            <div class="review-content">{{ $post->body }}</div>
            
            <!-- 評価表示 -->
            @if($post->rating)
            <div class="mt-2">
                <div id="rating-display-{{ $post->id }}"></div>
            </div>
            @endif
            
            <!-- タグ表示 -->
            @if($post->tags && $post->tags->count() > 0)
            <div class="post-tags mt-2">
                @foreach($post->tags as $tag)
                    <span class="badge">{{ $tag->name }}</span>
                @endforeach
            </div>
            @endif
        </div>
        <div class="review-card-footer">
            <span class="review-meta">
                <i class="far fa-clock"></i>
                投稿日: {{ date('Y年m月d日',strtotime($post->created_at)) }}
            </span>
        </div>
    </div>
    @endforeach
</div>

{{ $posts->links('vendor.pagination.custom') }}
@else

<div class="empty-state">
    <i class="far fa-edit"></i>
    <p>まだ投稿がありません</p>
    <a href="{{ route('program.schedule') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> レビューを投稿する
    </a>
</div>
@endif
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    @foreach ($posts as $post)
        @if($post->rating)
        const ratingContainer{{ $post->id }} = document.getElementById('rating-display-{{ $post->id }}');
        if (ratingContainer{{ $post->id }} && window.StarRating && window.React && window.createRoot) {
            const root{{ $post->id }} = window.createRoot(ratingContainer{{ $post->id }});
            root{{ $post->id }}.render(
                window.React.createElement(window.StarRating, {
                    value: {{ $post->rating }},
                    readOnly: true,
                    size: 18
                })
            );
        }
        @endif
    @endforeach
});
</script>
