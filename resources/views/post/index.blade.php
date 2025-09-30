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

.card {
    margin-bottom: 20px;
    border: 1px solid #ddd;
}

.card-header {
    background-color: #f8f9fa;
    padding: 15px;
    border-bottom: 1px solid #ddd;
}

.card-header a {
    text-decoration: none;
    color: #007bff;
    font-weight: 500;
}

.card-header a:hover {
    text-decoration: underline;
}

.card-body {
    padding: 15px;
    background-color: #fff;
}

.pagination {
    margin-top: 20px;
    justify-content: center;
}
</style>

<title>番組一覧</title>

<div class="container">
<!--初期表示-->
@include('includes.search')
@if (isset($results))
<div id="breadcrumb-container"></div>
<h3 class="caption">番組一覧（{{ $results->total() }}件）</h3>
<div class="card">
    @foreach ($results as $result)
    <div class="card-header"><a
            href="{{ route('program.detail',['station_id' => $result->station_id,'title' => $result->title]) }}">{{ $result->title }}</a>
    </div>
    <div class="card-body">
        {{ $result->cast }}
    </div>
    @endforeach
</div>
{{ $results->links() }}
<!--検索実行結果を表示する-->
@elseif(isset($programs) && $programs->total() > 0)
<div id="breadcrumb-container"></div>
<h3 class="caption">検索結果（{{ $programs->total() }}件）</h3>
<div class="card">
    @foreach ($programs as $item)
    <div class="card-header"><a
            href="{{ route('program.detail',['station_id' => $item->station_id,'title' => $item->title]) }}">{{ $item->title }}</a>
    </div>
    <div class="card-body">
        {{ $item->cast }}
    </div>
    @endforeach
</div>
{{ $programs->appends(request()->query())->links() }}
@else
<div id="breadcrumb-container"></div>
<h3 class="caption">
    検索結果が見つかりませんでした
</h3>
@endif
</div>
@endsection
