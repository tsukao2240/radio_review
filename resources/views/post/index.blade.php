@extends('layouts.header')
@section('content')
<style>
/* レスポンシブ対応 */
@media (max-width: 768px) {
    .container {
        padding: 10px;
    }
    .card {
        margin-bottom: 15px;
    }
    .card-header, .card-body {
        padding: 10px;
    }
    .caption {
        font-size: 18px;
        padding: 10px;
    }
}

.caption {
    text-align: center;
    margin: 20px 0;
}

.program-list {
    display: grid;
    gap: 15px;
}

.card {
    margin-bottom: 0;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    transition: box-shadow 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.card-header {
    background-color: #f8f9fa;
    padding: 15px;
    border-bottom: 1px solid #ddd;
}

.card-header a {
    text-decoration: none;
    color: #007bff;
    font-weight: 600;
    font-size: 16px;
}

.card-header a:hover {
    text-decoration: underline;
    color: #0056b3;
}

.card-body {
    padding: 15px;
    background-color: #fff;
}

.program-cast {
    color: #6c757d;
    font-size: 14px;
    line-height: 1.6;
}

nav {
    display: flex;
    justify-content: center;
    margin: 20px 0;
}

.pagination {
    display: flex;
    flex-direction: row;
    justify-content: center;
    align-items: center;
    margin: 0;
    padding: 0;
    list-style: none;
    gap: 5px;
}

.pagination .page-item {
    margin: 0;
    display: inline-block;
}

.pagination .page-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    color: #007bff;
    text-decoration: none;
    background-color: #fff;
    min-width: 40px;
    height: 40px;
    text-align: center;
    box-sizing: border-box;
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
    border-color: #ddd;
}
</style>

<title>番組一覧</title>

<div class="container">
<!--初期表示-->
@include('includes.search')
@if (isset($results))
<div id="breadcrumb-container"></div>
<h3 class="caption">番組一覧（{{ $results->total() }}件）</h3>
<div class="program-list">
    @foreach ($results as $result)
    <div class="card">
        <div class="card-header">
            <a href="{{ route('program.detail',['station_id' => $result->station_id,'title' => $result->title]) }}">{{ $result->title }}</a>
        </div>
        <div class="card-body">
            <div class="program-cast">{{ $result->cast }}</div>
        </div>
    </div>
    @endforeach
</div>
{{ $results->links('vendor.pagination.custom') }}
<!--検索実行結果を表示する-->
@elseif(isset($programs) && $programs->total() > 0)
<div id="breadcrumb-container"></div>
<h3 class="caption">検索結果（{{ $programs->total() }}件）</h3>
<div class="program-list">
    @foreach ($programs as $item)
    <div class="card">
        <div class="card-header">
            <a href="{{ route('program.detail',['station_id' => $item->station_id,'title' => $item->title]) }}">{{ $item->title }}</a>
        </div>
        <div class="card-body">
            <div class="program-cast">{{ $item->cast }}</div>
        </div>
    </div>
    @endforeach
</div>
{{ $programs->appends(request()->query())->links('vendor.pagination.custom') }}
@else
<div id="breadcrumb-container"></div>
<h3 class="caption">
    検索結果が見つかりませんでした
</h3>
@endif
</div>
@endsection
