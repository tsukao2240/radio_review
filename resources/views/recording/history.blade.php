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

<title>録音履歴</title>

<div class="container mt-4">
    <h3 class="text-center mb-4">録音履歴</h3>

    @if(session('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    @if(!empty($recordings) && count($recordings) > 0)
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>放送局</th>
                        <th>番組名</th>
                        <th>録音日時</th>
                        <th>ファイルサイズ</th>
                        <th>状態</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recordings as $recording)
                        <tr>
                            <td>{{ $recording['station_id'] }}</td>
                            <td>{{ $recording['title'] }}</td>
                            <td>{{ \Carbon\Carbon::parse($recording['created_at'])->format('Y/m/d H:i') }}</td>
                            <td>
                                @if(isset($recording['file_size']))
                                    {{ $recording['file_size'] }}
                                @else
                                    --
                                @endif
                            </td>
                            <td>
                                @if($recording['status'] === 'recording')
                                    <span class="badge badge-primary">録音中</span>
                                @elseif($recording['status'] === 'completed')
                                    <span class="badge badge-success">完了</span>
                                @else
                                    <span class="badge badge-secondary">{{ $recording['status'] }}</span>
                                @endif
                            </td>
                            <td>
                                @if($recording['status'] === 'completed' && isset($recording['file_exists']) && $recording['file_exists'])
                                    <a href="{{ route('recording.download', ['recording_id' => $recording['recording_id']]) }}"
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-download"></i> ダウンロード
                                    </a>
                                    <button class="btn btn-sm btn-danger delete-recording-btn"
                                            data-recording-id="{{ $recording['recording_id'] }}"
                                            data-filename="{{ $recording['filename'] }}">
                                        <i class="fas fa-trash"></i> 削除
                                    </button>
                                @elseif($recording['status'] === 'recording')
                                    <span class="text-muted">録音中...</span>
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(isset($diskUsage))
        <div class="alert alert-info mt-3">
            <strong>ディスク使用状況:</strong> {{ $diskUsage['used'] }} / {{ $diskUsage['total'] }}
            ({{ $diskUsage['percentage'] }}% 使用中)
        </div>
        @endif
    @else
        <div class="alert alert-warning text-center">
            録音履歴がありません
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
    document.querySelectorAll('.delete-recording-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const recordingId = this.dataset.recordingId;
            const filename = this.dataset.filename;

            if (!confirm(`${filename} を削除しますか？この操作は取り消せません。`)) {
                return;
            }

            // 削除リクエスト
            fetch('{{ route("recording.delete") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    recording_id: recordingId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('録音ファイルを削除しました');
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