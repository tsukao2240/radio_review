@extends('layouts.header')
@section('content')

<span>
    {{ Breadcrumbs::render('favorites') }}
</span>

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
                            <a href="{{ route('program.detail', ['station_id' => $favorite->station_id, 'title' => $favorite->program_title, 'from' => 'favorites', 'broadcast_day' => $favorite->broadcast_day]) }}" style="text-decoration: none; color: inherit;">
                                <div class="favorite-title" style="cursor: pointer; transition: color 0.2s;" onmouseover="this.style.color='#007bff'" onmouseout="this.style.color='inherit'">{{ $favorite->program_title }}</div>
                            </a>
                            @if($favorite->broadcast_day !== null)
                                @php $days = ['月','火','水','木','金','土','日']; @endphp
                                <span class="badge bg-secondary">{{ $days[$favorite->broadcast_day] }}曜日</span>
                            @endif
                            <span class="favorite-station">{{ $favorite->station_id }}</span>
                        </div>
                    </div>
                    <div class="favorite-date">
                        <i class="far fa-clock"></i> 登録日時: {{ $favorite->created_at->format('Y年m月d日 H:i') }}
                    </div>

                    @if($favorite->latest_broadcast)
                        @php
                            $broadcast = $favorite->latest_broadcast;
                            $programEndTime = \Carbon\Carbon::createFromFormat('Ymd H:i', $broadcast['date'] . ' ' . $broadcast['end']);
                        @endphp
                        <div class="favorite-broadcast-info">
                            <small class="text-muted">
                                直近放送: {{ \Carbon\Carbon::createFromFormat('Ymd', $broadcast['date'])->format('m月d日(D)') }}
                                {{ $broadcast['start'] }} - {{ $broadcast['end'] }}
                            </small>
                        </div>
                        <div class="recording-controls-wrapper" style="margin-top: 10px;">
                            <div class="d-flex align-items-center gap-2 mb-2 recording-btn-wrapper">
                                <select class="form-select form-select-sm area-select" style="max-width: 180px;" data-entry-id="{{ $broadcast['id'] }}">
                                    <option value="">エリア内</option>
                                    <optgroup label="北海道・東北">
                                        <option value="JP1">北海道</option>
                                        <option value="JP2">青森県</option>
                                        <option value="JP3">岩手県</option>
                                        <option value="JP4">宮城県</option>
                                        <option value="JP5">秋田県</option>
                                        <option value="JP6">山形県</option>
                                        <option value="JP7">福島県</option>
                                    </optgroup>
                                    <optgroup label="関東">
                                        <option value="JP8">茨城県</option>
                                        <option value="JP9">栃木県</option>
                                        <option value="JP10">群馬県</option>
                                        <option value="JP11">埼玉県</option>
                                        <option value="JP12">千葉県</option>
                                        <option value="JP13">東京都</option>
                                        <option value="JP14">神奈川県</option>
                                    </optgroup>
                                    <optgroup label="中部">
                                        <option value="JP15">新潟県</option>
                                        <option value="JP16">富山県</option>
                                        <option value="JP17">石川県</option>
                                        <option value="JP18">福井県</option>
                                        <option value="JP19">山梨県</option>
                                        <option value="JP20">長野県</option>
                                        <option value="JP21">岐阜県</option>
                                        <option value="JP22">静岡県</option>
                                        <option value="JP23">愛知県</option>
                                        <option value="JP24">三重県</option>
                                    </optgroup>
                                    <optgroup label="近畿">
                                        <option value="JP25">滋賀県</option>
                                        <option value="JP26">京都府</option>
                                        <option value="JP27">大阪府</option>
                                        <option value="JP28">兵庫県</option>
                                        <option value="JP29">奈良県</option>
                                        <option value="JP30">和歌山県</option>
                                    </optgroup>
                                    <optgroup label="中国・四国">
                                        <option value="JP31">鳥取県</option>
                                        <option value="JP32">島根県</option>
                                        <option value="JP33">岡山県</option>
                                        <option value="JP34">広島県</option>
                                        <option value="JP35">山口県</option>
                                        <option value="JP36">徳島県</option>
                                        <option value="JP37">香川県</option>
                                        <option value="JP38">愛媛県</option>
                                        <option value="JP39">高知県</option>
                                    </optgroup>
                                    <optgroup label="九州・沖縄">
                                        <option value="JP40">福岡県</option>
                                        <option value="JP41">佐賀県</option>
                                        <option value="JP42">長崎県</option>
                                        <option value="JP43">熊本県</option>
                                        <option value="JP44">大分県</option>
                                        <option value="JP45">宮崎県</option>
                                        <option value="JP46">鹿児島県</option>
                                        <option value="JP47">沖縄県</option>
                                    </optgroup>
                                </select>
                                <button class="btn btn-sm btn-success recording-btn"
                                        data-station-id="{{ $broadcast['id'] }}"
                                        data-station-name="{{ $favorite->station_id }}"
                                        data-title="{{ $broadcast['title'] }}"
                                        data-cast="{{ $broadcast['cast'] ?? '' }}"
                                        data-date="{{ $broadcast['date'] }}"
                                        data-start="{{ str_replace(':', '', $broadcast['start']) }}"
                                        data-end="{{ str_replace(':', '', $broadcast['end']) }}">
                                    タイムフリー録音
                                </button>
                            </div>
                            <div class="recording-status" style="display:none; margin-top:5px;">
                                <div class="progress" style="height: 20px; margin-bottom: 5px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                                         role="progressbar"
                                         style="width: 0%"
                                         aria-valuenow="0"
                                         aria-valuemin="0"
                                         aria-valuemax="100">0%</div>
                                </div>
                                <small class="recording-info" style="display: block; margin-bottom: 3px;">
                                    サイズ: <span class="file-size">0 MB</span> |
                                    時間: <span class="elapsed-time">00:00</span> / <span class="total-time">--:--</span>
                                </small>
                                <button class="btn btn-sm btn-danger stop-recording-btn" style="width: 100%;">
                                    録音停止
                                </button>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info" style="margin-top: 10px; font-size: 0.9em;">
                            <i class="fas fa-info-circle"></i> タイムフリー期間内の放送がありません
                        </div>
                    @endif

                    <div class="favorite-actions" style="margin-top: 10px;">
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
// 録音APIのルートURLを設定（共通モジュールで使用）
window.recordingStatusUrl = '{{ route("recording.status") }}';
window.recordingStopUrl = '{{ route("recording.stop") }}';
window.recordingDownloadUrl = '{{ route("recording.download") }}';

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

    // タイムフリー録音ボタンのイベントリスナー
    document.querySelectorAll('.recording-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const stationId = this.dataset.stationId;
            const stationName = this.dataset.stationName;
            const title = this.dataset.title;
            const cast = this.dataset.cast;
            const date = this.dataset.date;
            const startTime = this.dataset.start;
            const endTime = this.dataset.end;

            // エリアIDを取得（同じ録音コントロールラッパー内のselectから）
            const wrapper = this.closest('.recording-controls-wrapper');
            const areaSelect = wrapper ? wrapper.querySelector('.area-select[data-entry-id="' + stationId + '"]') : null;
            const areaId = areaSelect ? areaSelect.value : '';

            // 録音時間を計算（分単位）
            const startMinutes = parseInt(startTime.substring(0, 2)) * 60 + parseInt(startTime.substring(2, 4));
            const endMinutes = parseInt(endTime.substring(0, 2)) * 60 + parseInt(endTime.substring(2, 4));
            const durationMinutes = endMinutes - startMinutes;

            // ボタンを無効化
            this.disabled = true;
            this.textContent = '録音開始中...';

            const currentButton = this;

            // リクエストボディを構築
            const requestBody = {
                station_id: stationId,
                station_name: stationName,
                title: title,
                cast: cast,
                start_time: date + startTime,
                end_time: date + endTime
            };

            // エリアIDが指定されている場合のみ追加
            if (areaId) {
                requestBody.area_id = areaId;
            }

            // AJAX リクエストでタイムフリー録音開始
            fetch('{{ route("recording.timefree.start") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(requestBody)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // ボタンのラッパーを非表示にして、進行状況表示を表示
                    const btnWrapper = currentButton.closest('.recording-btn-wrapper');
                    if (btnWrapper) {
                        btnWrapper.style.display = 'none';
                    }

                    const statusDiv = currentButton.closest('.recording-controls-wrapper').querySelector('.recording-status');

                    if (statusDiv) {
                        statusDiv.style.display = 'block';

                        // 停止ボタンのイベントリスナーを設定
                        const stopBtn = statusDiv.querySelector('.stop-recording-btn');
                        if (stopBtn) {
                            stopBtn.onclick = function() {
                                window.stopRecording(data.recording_id, currentButton, statusDiv);
                            };
                        }

                        // 共通モジュールの録音監視を開始
                        window.startRecordingMonitor(data.recording_id, currentButton, data.filename, statusDiv, durationMinutes);
                    }
                } else {
                    // エラー時：ボタンラッパーを再表示
                    const btnWrapper = currentButton.closest('.recording-btn-wrapper');
                    if (btnWrapper) {
                        btnWrapper.style.display = 'flex';
                    }
                    currentButton.disabled = false;
                    currentButton.textContent = 'タイムフリー録音';
                    alert('録音開始に失敗しました: ' + data.message);
                }
            })
            .catch(error => {
                // エラー時：ボタンラッパーを再表示
                const btnWrapper = currentButton.closest('.recording-btn-wrapper');
                if (btnWrapper) {
                    btnWrapper.style.display = 'flex';
                }
                currentButton.disabled = false;
                currentButton.textContent = 'タイムフリー録音';
                alert('エラーが発生しました: ' + error);
            });
        });
    });

});
</script>

<script>
// 録音ファイルをダウンロード（お気に入り番組用：共通モジュールを使用）
async function downloadRecording(recordingId) {
    const filename = window.getFilenameFromRecordingId(recordingId);
    await window.downloadRecording(recordingId, filename);
}
</script>
@endsection