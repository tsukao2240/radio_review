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

<!-- 番組の平均評価 -->
@if(isset($programRating) && $programRating['average_rating'])
<div class="program-rating">
    <span class="rating-value">★ {{ $programRating['average_rating'] }}</span>
    <span class="rating-count">({{ $programRating['review_count'] }}件のレビュー)</span>
</div>
@endif

@foreach ($posts as $post)
<br>
<div class="card">
    <div class="card-header">
        {{ $post->title }}
    </div>
    <div class="card-body">
        {!! nl2br(e($post->body)) !!}
        
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
    <div class="card-footer card_review_footer">
        <span class="review_meta">
            投稿者: {{ $post->user->name ?? '匿名' }}/(投稿日:{{ date('Y年m月d日',strtotime($post->created_at)) }})
        </span>
        
        <!-- いいね・コメント -->
        <div class="social-actions mt-2">
            <div id="like-button-{{ $post->id }}"></div>
            <div id="comment-section-{{ $post->id }}"></div>
        </div>
    </div>
</div>
@endforeach
{{ $posts->links() }}

<script>
document.addEventListener('DOMContentLoaded', function() {
    const isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
    const currentUserId = {{ auth()->id() ?? 'null' }};
    
    @foreach ($posts as $post)
        // 評価表示
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
        
        // いいねボタン
        const likeContainer{{ $post->id }} = document.getElementById('like-button-{{ $post->id }}');
        if (likeContainer{{ $post->id }} && window.LikeButton && window.React && window.createRoot) {
            const likeRoot{{ $post->id }} = window.createRoot(likeContainer{{ $post->id }});
            likeRoot{{ $post->id }}.render(
                window.React.createElement(window.LikeButton, {
                    postId: {{ $post->id }},
                    initialLikesCount: {{ $post->likes_count ?? 0 }},
                    initialLiked: false,
                    isAuthenticated: isAuthenticated
                })
            );
        }
        
        // コメントセクション
        const commentContainer{{ $post->id }} = document.getElementById('comment-section-{{ $post->id }}');
        if (commentContainer{{ $post->id }} && window.CommentSection && window.React && window.createRoot) {
            const commentRoot{{ $post->id }} = window.createRoot(commentContainer{{ $post->id }});
            commentRoot{{ $post->id }}.render(
                window.React.createElement(window.CommentSection, {
                    postId: {{ $post->id }},
                    initialCommentsCount: {{ $post->comments_count ?? 0 }},
                    isAuthenticated: isAuthenticated,
                    currentUserId: currentUserId
                })
            );
        }
    @endforeach
});
</script>

@else
<h3 class="caption">レビューがまだありません</h3>
@endif
@endsection
