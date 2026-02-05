@extends('layouts.header')
@section('content')

<title>番組検索</title>

<div class="container">
    @if(request('title'))
        {{ Breadcrumbs::render('search.result', request('title')) }}
    @else
        {{ Breadcrumbs::render('search') }}
    @endif
    
    <div class="page-header">
        <h3>番組検索</h3>
        <p class="text-muted">番組名で検索できます</p>
    </div>

    <!-- 検索フォーム -->
    <div class="search-page-form">
        <form method="get" action="{{ route('program.search') }}">
            <div class="input-group">
                <input type="text" 
                       name="title" 
                       class="form-control form-control-lg" 
                       placeholder="番組名を入力してください" 
                       value="{{ request('title') }}"
                       autofocus>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-search"></i> 検索
                </button>
            </div>
        </form>
    </div>

    <!-- 検索結果 -->
    @if(isset($programs))
        @if($programs->total() > 0)
            <div class="search-results">
                <h4 class="caption">検索結果（{{ $programs->total() }}件）</h4>
                <div class="program-list">
                    @foreach ($programs as $item)
                    <div class="card">
                        <div class="card-header">
                            <a href="{{ route('program.detail', ['station_id' => $item->station_id, 'title' => $item->title, 'from' => 'search', 'keyword' => request('title')]) }}">
                                {{ $item->title }}
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="program-cast">
                                <i class="fas fa-microphone"></i> {{ $item->cast }}
                            </div>
                            <div class="program-station">
                                <i class="fas fa-broadcast-tower"></i> {{ $item->station_id }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                {{ $programs->appends(request()->query())->links('vendor.pagination.custom') }}
            </div>
        @else
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <p>「{{ request('title') }}」に一致する番組が見つかりませんでした</p>
                <p class="text-muted">別のキーワードで検索してみてください</p>
            </div>
        @endif
    @endif
</div>

<style>
.search-page-form {
    max-width: 800px;
    margin: 40px auto;
}

.search-page-form .input-group {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-radius: 8px;
    overflow: hidden;
}

.search-page-form .form-control {
    border: none;
    padding: 16px 24px;
    font-size: 18px;
}

.search-page-form .form-control:focus {
    box-shadow: none;
    outline: none;
}

.search-page-form .btn {
    padding: 16px 32px;
    font-size: 18px;
    font-weight: 600;
    border: none;
}

.search-results {
    margin-top: 40px;
}

.program-station {
    color: #6c757d;
    font-size: 13px;
    margin-top: 5px;
}

.program-station i,
.program-cast i {
    margin-right: 5px;
}
</style>

@endsection
