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

<title>録音予約</title>

<div class="container mt-4">
    <h3 class="text-center mb-4">録音予約</h3>

    @if(session('message'))
        <div class="alert alert-success">
            {{ session('message') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if(!empty($schedules) && count($schedules) > 0)
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>放送局</th>
                        <th>番組名</th>
                        <th>予約日時</th>
                        <th>状態</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schedules as $schedule)
                        <tr>
                            <td>{{ $schedule->station_id }}</td>
                            <td>{{ $schedule->program_title }}</td>
                            <td>
                                {{ \Carbon\Carbon::parse($schedule->scheduled_start_time)->format('Y/m/d H:i') }}
                                ~
                                {{ \Carbon\Carbon::parse($schedule->scheduled_end_time)->format('H:i') }}
                            </td>
                            <td>
                                @if($schedule->status === 'pending')
                                    <span class="badge badge-warning">予約中</span>
                                @elseif($schedule->status === 'recording')
                                    <span class="badge badge-primary">録音中</span>
                                @elseif($schedule->status === 'completed')
                                    <span class="badge badge-success">完了</span>
                                @elseif($schedule->status === 'failed')
                                    <span class="badge badge-danger">失敗</span>
                                @elseif($schedule->status === 'cancelled')
                                    <span class="badge badge-secondary">キャンセル</span>
                                @else
                                    <span class="badge badge-secondary">{{ $schedule->status }}</span>
                                @endif
                            </td>
                            <td>
                                @if($schedule->status === 'pending')
                                    <button class="btn btn-sm btn-danger cancel-schedule-btn"
                                            data-schedule-id="{{ $schedule->id }}"
                                            data-program-title="{{ $schedule->program_title }}">
                                        <i class="fas fa-times"></i> キャンセル
                                    </button>
                                @elseif($schedule->status === 'completed' && $schedule->recording_id)
                                    <a href="{{ route('recording.history') }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-history"></i> 録音履歴で確認
                                    </a>
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="alert alert-warning text-center">
            録音予約がありません
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
    // キャンセルボタンのイベントリスナー
    document.querySelectorAll('.cancel-schedule-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const scheduleId = this.dataset.scheduleId;
            const programTitle = this.dataset.programTitle;

            if (!confirm(`「${programTitle}」の録音予約をキャンセルしますか?`)) {
                return;
            }

            // キャンセルリクエスト
            fetch('{{ route("recording.schedule.cancel") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    schedule_id: scheduleId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('録音予約をキャンセルしました');
                    location.reload();
                } else {
                    alert('キャンセルに失敗しました: ' + data.message);
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