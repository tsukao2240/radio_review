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

.caption {
    text-align: center;
    margin: 20px 0;
}

#reviews {
    max-width: 1200px;
    margin: 0 auto;
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
<h3 class="caption">{{ Auth::user()->name }}さんが投稿したレビュー</h3>
<br>
@foreach ($posts as $post)
<div id="reviews">
    <div class="card">
        <div class="card-header"><a
                href="{{ route('program.detail',['station_id' => $post->station_id,'title' => $post->program_title]) }}">{{ $post->program_title }}</a>
            <div class="float-right">
                <div class="btn-toolbar">
                    <div class="btn-group">
                        <div class="form-group">
                            <input type="hidden" name="program_id" value="{{ $post->program_id }}">
                        </div>
                        <a href="{{ route('myreview.edit',$post->id) }}"><button class="btn btn-primary">編集</button></a>
                        <form method="POST" action="{{ route('myreview.delete') }}">
                            @csrf
                            <input type="hidden" name="id" value="{{ $post->id }}">
                            <button type="submit" class="btn btn-danger">削除</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body card_review_body">
            <dt>
                {{ $post->title }}
            </dt>
            <dd>
                {{ $post->body }}
            </dd>
        </div>
        <div class="card-footer card_review_footer">
            <span class="review_meta">
                投稿日:{{ date('Y年m月d日',strtotime($post->created_at)) }}
            </span>
        </div>

    </div>
</div>
@endforeach
{{ $posts->links() }}
@else

<h3 class="caption">
    まだ投稿がありません
</h3>
@endif
@endsection
