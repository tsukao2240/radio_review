@extends('layouts.header')
@section('content')
@include('includes.search')

<title>{{ $broadcast_name }} - 2週間番組表</title>

<div class="container-fluid mt-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('schedule.twoweek', ['area' => $selectedArea]) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> 放送局選択に戻る
            </a>
        </div>
        <h4 class="mb-0">
            <i class="fas fa-calendar-alt"></i> {{ $broadcast_name }} - 2週間番組表
        </h4>
        <div></div>
    </div>

    <!-- 日付ナビゲーション -->
    <div class="date-navigation mb-3">
        <div class="d-flex flex-wrap gap-1 justify-content-center">
            @php
                $today = \Carbon\Carbon::now();
                if ($today->hour < 5) {
                    $today = $today->subDay();
                }
                $todayStr = $today->format('Ymd');
            @endphp
            @foreach($dates as $date)
                @php
                    $dateObj = \Carbon\Carbon::createFromFormat('Ymd', $date);
                    $isToday = $date === $todayStr;
                    $isPast = $date < $todayStr;
                    $dayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][$dateObj->dayOfWeek];
                    $isSunday = $dateObj->dayOfWeek === 0;
                    $isSaturday = $dateObj->dayOfWeek === 6;
                @endphp
                <button class="btn btn-sm date-btn {{ $isToday ? 'btn-primary' : ($isPast ? 'btn-outline-secondary' : 'btn-outline-primary') }}"
                        data-date="{{ $date }}"
                        onclick="scrollToDate('{{ $date }}')">
                    <span class="{{ $isSunday ? 'text-danger' : ($isSaturday ? 'text-primary' : '') }}">
                        {{ $dateObj->format('n/j') }}({{ $dayOfWeek }})
                    </span>
                    @if($isToday)
                        <span class="badge bg-warning text-dark">今日</span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    <!-- エリア選択（エリアフリー用） -->
    <div class="card mb-3">
        <div class="card-body py-2">
            <div class="row align-items-center">
                <div class="col-auto">
                    <label class="form-label mb-0"><i class="fas fa-map-marker-alt"></i> 録音エリア:</label>
                </div>
                <div class="col-md-3">
                    <select id="global-area-select" class="form-select form-select-sm">
                        <option value="">現在のエリア（自動判定）</option>
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
                </div>
                <div class="col">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        エリアフリー機能で他地域の番組も録音できます
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- 番組リスト -->
    <div class="program-list">
        @php
            $currentDate = null;
            $now = \Carbon\Carbon::now();
        @endphp

        @foreach($entries as $entry)
            @php
                $entryDate = $entry['date'];
                $showDateHeader = $currentDate !== $entryDate;
                $currentDate = $entryDate;

                $dateObj = \Carbon\Carbon::createFromFormat('Ymd', $entryDate);
                $dayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][$dateObj->dayOfWeek];
                $isSunday = $dateObj->dayOfWeek === 0;
                $isSaturday = $dateObj->dayOfWeek === 6;

                // 番組の開始・終了時刻
                $startTime = \Carbon\Carbon::createFromFormat('YmdHis', $entry['ft']);
                $endTime = \Carbon\Carbon::createFromFormat('YmdHis', $entry['to']);

                $isPast = $endTime->isPast();
                $isFuture = $startTime->isFuture();
                $isNow = $startTime->isPast() && $endTime->isFuture();

                // タイムフリー録音可能かどうか（過去7日以内）
                $canTimefree = $isPast && $endTime->diffInDays($now) <= 7;
            @endphp

            @if($showDateHeader)
                <div class="date-header sticky-top py-2 border-bottom" id="date-{{ $entryDate }}">
                    <h5 class="mb-0 {{ $isSunday ? 'text-danger' : ($isSaturday ? 'text-primary' : '') }}">
                        <i class="fas fa-calendar-day"></i>
                        {{ $dateObj->format('Y年n月j日') }}（{{ $dayOfWeek }}）
                        @if($entryDate === $todayStr)
                            <span class="badge bg-warning text-dark">今日</span>
                        @endif
                    </h5>
                </div>
            @endif

            <div class="card mb-2 program-card {{ $isNow ? 'border-danger' : ($isPast ? 'border-secondary' : '') }}">
                <div class="card-body py-2">
                    <div class="row align-items-center">
                        <div class="col-md-1 text-center">
                            <span class="badge {{ $isNow ? 'bg-danger' : ($isPast ? 'bg-secondary' : 'bg-primary') }}">
                                {{ $entry['start'] }}
                            </span>
                            <br>
                            <small class="text-muted">〜{{ $entry['end'] }}</small>
                        </div>
                        <div class="col-md-7">
                            <a href="{{ url('list/' . $entry['id'] . '/' . urlencode($entry['title'])) }}"
                               class="text-decoration-none">
                                <strong>{{ $entry['title'] }}</strong>
                            </a>
                            @if($entry['cast'])
                                <br><small class="text-muted"><i class="fas fa-user"></i> {{ $entry['cast'] }}</small>
                            @endif
                            @if($entry['desc'])
                                <br><small class="text-muted">{{ $entry['desc'] }}...</small>
                            @endif
                        </div>
                        <div class="col-md-4 text-end">
                            @if($isNow)
                                <span class="badge bg-danger"><i class="fas fa-broadcast-tower"></i> 放送中</span>
                            @elseif($canTimefree)
                                <div class="recording-controls" data-entry-id="{{ $entry['ft'] }}">
                                    <button class="btn btn-sm btn-success timefree-btn"
                                            data-station-id="{{ $entry['id'] }}"
                                            data-station-name="{{ $station_id }}"
                                            data-title="{{ $entry['title'] }}"
                                            data-cast="{{ $entry['cast'] ?? '' }}"
                                            data-ft="{{ $entry['ft'] }}"
                                            data-to="{{ $entry['to'] }}">
                                        <i class="fas fa-download"></i> タイムフリー録音
                                    </button>
                                    <div class="recording-progress mt-2" style="display: none;">
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                                 role="progressbar" style="width: 0%">0%</div>
                                        </div>
                                        <small class="d-block mt-1">
                                            <span class="file-size">0 MB</span> |
                                            <span class="elapsed-time">00:00</span>
                                        </small>
                                        <button class="btn btn-sm btn-danger stop-btn mt-1" style="display: none;">
                                            <i class="fas fa-stop"></i> 停止
                                        </button>
                                        <button class="btn btn-sm btn-primary download-btn mt-1" style="display: none;">
                                            <i class="fas fa-download"></i> ダウンロード
                                        </button>
                                    </div>
                                </div>
                            @elseif($isFuture)
                                @if(Auth::check())
                                    <button class="btn btn-sm btn-warning schedule-btn"
                                            data-station-id="{{ $entry['id'] }}"
                                            data-title="{{ $entry['title'] }}"
                                            data-ft="{{ $entry['ft'] }}"
                                            data-to="{{ $entry['to'] }}">
                                        <i class="fas fa-clock"></i> 録音予約
                                    </button>
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-sign-in-alt"></i> ログインして予約
                                    </a>
                                @endif
                            @elseif($isPast && !$canTimefree)
                                <span class="badge bg-secondary">
                                    <i class="fas fa-clock"></i> タイムフリー期間外
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        @if(count($entries) === 0)
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                番組データがありません。
            </div>
        @endif
    </div>
