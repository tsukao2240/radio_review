@extends('layouts.header')
@section('content')
@include('includes.search')
<span>
    {{ Breadcrumbs::render('weekly_schedule', $station_id) }}
</span>
<title>{{ $broadcast_name }}の週間番組表</title>
<div class="schedule-header">
    <h3>週間番組表（{{ $broadcast_name }}）</h3>
</div>
<div class="timetable">
    @for ($i = 0; $i < count($thisWeek); $i++)
    @php
        // この曜日に表示すべき番組があるかチェック
        $currentDate = $thisWeek[$i];
        $nextDate = date('Ymd', strtotime($currentDate . ' +1 day'));
        $hasPrograms = false;
        
        foreach ($entries as $entry) {
            $entryDate = $entry['date'];
            $startTimeInt = (int)str_replace(':', '', $entry['start']);
            
            // 当日の5:00〜23:59の番組
            $isCurrentDayProgram = ($currentDate === $entryDate && $startTimeInt >= 500 && $startTimeInt < 2400);
            
            // 当日の24:00〜28:59の深夜番組
            $isCurrentDayLateNightProgram = ($entryDate === $nextDate && $startTimeInt >= 2400 && $startTimeInt < 2900);
            
            if ($isCurrentDayProgram || $isCurrentDayLateNightProgram) {
                $hasPrograms = true;
                break;
            }
        }
    @endphp
    
    @if ($hasPrograms)
    <div class="tablebox">
        <div class="table">
            <table class="table table-bordered table-responsive">
                <thead>
                    <tr>
                        <th>{{ date('m月d日(D)',strtotime($thisWeek[$i])) }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($entries as $entry)
                    @php
                        // 現在の曜日の日付
                        $currentDate = $thisWeek[$i];
                        
                        // 前日の日付（深夜番組用）
                        $previousDate = isset($thisWeek[$i - 1]) ? $thisWeek[$i - 1] : null;
                        
                        // 番組の日付と開始時刻
                        $entryDate = $entry['date'];
                        $entryStart = $entry['start']; // "HH:MM"形式
                        
                        // 時刻を整数に変換（"24:30" -> 2430）
                        $startTimeInt = (int)str_replace(':', '', $entryStart);
                        
                        // 表示条件
                        // 1. 当日の5:00〜23:59の番組（24:00以降は除外して重複を防ぐ）
                        $isCurrentDayProgram = ($currentDate === $entryDate && $startTimeInt >= 500 && $startTimeInt < 2400);
                        
                        // 2. 当日の24:00〜28:59の深夜番組（dateは翌日だがstartは24時以降）
                        $isCurrentDayLateNightProgram = false;
                        $nextDate = date('Ymd', strtotime($currentDate . ' +1 day'));
                        // 当日の深夜番組: dateは翌日、startは24:00以降
                        $isCurrentDayLateNightProgram = ($entryDate === $nextDate && $startTimeInt >= 2400 && $startTimeInt < 2900);
                        
                        // 表示判定: 当日5:00〜23:59 または 当日24:00〜28:59
                        $shouldDisplay = $isCurrentDayProgram || $isCurrentDayLateNightProgram;
                    @endphp
                    @if ($shouldDisplay)
                    <tr>
                        <td>
                            <a href="{{ url('list/' . $entry['id'] . '/' . $entry['title']) }}?from=weekly&station_id={{ $station_id }}">{{$entry['title']}}</a>
                            @if ($entry['cast'] !== '')
                            <br>
                            {{ $entry['cast'] }}
                            @endif
                            <br>
                            <span class="program-time">{{ $entry['start'] }} - {{ $entry['end'] }}</span>
                            <br>
                            @php
                                $programStartTime = \Carbon\Carbon::createFromFormat('Ymd H:i', $entry['date'] . ' ' . $entry['start']);
                                $programEndTime = \Carbon\Carbon::createFromFormat('Ymd H:i', $entry['date'] . ' ' . $entry['end']);
                                $canRecord = $programEndTime->diffInDays(now()) <= 7;
                            @endphp
                            @if(!$programStartTime->isPast())
                                @if (Auth::check())
                                    <button class="btn btn-sm btn-warning schedule-recording-btn"
                                            data-station-id="{{ $entry['id'] }}"
                                            data-station-name="{{ $station_id }}"
                                            data-title="{{ $entry['title'] }}"
                                            data-cast="{{ $entry['cast'] ?? '' }}"
                                            data-start="{{ $entry['date'] . str_replace(':', '', $entry['start']) }}"
                                            data-end="{{ $entry['date'] . str_replace(':', '', $entry['end']) }}">
                                        録音予約
                                    </button>
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-sm btn-warning">
                                        ログインして録音予約
                                    </a>
                                @endif
                            @elseif($canRecord && $programEndTime->isPast())
                                <div class="recording-controls-wrapper">
                                    <div class="d-flex align-items-center gap-2 mb-2 recording-btn-wrapper">
                                        <select class="form-select form-select-sm area-select" style="max-width: 180px;" data-entry-id="{{ $entry['id'] }}">
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
                                                data-station-id="{{ $entry['id'] }}"
                                                data-station-name="{{ $station_id }}"
                                                data-title="{{ $entry['title'] }}"
                                                data-cast="{{ $entry['cast'] ?? '' }}"
                                                data-date="{{ $entry['date'] }}"
                                                data-start="{{ str_replace(':', '', $entry['start']) }}"
                                                data-end="{{ str_replace(':', '', $entry['end']) }}">
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
                            @endif
                        </td>
                    </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
@endfor
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 録音APIのルートURLを設定（共通モジュールで使用）
    window.recordingStatusUrl = '{{ route("recording.status") }}';
    window.recordingStopUrl = '{{ route("recording.stop") }}';
    window.recordingDownloadUrl = '{{ route("recording.download") }}';

    // 通知許可リクエスト
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    // 録音予約ボタンのイベントリスナー
    document.querySelectorAll('.schedule-recording-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const stationId = this.dataset.stationId;
            const title = this.dataset.title;
            const startTime = this.dataset.start;
            const endTime = this.dataset.end;

            // ボタンを無効化
            this.disabled = true;
            this.textContent = '予約中...';

            const currentButton = this;

            // AJAX リクエストで録音予約
            fetch('{{ route("recording.schedule.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    station_id: stationId,
                    program_title: title,
                    scheduled_start_time: startTime,
                    scheduled_end_time: endTime
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentButton.textContent = '予約完了';
                    currentButton.classList.remove('btn-warning');
                    currentButton.classList.add('btn-secondary');
                    alert('録音予約が完了しました');
                } else {
                    currentButton.disabled = false;
                    currentButton.textContent = '録音予約';
                    alert('録音予約に失敗しました: ' + data.message);
                }
            })
            .catch(error => {
                currentButton.disabled = false;
                currentButton.textContent = '録音予約';
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

            console.log('=== 録音ボタンがクリックされました ===');
            console.log('Station ID:', stationId);
            console.log('Date:', date);

            // エリアIDを取得（同じ録音コントロールラッパー内のselectから）
            const wrapper = this.closest('.recording-controls-wrapper');
            console.log('Wrapper found:', wrapper);

            const areaSelect = wrapper ? wrapper.querySelector('.area-select[data-entry-id="' + stationId + '"]') : null;
            console.log('Area select element:', areaSelect);

            const areaId = areaSelect ? areaSelect.value : '';
            console.log('Selected Area ID:', areaId);

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

            // デバッグ: リクエストボディを確認
            console.log('Request Body:', requestBody);

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
// 録音ファイルをダウンロード（週間番組表用：共通モジュールを使用）
async function downloadRecording(recordingId) {
    const filename = window.getFilenameFromRecordingId(recordingId);
    await window.downloadRecording(recordingId, filename);
}
</script>
@endsection
