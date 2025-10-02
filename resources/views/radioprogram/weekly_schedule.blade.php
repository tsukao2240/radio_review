@extends('layouts.header')
@section('content')
@include('includes.search')
<span>
    {{ Breadcrumbs::render('weekly_schedule') }}
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
                            <a href="{{ url('list/' . $entry['id'] . '/' . $entry['title'])}}">{{$entry['title']}}</a>
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
                                            data-title="{{ $entry['title'] }}"
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
                                <button class="btn btn-sm btn-success recording-btn"
                                        data-station-id="{{ $entry['id'] }}"
                                        data-title="{{ $entry['title'] }}"
                                        data-date="{{ $entry['date'] }}"
                                        data-start="{{ str_replace(':', '', $entry['start']) }}"
                                        data-end="{{ str_replace(':', '', $entry['end']) }}">
                                    タイムフリー録音
                                </button>
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
    let activeRecordings = new Map();

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
            const title = this.dataset.title;
            const date = this.dataset.date;
            const startTime = this.dataset.start;
            const endTime = this.dataset.end;

            // 録音時間を計算（分単位）
            const startMinutes = parseInt(startTime.substring(0, 2)) * 60 + parseInt(startTime.substring(2, 4));
            const endMinutes = parseInt(endTime.substring(0, 2)) * 60 + parseInt(endTime.substring(2, 4));
            const durationMinutes = endMinutes - startMinutes;

            // ボタンを無効化
            this.disabled = true;
            this.textContent = '録音開始中...';

            const currentButton = this;

            // AJAX リクエストでタイムフリー録音開始
            fetch('{{ route("recording.timefree.start") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    station_id: stationId,
                    title: title,
                    start_time: date + startTime,
                    end_time: date + endTime
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // ボタンを非表示にして、進行状況表示を表示
                    currentButton.style.display = 'none';
                    const statusDiv = currentButton.nextElementSibling;

                    if (statusDiv && statusDiv.classList.contains('recording-status')) {
                        statusDiv.style.display = 'block';

                        // 停止ボタンのイベントリスナーを設定
                        const stopBtn = statusDiv.querySelector('.stop-recording-btn');
                        if (stopBtn) {
                            stopBtn.onclick = function() {
                                stopRecording(data.recording_id, currentButton, statusDiv);
                            };
                        }

                        // 録音状態を監視開始
                        startRecordingMonitor(data.recording_id, currentButton, data.filename, statusDiv, durationMinutes);
                    }
                } else {
                    currentButton.disabled = false;
                    currentButton.textContent = 'タイムフリー録音';
                    alert('録音開始に失敗しました: ' + data.message);
                }
            })
            .catch(error => {
                currentButton.disabled = false;
                currentButton.textContent = 'タイムフリー録音';
                alert('エラーが発生しました: ' + error);
            });
        });
    });

    // 録音状態監視開始
    function startRecordingMonitor(recordingId, button, filename, statusDiv, durationMinutes) {
        const startTime = Date.now();
        const totalSeconds = durationMinutes * 60;

        activeRecordings.set(recordingId, {
            button: button,
            statusDiv: statusDiv,
            filename: filename,
            startTime: startTime,
            totalSeconds: totalSeconds
        });

        // 総録音時間を表示
        const totalTimeSpan = statusDiv.querySelector('.total-time');
        if (totalTimeSpan) {
            totalTimeSpan.textContent = formatTime(totalSeconds);
        }

        // 即座に最初のチェックを実行
        checkRecordingStatus(recordingId, button, filename, statusDiv, null, startTime, totalSeconds);

        // 500ms間隔で状態をチェック（高速ダウンロード対応）
        const intervalId = setInterval(() => {
            checkRecordingStatus(recordingId, button, filename, statusDiv, intervalId, startTime, totalSeconds);
        }, 500);

        activeRecordings.get(recordingId).intervalId = intervalId;
    }

    // 録音状態をチェック
    function checkRecordingStatus(recordingId, button, filename, statusDiv, intervalId, startTime, totalSeconds) {
        // 既に完了済みの録音はチェックしない
        if (!activeRecordings.has(recordingId)) {
            return;
        }

        fetch('{{ route("recording.status") }}?' + new URLSearchParams({
            recording_id: recordingId
        }))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 既に完了済みならスキップ
                if (!activeRecordings.has(recordingId)) {
                    return;
                }

                // 経過時間を表示（整数に変換）
                const elapsedSeconds = Math.floor(data.elapsed_seconds || Math.floor((Date.now() - startTime) / 1000));
                const elapsedTimeSpan = statusDiv.querySelector('.elapsed-time');
                if (elapsedTimeSpan) {
                    elapsedTimeSpan.textContent = formatTime(elapsedSeconds);
                }

                // 進捗率を表示（サーバーからの値を優先）
                const progress = data.progress_percentage !== undefined ? data.progress_percentage : 0;
                const progressBar = statusDiv.querySelector('.progress-bar');
                if (progressBar) {
                    const progressInt = Math.floor(progress);
                    progressBar.style.width = progressInt + '%';
                    progressBar.textContent = progressInt + '%';
                    progressBar.setAttribute('aria-valuenow', progressInt);
                }

                // ファイルサイズを表示
                if (data.file_size !== undefined && data.file_size > 0) {
                    const fileSizeSpan = statusDiv.querySelector('.file-size');
                    if (fileSizeSpan) {
                        fileSizeSpan.textContent = data.file_size_formatted || formatFileSize(data.file_size);
                    }
                }

                // 録音完了判定
                if (data.status === 'completed' || (data.file_exists && !data.is_recording)) {
                    // 録音情報を削除（重複実行防止）
                    const recording = activeRecordings.get(recordingId);
                    activeRecordings.delete(recordingId);

                    // タイマーを停止
                    if (recording && recording.intervalId) {
                        clearInterval(recording.intervalId);
                    }

                    // 進行状況表示を非表示
                    statusDiv.style.display = 'none';

                    // ボタンを再表示してダウンロードボタンに変更
                    button.style.display = 'block';
                    button.textContent = 'ダウンロード';
                    button.classList.remove('btn-success', 'btn-warning');
                    button.classList.add('btn-primary');
                    button.disabled = false;

                    // ボタンをダウンロードボタンに変更
                    button.onclick = function() {
                        downloadRecording(recordingId);
                    };

                    // ブラウザ通知を表示
                    showBrowserNotification(filename);
                    alert('録音が完了しました: ' + filename);
                }
            }
        })
        .catch(error => {
            // エラー表示
            if (activeRecordings.has(recordingId)) {
                const recording = activeRecordings.get(recordingId);
                if (recording && recording.statusDiv) {
                    const recordingInfo = recording.statusDiv.querySelector('.recording-info');
                    if (recordingInfo) {
                        recordingInfo.innerHTML = '<span style="color: red;">エラー: 状態取得失敗</span>';
                    }
                }
            }
        });
    }

    // 録音を停止
    function stopRecording(recordingId, button, statusDiv) {
        if (!confirm('録音を停止しますか？')) {
            return;
        }

        fetch('{{ route("recording.stop") }}', {
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
                // 監視を停止
                const recording = activeRecordings.get(recordingId);
                if (recording && recording.intervalId) {
                    clearInterval(recording.intervalId);
                }
                activeRecordings.delete(recordingId);

                // 進行状況表示を非表示
                statusDiv.style.display = 'none';

                // ボタンを元に戻す
                button.style.display = 'block';
                button.textContent = 'タイムフリー録音';
                button.classList.remove('btn-warning', 'btn-primary');
                button.classList.add('btn-success');
                button.disabled = false;

                alert('録音を停止しました');
            } else {
                alert('録音停止に失敗しました: ' + data.message);
            }
        })
        .catch(error => {
            alert('エラーが発生しました: ' + error);
        });
    }

    // 時間をフォーマット（秒 -> MM:SS）
    function formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
    }

    // ファイルサイズをフォーマット
    function formatFileSize(bytes) {
        if (bytes < 1024) {
            return bytes + ' B';
        } else if (bytes < 1024 * 1024) {
            return (bytes / 1024).toFixed(0) + ' KB';
        } else {
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        }
    }
});

