@extends('layouts.header')
@section('content')

<title>番組一覧</title>

<!--初期表示-->
@include('includes.search')
@if (isset($results))
<span>
    {{ Breadcrumbs::render('result') }}
</span>
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
<span>
    {{ Breadcrumbs::render('result') }}
</span>
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
<span>
    {{ Breadcrumbs::render('result') }}
</span>
<h3 class="caption">
    検索結果が見つかりませんでした
</h3>
@endif
@endsection
