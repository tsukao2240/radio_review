@extends('layouts.header')
@section('content')
<style>
.recording-header {
    text-align: center;
    margin: 30px 0;
}

.recording-header h3 {
    font-size: 28px;
    font-weight: 600;
    color: #333;
}

.recording-list {
    display: grid;
    gap: 20px;
    margin-bottom: 30px;
}

.recording-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.recording-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.recording-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.recording-title {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    flex: 1;
}

.recording-status {
    display: inline-flex;
    align-items: center;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    margin-left: 10px;
}

.status-recording {
    background: #007bff;
    color: white;
}

.status-completed {
    background: #28a745;
    color: white;
}

.status-failed {
    background: #dc3545;
    color: white;
}

.recording-meta {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
    flex-wrap: wrap;
}

.meta-item {
    display: flex;
    align-items: center;
    color: #6c757d;
    font-size: 14px;
}

.meta-item i {
    margin-right: 6px;
}

.recording-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.btn-download {
    background: #007bff;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    transition: background 0.3s ease;
}

.btn-download:hover {
    background: #0056b3;
    color: white;
    text-decoration: none;
}

.btn-download i {
    margin-right: 5px;
}

.btn-delete-recording {
    background: #dc3545;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.3s ease;
}

.btn-delete-recording:hover {
    background: #c82333;
}

.btn-delete-recording i {
    margin-right: 5px;
}

.disk-usage {
    background: #e7f3ff;
    border-left: 4px solid #007bff;
    padding: 15px 20px;
    margin-bottom: 20px;
    border-radius: 6px;
}

.disk-usage strong {
    color: #007bff;
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
    .recording-header h3 {
        font-size: 22px;
    }
    .recording-card {
        padding: 15px;
    }
    .recording-title {
        font-size: 16px;
    }
    .recording-card-header {
        flex-direction: column;
    }
    .recording-status {
        margin-left: 0;
        margin-top: 8px;
    }
    .recording-actions {
        flex-direction: column;
    }
    .btn-download, .btn-delete-recording {
        width: 100%;
        justify-content: center;
    }
}
</style>

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