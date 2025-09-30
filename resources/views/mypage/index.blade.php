@extends('layouts.header')
@section('content')
<style>
.mypage-header {
    text-align: center;
    margin: 30px 0;
    padding: 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.mypage-header h3 {
    font-size: 28px;
    font-weight: 600;
    margin: 0;
}

#reviews {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.review-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    margin-bottom: 20px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.review-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.review-card-header {
    background: #f8f9fa;
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.review-card-header a {
    color: #007bff;
    font-size: 18px;
    font-weight: 600;
    text-decoration: none;
    flex: 1;
}

.review-card-header a:hover {
    color: #0056b3;
    text-decoration: underline;
}

.review-actions {
    display: flex;
    gap: 10px;
}

.btn-edit, .btn-delete-review {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
}

.btn-edit {
    background: #007bff;
    color: white;
}

.btn-edit:hover {
    background: #0056b3;
    color: white;
    text-decoration: none;
}

.btn-delete-review {
    background: #dc3545;
    color: white;
}

.btn-delete-review:hover {
    background: #c82333;
}

.review-card-body {
    padding: 20px;
}

.review-title {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-bottom: 12px;
}

.review-content {
    color: #6c757d;
    line-height: 1.6;
    font-size: 14px;
}

.review-card-footer {
    background: #f8f9fa;
    padding: 15px 20px;
    border-top: 1px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.review-meta {
    color: #6c757d;
    font-size: 13px;
}

.review-meta i {
    margin-right: 5px;
}

.empty-state {
    text-align: center;
    padding: 80px 20px;
}

.empty-state i {
    font-size: 64px;
    color: #ccc;
    margin-bottom: 20px;
}

.empty-state p {
    font-size: 18px;
    color: #6c757d;
    margin-bottom: 20px;
}

.empty-state .btn {
    padding: 12px 24px;
    font-size: 16px;
}

.pagination {
    display: flex;
    justify-content: center;
    margin: 30px 0;
    padding: 0;
    list-style: none;
    gap: 5px;
}

.pagination .page-item {
    margin: 0;
}

.pagination .page-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    color: #007bff;
    text-decoration: none;
    min-width: 40px;
    height: 40px;
    transition: all 0.3s ease;
}

.pagination .page-link:hover {
    background-color: #f8f9fa;
    border-color: #007bff;
}

.pagination .page-item.active .page-link {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}

.pagination .page-item.disabled .page-link {
    color: #6c757d;
    pointer-events: none;
    background-color: #f8f9fa;
}

/* レスポンシブ対応 */
@media (max-width: 768px) {
    #reviews {
        padding: 10px;
    }
    
    .mypage-header h3 {
        font-size: 20px;
    }
    
    .review-card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .review-card-header a {
        font-size: 16px;
    }
    
    .review-actions {
        width: 100%;
        flex-direction: column;
    }
    
    .btn-edit, .btn-delete-review {
        width: 100%;
        text-align: center;
    }
    
    .review-card-body {
        padding: 15px;
    }
    
    .review-card-footer {
        padding: 12px 15px;
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
}
</style>
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
            <a href="{{ route('program.detail',['station_id' => $post->station_id,'title' => $post->program_title]) }}">
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
    <a href="{{ route('program.list') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> レビューを投稿する
    </a>
</div>
@endif
@endsection
