@extends('layouts.header')
@section('content')
<title>おすすめ番組</title>

@include('includes.search')

<div class="container mt-4">
    <h2 class="mb-4">
        <i class="fas fa-star text-warning"></i> おすすめ番組
    </h2>

    <div class="row">
        <!-- パーソナライズ推薦 -->
        <div class="col-lg-6 mb-4">
            <div class="recommendations-section">
                <h2>
                    <i class="fas fa-user-circle"></i> あなたへのおすすめ
                </h2>
                
                @if($recommendations && $recommendations->count() > 0)
                    @foreach($recommendations as $program)
                        <div class="recommendation-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h5 class="program-title">{{ $program->title }}</h5>
                                    <p class="program-station text-muted">
                                        <i class="fas fa-broadcast-tower"></i> {{ $program->station_id }}
                                    </p>
                                    @if(isset($program->cast) && $program->cast)
                                        <p class="program-cast small">
                                            <i class="fas fa-microphone"></i> {{ $program->cast }}
                                        </p>
                                    @endif
                                    @if(isset($program->avg_rating) && $program->avg_rating)
                                        <div class="mt-2">
                                            <span class="badge bg-warning text-dark">
                                                ★ {{ number_format($program->avg_rating, 1) }}
                                            </span>
                                            @if(isset($program->reviews_count))
                                                <small class="text-muted">({{ $program->reviews_count }}件)</small>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <a href="{{ route('program.detail', ['station_id' => $program->station_id, 'title' => $program->title]) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        詳細 <i class="fas fa-chevron-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                        <p class="text-muted">
                            お気に入り登録や高評価レビューをすると<br>
                            あなたにぴったりの番組をおすすめします！
                        </p>
                        <a href="{{ route('favorites.index') }}" class="btn btn-primary mt-2">
                            <i class="fas fa-heart"></i> お気に入りを登録する
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- トレンド番組 -->
        <div class="col-lg-6 mb-4">
            <div class="recommendations-section">
                <h2>
                    <i class="fas fa-fire text-danger"></i> 話題の番組
                </h2>
                
                @if($trending && $trending->count() > 0)
                    @foreach($trending as $program)
                        <div class="recommendation-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h5 class="program-title">{{ $program->title }}</h5>
                                    <p class="program-station text-muted">
                                        <i class="fas fa-broadcast-tower"></i> {{ $program->station_id }}
                                    </p>
                                    @if(isset($program->cast) && $program->cast)
                                        <p class="program-cast small">
                                            <i class="fas fa-microphone"></i> {{ $program->cast }}
                                        </p>
                                    @endif
                                    @if(isset($program->avg_rating) && $program->avg_rating)
                                        <div class="mt-2">
                                            <span class="badge bg-danger text-white">
                                                🔥 ★ {{ number_format($program->avg_rating, 1) }}
                                            </span>
                                            @if(isset($program->recent_reviews_count))
                                                <small class="text-muted">(最近{{ $program->recent_reviews_count }}件)</small>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <a href="{{ route('program.detail', ['station_id' => $program->station_id, 'title' => $program->title]) }}" 
                                       class="btn btn-sm btn-outline-danger">
                                        詳細 <i class="fas fa-chevron-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                        <p class="text-muted">現在、話題の番組はありません</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- リフレッシュボタン -->
    <div class="text-center mt-4">
        <form method="POST" action="{{ route('api.recommendations.refresh') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">
                <i class="fas fa-sync-alt"></i> おすすめを更新
            </button>
        </form>
    </div>
</div>

@endsection
