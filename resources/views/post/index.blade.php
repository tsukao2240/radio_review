@extends('layouts.header')
@section('content')

<title>番組一覧</title>

<div class="container">
<!--初期表示-->
@include('includes.search')
@if (isset($results))
<div id="breadcrumb-container"></div>
<h3 class="caption">番組一覧（{{ $results->total() }}件）</h3>

<!-- 検索フォームとページネーション設定 -->
<div class="mb-3">
    <form method="get" action="{{ route('post.program') }}" class="row g-3">
        <div class="col-md-6">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="番組名または出演者で検索" value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> 検索
                </button>
                @if(request('search'))
                <a href="{{ route('post.program') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> クリア
                </a>
                @endif
            </div>
        </div>
        <div class="col-md-3">
            <select name="per_page" class="form-select" onchange="this.form.submit()">
                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10件表示</option>
                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25件表示</option>
                <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50件表示</option>
                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100件表示</option>
            </select>
        </div>
    </form>
</div>
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
@if($results->total() > 0)
{{ $results->links('vendor.pagination.custom') }}
@else
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> 
    @if(request('search'))
        「{{ request('search') }}」に一致する番組が見つかりませんでした。別のキーワードで検索してください。
    @else
        表示する番組がありません。
    @endif
</div>
@endif
@endif

@if(isset($programs))
<!-- CrudControllerからの検索結果表示 -->
<div id="breadcrumb-container"></div>
@if($programs->total() > 0)
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
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> 検索結果が見つかりませんでした。
</div>
@endif
@endif
</div>
@endsection
