@extends('layouts.header')
@section('content')
<style>
.favorite-header {
    text-align: center;
    margin: 30px 0;
}

.favorite-header h3 {
    font-size: 28px;
    font-weight: 600;
    color: #333;
}

.favorite-list {
    display: grid;
    gap: 20px;
    margin-bottom: 30px;
}

.favorite-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.favorite-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.favorite-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}

.favorite-title {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
    flex: 1;
}

.favorite-station {
    display: inline-block;
    background: #007bff;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    margin-left: 10px;
}

.favorite-date {
    color: #6c757d;
    font-size: 14px;
    margin-bottom: 12px;
}

.favorite-actions {
    display: flex;
    gap: 10px;
}

.btn-delete {
    background: #dc3545;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.3s ease;
}

.btn-delete:hover {
    background: #c82333;
}

.btn-delete i {
    margin-right: 5px;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state i {
    font-size: 64px;
    color: #ccc;
    margin-bottom: 20px;
}

.empty-state p {
    font-size: 18px;
    color: #6c757d;
}

.back-button {
    text-align: center;
    margin-top: 30px;
}

/* レスポンシブ対応 */
@media (max-width: 768px) {
    .container {
        padding: 10px;
    }
    .favorite-header h3 {
        font-size: 22px;
    }
    .favorite-card {
        padding: 15px;
    }
    .favorite-title {
        font-size: 16px;
    }
    .favorite-card-header {
        flex-direction: column;
    }
    .favorite-station {
        margin-left: 0;
        margin-top: 8px;
    }
}
</style>

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