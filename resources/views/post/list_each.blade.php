@extends('layouts.header')
@section('content')
<style>
/* レスポンシブ対応 */
@media (max-width: 768px) {
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

.caption {
    text-align: center;
    margin: 20px 0;
}

.pagination {
    display: flex;
    justify-content: center;
    margin: 20px 0;
}

.pagination .page-item {
    margin: 0 5px;
}

.pagination .page-link {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    color: #007bff;
    text-decoration: none;
}

.pagination .page-link:hover {
    background-color: #f8f9fa;
}

.pagination .page-item.active .page-link {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}

.pagination .page-item.disabled .page-link {
    color: #6c757d;
    pointer-events: none;
}
</style>
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
