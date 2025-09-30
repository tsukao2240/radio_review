@extends('layouts.header')
@section('content')
<style>
/* レスポンシブ対応 */
@media (max-width: 768px) {
    .container {
        padding: 10px;
    }
    .table {
        font-size: 12px;
    }
    .table td, .table th {
        padding: 8px 5px;
    }
    .btn-sm {
        font-size: 11px;
        padding: 4px 8px;
    }
    h3 {
        font-size: 18px;
    }
}
</style>

<title>お気に入り番組</title>

<div class="container mt-4">
    <h3 class="text-center mb-4">お気に入り番組</h3>

    @if(session('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    @if(!empty($favorites) && count($favorites) > 0)
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>放送局</th>
                        <th>番組名</th>
                        <th>登録日時</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($favorites as $favorite)
                        <tr>
                            <td>{{ $favorite->station_id }}</td>
                            <td>{{ $favorite->program_title }}</td>
                            <td>{{ \Carbon\Carbon::parse($favorite->created_at)->format('Y/m/d H:i') }}</td>
                            <td>
                                <button class="btn btn-sm btn-danger delete-favorite-btn"
                                        data-favorite-id="{{ $favorite->id }}"
                                        data-program-title="{{ $favorite->program_title }}">
                                    <i class="fas fa-trash"></i> 削除
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="alert alert-warning text-center">
            お気に入り番組がありません
        </div>
    @endif

    <div class="text-center mt-4">
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