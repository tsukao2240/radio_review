@extends('layouts.app')
@section('content')

<title>お気に入り番組</title>

<div class="container mt-4">
    <div class="favorite-header">
        <h3>お気に入り番組</h3>
    </div>

    @if(session('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    @if(!empty($favorites) && count($favorites) > 0)
        <div class="favorite-list">
            @foreach($favorites as $favorite)
                <div class="favorite-card">
                    <div class="favorite-card-header">
                        <div>
                            <div class="favorite-title">{{ $favorite->program_title }}</div>
                            <span class="favorite-station">{{ $favorite->station_id }}</span>
                        </div>
                    </div>
                    <div class="favorite-date">
                        <i class="far fa-clock"></i> 登録日時: {{ $favorite->created_at->format('Y年m月d日 H:i') }}
                    </div>
                    <div class="favorite-actions">
                        <button class="btn-delete delete-favorite-btn"
                                data-favorite-id="{{ $favorite->id }}"
                                data-program-title="{{ $favorite->program_title }}">
                            <i class="fas fa-trash"></i> 削除
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <i class="far fa-heart"></i>
            <p>お気に入り番組がありません</p>
        </div>
    @endif

    <div class="back-button">
        <a href="{{ route('program.schedule') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> 放送中の番組に戻る
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 削除ボタンのイベントリスナー
    document.querySelectorAll('.delete-favorite-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const favoriteId = this.dataset.favoriteId;
            const programTitle = this.dataset.programTitle;

            if (!confirm(`${programTitle} をお気に入りから削除しますか？`)) {
                return;
            }

            // 削除リクエスト
            fetch('{{ route("favorites.destroy") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    id: favoriteId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('お気に入りを削除しました');
                    location.reload();
                } else {
                    alert('削除に失敗しました: ' + data.message);
                }
            })
            .catch(error => {
                alert('エラーが発生しました: ' + error);
            });
        });
    });
});
</script>
@endsection