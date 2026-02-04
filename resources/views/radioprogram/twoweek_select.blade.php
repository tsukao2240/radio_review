@extends('layouts.header')
@section('content')
@include('includes.search')

<title>タイムフリー録音 - 放送局選択</title>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-podcast"></i> タイムフリー録音</h3>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-map-marker-alt"></i> エリア選択
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('schedule.twoweek') }}" class="row g-3">
                <div class="col-md-6">
                    <select name="area" class="form-select" onchange="this.form.submit()">
                        @foreach($areas as $areaId => $areaName)
                            <option value="{{ $areaId }}" {{ $selectedArea == $areaId ? 'selected' : '' }}>
                                {{ $areaName }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <span class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        エリアを選択すると、その地域の放送局が表示されます
                    </span>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-secondary text-white">
            <i class="fas fa-broadcast-tower"></i> 放送局一覧 ({{ count($stations) }}局)
        </div>
        <div class="card-body">
            @if(count($stations) > 0)
                <div class="row">
                    @foreach($stations as $station)
                        @if(!empty($station['id']) && !empty($station['name']))
                        <div class="col-6 col-md-4 col-lg-3 mb-3">
                            <a href="{{ route('schedule.twoweek.station', ['station_id' => $station['id'], 'area' => $selectedArea]) }}"
                               class="card h-100 text-decoration-none station-card">
                                <div class="card-body text-center">
                                    @if(!empty($station['logo']))
                                        <img src="{{ $station['logo'] }}"
                                             alt="{{ $station['name'] }}"
                                             class="img-fluid mb-2"
                                             style="max-height: 50px;">
                                    @else
                                        <i class="fas fa-broadcast-tower fa-3x text-muted mb-2"></i>
                                    @endif
                                    <h6 class="card-title mb-0">{{ $station['name'] }}</h6>
                                    <small class="text-muted">{{ $station['id'] }}</small>
                                </div>
                            </a>
                        </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    放送局情報を取得できませんでした。
                </div>
            @endif
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header bg-info text-white">
            <i class="fas fa-question-circle"></i> 使い方
        </div>
        <div class="card-body">
            <ol class="mb-0">
                <li>エリアを選択して、録音したい放送局をクリックします</li>
                <li>過去7日間〜未来7日間の番組表が表示されます</li>
                <li><span class="badge bg-success">タイムフリー録音</span> ボタンで過去の番組を録音</li>
                <li><span class="badge bg-warning text-dark">録音予約</span> ボタンで未来の番組を予約</li>
            </ol>
        </div>
    </div>
</div>

<style>
.station-card {
    transition: transform 0.2s, box-shadow 0.2s;
}
.station-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-color: #007bff;
}
.station-card .card-body {
    padding: 1rem;
}
</style>
@endsection