// 録音ファイルをダウンロード（週間番組表用）
async function downloadRecording(recordingId) {
    try {
        // ダウンロードURLを構築
        const downloadUrl = '{{ route("recording.download") }}?' + new URLSearchParams({
            recording_id: recordingId
        });

        // ファイルを取得
        const response = await fetch(downloadUrl);
        if (!response.ok) {
            throw new Error('ダウンロードに失敗しました');
        }

        const blob = await response.blob();
        const filename = getFilenameFromRecordingId(recordingId);

        // File System Access APIをサポートしているかチェック
        if ('showSaveFilePicker' in window) {
            try {
                // カスタム保存先を指定（D:\ミュージック\radio）
                const fileHandle = await window.showSaveFilePicker({
                    suggestedName: filename,
                    startIn: 'music', // 音楽フォルダから開始
                    types: [{
                        description: '音声ファイル',
                        accept: {
                            'audio/mp4': ['.m4a'],
                            'audio/mpeg': ['.mp3']
                        }
                    }]
                });

                const writable = await fileHandle.createWritable();
                await writable.write(blob);
                await writable.close();

                alert('ファイルが正常に保存されました！');
                return;
            } catch (e) {
                console.log('Save picker cancelled, falling back to default download');
            }
        }

        // フォールバック: 通常のダウンロード
        downloadWithCustomName(blob, filename);

    } catch (error) {
        console.error('ダウンロードエラー:', error);
        alert('ダウンロードに失敗しました: ' + error.message);
    }
}

