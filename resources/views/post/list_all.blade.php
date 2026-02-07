@extends('layouts.header')
@section('content')
<title>
    レビュー一覧
</title>

@include('includes.search')
<span>
    {{ Breadcrumbs::render('review') }}
</span>

<!-- フィルターコントロール -->
<div class="filter-controls">
    <form method="GET" action="{{ route('review.view') }}" class="row g-3">
        <div class="col-md-3">
            <label for="min_rating" class="form-label">最低評価</label>
            <select name="min_rating" id="min_rating" class="form-select">
                <option value="">すべて</option>
                <option value="4" {{ request('min_rating') == 4 ? 'selected' : '' }}>4つ星以上</option>
                <option value="3" {{ request('min_rating') == 3 ? 'selected' : '' }}>3つ星以上</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="tag_id" class="form-label">タグ</label>
            <select name="tag_id" id="tag_id" class="form-select">
                <option value="">すべて</option>
                @foreach($tags as $tag)
                    <option value="{{ $tag->id }}" {{ request('tag_id') == $tag->id ? 'selected' : '' }}>
                        {{ $tag->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="sort_by" class="form-label">並び順</label>
            <select name="sort_by" id="sort_by" class="form-select">
                <option value="created_at" {{ request('sort_by', 'created_at') == 'created_at' ? 'selected' : '' }}>投稿日順</option>
                <option value="likes_count" {{ request('sort_by') == 'likes_count' ? 'selected' : '' }}>いいね順</option>
                <option value="rating" {{ request('sort_by') == 'rating' ? 'selected' : '' }}>評価順</option>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">絞り込み</button>
        </div>
    </form>
</div>

@if (!$posts->isEmpty())
<h3 class="caption">レビュー一覧</h3>
<br>
<div id="reviews">
    <div class="card">
        @foreach ($posts as $post)
        <div class="card-header">
            <a href="{{ route('program.detail',['station_id' => $post->station_id,'title' => $post->program_title, 'from' => 'review']) }}">{{ $post->program_title }}
            </a>
        </div>
        <div class="card-body card_review_body">
            <dt>
                {{ $post->title }}
            </dt>
            <dd>
                {!! nl2br(e($post->body)) !!}
            </dd>
            
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
                投稿者: {{ $post->name }}/(投稿日:{{ date('Y年m月d日',strtotime($post->created_at)) }})
            </span>
            
            <!-- いいね・コメント -->
            <div class="social-actions mt-2">
                <div id="like-button-{{ $post->id }}"></div>
                <div id="comment-section-{{ $post->id }}"></div>
            </div>
        </div>
        @endforeach
    </div>
</div>
{{ $posts->appends(request()->query())->links() }}
@else
<h3 class="caption">レビューがまだありません</h3>
@endif

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
@endsection
