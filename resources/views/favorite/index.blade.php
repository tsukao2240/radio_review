@extends('layouts.header')
@section('content')

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
// 進行中の録音を管理するMap
const activeRecordings = new Map();

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
                                stopRecording(data.recording_id, currentButton, statusDiv);
                            };
                        }

                        // 録音状態を監視開始
                        startRecordingMonitor(data.recording_id, currentButton, data.filename, statusDiv, durationMinutes);
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

                    // ボタンラッパーを再表示してダウンロードボタンに変更
                    const btnWrapper = button.closest('.recording-btn-wrapper');
                    if (btnWrapper) {
                        btnWrapper.style.display = 'flex';
                    }

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

                // ボタンラッパーを再表示
                const btnWrapper = button.closest('.recording-btn-wrapper');
                if (btnWrapper) {
                    btnWrapper.style.display = 'flex';
                }

                // ボタンを元に戻す
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

// 録音ファイルをダウンロード
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