</div>

<style>
.date-navigation {
    padding: 10px;
    border-radius: 8px;
}
.date-btn {
    min-width: 70px;
    font-size: 0.85rem;
}
.date-header {
    z-index: 100;
    background-color: var(--bs-body-bg);
}
.program-card {
    transition: box-shadow 0.2s;
}
.program-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.program-card.border-danger {
    border-width: 2px;
}
.recording-controls {
    min-width: 200px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const activeRecordings = new Map();
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // 通知許可をリクエスト
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    // 日付にスクロール
    window.scrollToDate = function(date) {
        const element = document.getElementById('date-' + date);
        if (element) {
            element.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };

    // 今日の日付までスクロール
    const todayElement = document.querySelector('.date-header .badge.bg-warning');
    if (todayElement) {
        setTimeout(() => {
            todayElement.closest('.date-header').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 300);
    }

    // タイムフリー録音ボタン
    document.querySelectorAll('.timefree-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const stationId = this.dataset.stationId;
            const stationName = this.dataset.stationName;
            const title = this.dataset.title;
            const cast = this.dataset.cast;
            const ft = this.dataset.ft;
            const to = this.dataset.to;
            const areaId = document.getElementById('global-area-select').value;

            const controls = this.closest('.recording-controls');
            const progressDiv = controls.querySelector('.recording-progress');

            // ボタンを無効化
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 開始中...';

            // リクエスト送信
            fetch('{{ route("recording.timefree.start") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    station_id: stationId,
                    station_name: stationName,
                    title: title,
                    cast: cast,
                    start_time: ft.substring(0, 12),
                    end_time: to.substring(0, 12),
                    area_id: areaId || null
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.style.display = 'none';
                    progressDiv.style.display = 'block';
                    progressDiv.querySelector('.stop-btn').style.display = 'inline-block';

                    // 録音時間を計算
                    const startMinutes = parseInt(ft.substring(8, 10)) * 60 + parseInt(ft.substring(10, 12));
                    const endMinutes = parseInt(to.substring(8, 10)) * 60 + parseInt(to.substring(10, 12));
                    const durationMinutes = endMinutes - startMinutes;

                    // 監視開始
                    startMonitor(data.recording_id, controls, this, data.filename, durationMinutes);
                } else {
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-download"></i> タイムフリー録音';
                    alert('録音開始に失敗しました: ' + data.message);
                }
            })
            .catch(err => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-download"></i> タイムフリー録音';
                alert('エラー: ' + err);
            });
        });
    });

    // 録音予約ボタン
    document.querySelectorAll('.schedule-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const stationId = this.dataset.stationId;
            const title = this.dataset.title;
            const ft = this.dataset.ft;
            const to = this.dataset.to;

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 予約中...';

            fetch('{{ route("recording.schedule.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    station_id: stationId,
                    program_title: title,
                    scheduled_start_time: ft.substring(0, 12),
                    scheduled_end_time: to.substring(0, 12)
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.innerHTML = '<i class="fas fa-check"></i> 予約完了';
                    this.classList.remove('btn-warning');
                    this.classList.add('btn-secondary');
                } else {
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-clock"></i> 録音予約';
                    alert('予約に失敗しました: ' + data.message);
                }
            })
            .catch(err => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-clock"></i> 録音予約';
                alert('エラー: ' + err);
            });
        });
    });

    // 録音監視開始
    function startMonitor(recordingId, controls, btn, filename, durationMinutes) {
        const progressDiv = controls.querySelector('.recording-progress');
        const progressBar = progressDiv.querySelector('.progress-bar');
        const fileSizeSpan = progressDiv.querySelector('.file-size');
        const elapsedSpan = progressDiv.querySelector('.elapsed-time');
        const stopBtn = progressDiv.querySelector('.stop-btn');
        const downloadBtn = progressDiv.querySelector('.download-btn');

        const startTime = Date.now();

        activeRecordings.set(recordingId, { intervalId: null });

        // 停止ボタンのイベント
        stopBtn.onclick = function() {
            if (!confirm('録音を停止しますか？')) return;

            fetch('{{ route("recording.stop") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ recording_id: recordingId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    clearInterval(activeRecordings.get(recordingId).intervalId);
                    activeRecordings.delete(recordingId);
                    progressDiv.style.display = 'none';
                    btn.style.display = 'inline-block';
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-download"></i> タイムフリー録音';
                }
            });
        };

        // ダウンロードボタンのイベント
        downloadBtn.onclick = function() {
            downloadRecording(recordingId, filename);
        };

        // 状態チェック
        function checkStatus() {
            fetch('{{ route("recording.status") }}?' + new URLSearchParams({ recording_id: recordingId }))
            .then(res => res.json())
            .then(data => {
                if (!activeRecordings.has(recordingId)) return;

                if (data.success) {
                    const progress = data.progress_percentage || 0;
                    progressBar.style.width = Math.floor(progress) + '%';
                    progressBar.textContent = Math.floor(progress) + '%';

                    if (data.file_size) {
                        fileSizeSpan.textContent = data.file_size_formatted || formatFileSize(data.file_size);
                    }

                    const elapsed = Math.floor((Date.now() - startTime) / 1000);
                    elapsedSpan.textContent = formatTime(elapsed);

                    // 完了チェック
                    if (data.status === 'completed' || (data.file_exists && !data.is_recording)) {
                        clearInterval(activeRecordings.get(recordingId).intervalId);
                        activeRecordings.delete(recordingId);

                        stopBtn.style.display = 'none';
                        downloadBtn.style.display = 'inline-block';

                        showNotification('録音完了', filename + ' の録音が完了しました');
                    }
                }
            });
        }

        // 即座にチェック開始
        checkStatus();
        activeRecordings.get(recordingId).intervalId = setInterval(checkStatus, 500);
    }

    // ファイルダウンロード
    async function downloadRecording(recordingId, filename) {
        try {
            const response = await fetch('{{ route("recording.download") }}?' + new URLSearchParams({ recording_id: recordingId }));
            if (!response.ok) throw new Error('ダウンロードに失敗しました');

            const blob = await response.blob();

            if ('showSaveFilePicker' in window) {
                try {
                    const fileHandle = await window.showSaveFilePicker({
                        suggestedName: filename || recordingId + '.m4a',
                        types: [{
                            description: '音声ファイル',
                            accept: { 'audio/mp4': ['.m4a'] }
                        }]
                    });
                    const writable = await fileHandle.createWritable();
                    await writable.write(blob);
                    await writable.close();
                    return;
                } catch (e) { }
            }

            // フォールバック
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename || recordingId + '.m4a';
            document.body.appendChild(a);
            a.click();
            URL.revokeObjectURL(url);
            document.body.removeChild(a);
        } catch (err) {
            alert('ダウンロードエラー: ' + err.message);
        }
    }

    // ユーティリティ関数
    function formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
    }

    function formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(0) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    function showNotification(title, body) {
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification(title, { body, icon: '/favicon.ico' });
        }
    }
});
</script>
@endsection
