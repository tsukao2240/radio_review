@extends('layouts.app')
@section('content')

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