// カスタム名でダウンロード（フォールバック用）
function downloadWithCustomName(blob, filename) {
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.style.display = 'none';
    a.href = url;
    a.download = filename;

    document.body.appendChild(a);
    a.click();

    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);

    showDownloadLocationInfo();
}

// 録音IDからファイル名を取得
function getFilenameFromRecordingId(recordingId) {
    const parts = recordingId.split('_');
    if (parts.length >= 3) {
        const station = parts[0];
        const datetime = parts[1];
        const timestamp = parts[2];

        const year = datetime.substring(0, 4);
        const month = datetime.substring(4, 6);
        const day = datetime.substring(6, 8);
        const hour = datetime.substring(8, 10);
        const minute = datetime.substring(10, 12);

        return `${station}_${year}${month}${day}_${hour}${minute}.m4a`;
    }
    return recordingId + '.m4a';
}

// ダウンロード場所の案内を表示
function showDownloadLocationInfo() {
    const info = document.createElement('div');
    info.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #007bff;
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 10001;
        max-width: 350px;
    `;

    info.innerHTML = `
        <div style="font-weight: bold; margin-bottom: 8px;">💾 ダウンロード完了</div>
        <div style="font-size: 14px; line-height: 1.4;">
            推奨保存先: <strong>D:\\ミュージック\\radio</strong><br>
            ブラウザの設定でデフォルト保存先を変更できます
        </div>
    `;

    document.body.appendChild(info);

    setTimeout(() => {
        if (document.body.contains(info)) {
            document.body.removeChild(info);
        }
    }, 8000);
}

// ブラウザ通知を表示
function showBrowserNotification(filename) {
    if ('Notification' in window && Notification.permission === 'granted') {
        try {
            const notification = new Notification('録音完了', {
                body: `${filename} のダウンロードが完了しました`,
                icon: '/favicon.ico',
                badge: '/favicon.ico',
                tag: 'recording-complete',
                requireInteraction: false,
                silent: false
            });

            // 通知をクリックした時の動作
            notification.onclick = function() {
                window.focus();
                this.close();
            };

            // 5秒後に自動で閉じる
            setTimeout(() => {
                notification.close();
            }, 5000);
        } catch (error) {
            console.error('通知の表示に失敗しました:', error);
        }
    }
}
</script>
@endsection
