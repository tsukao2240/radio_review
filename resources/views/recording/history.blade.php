@extends('layouts.header')
@section('content')

<title>録音履歴</title>

<div class="container mt-4">
    <div class="recording-header">
        <h3>録音履歴</h3>
    </div>

    @if(session('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    @if(isset($diskUsage))
    <div class="disk-usage">
        <strong>ディスク使用状況:</strong> {{ $diskUsage['used'] }} / {{ $diskUsage['total'] }}
        ({{ $diskUsage['percentage'] }}% 使用中)
    </div>
    @endif

    @if(!empty($recordings) && count($recordings) > 0)
        <div class="recording-list">
            @foreach($recordings as $recording)
                <div class="recording-card">
                    <div class="recording-card-header">
                        <div class="recording-title">{{ $recording['title'] }}</div>
                        <div>
                            @if($recording['status'] === 'recording')
                                <span class="recording-status status-recording">
                                    <i class="fas fa-circle"></i> 録音中
                                </span>
                            @elseif($recording['status'] === 'completed')
                                <span class="recording-status status-completed">
                                    <i class="fas fa-check-circle"></i> 完了
                                </span>
                            @else
                                <span class="recording-status status-failed">{{ $recording['status'] }}</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="recording-meta">
                        <div class="meta-item">
                            <i class="fas fa-broadcast-tower"></i>
                            {{ $recording['station_id'] }}
                        </div>
                        <div class="meta-item">
                            <i class="far fa-clock"></i>
                            {{ $recording['created_at']->format('Y年m月d日 H:i') }}
                        </div>
                        @if(isset($recording['file_size']))
                        <div class="meta-item">
                            <i class="fas fa-file"></i>
                            {{ $recording['file_size'] }}
                        </div>
                        @endif
                    </div>

                    @if($recording['status'] === 'completed' && isset($recording['file_exists']) && $recording['file_exists'])
                        <div class="recording-actions">
                            <a href="{{ route('recording.download', ['recording_id' => $recording['recording_id']]) }}"
                               class="btn-download">
                                <i class="fas fa-download"></i> ダウンロード
                            </a>
                            <button class="btn-delete-recording delete-recording-btn"
                                    data-recording-id="{{ $recording['recording_id'] }}"
                                    data-filename="{{ $recording['filename'] }}">
                                <i class="fas fa-trash"></i> 削除
                            </button>
                        </div>
                    @elseif($recording['status'] === 'recording')
                        <div class="text-muted" style="margin-top: 10px;">
                            <i class="fas fa-spinner fa-spin"></i> 録音中...
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-microphone-slash"></i>
            <p>録音履歴がありません</p>
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