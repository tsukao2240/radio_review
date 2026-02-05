@extends('layouts.header')
@section('content')

<title>放送中の番組</title>

@include('includes.search')
<span>
    {{ Breadcrumbs::render('schedule') }}
</span>
<h3 style="text-align: center">放送中の番組</h3>
<div>
    <table class="table table-bordered table-responsive">
        <thead class="thead-light">
            <tr>
                <th>放送局</th>
                <th>番組名</th>
                <th style="width:25%">出演者</th>
                <th>放送時間</th>
                <th style="white-space: nowrap">ホームページ</th>
                <th style="width:120px">タイムフリー録音</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results as $result)
            <tr>
                <td><a href="{{ url('schedule/' . $result['station_id']) }}">{{$result['station']}}</a></td>
                <td>
                    <a href="{{ url('list/' . $result['station_id'] . '/' . $result['title']) }}?from=schedule">{{$result['title']}}</a>
                </td>
                <td style="width:25%">{{ $result['cast'] }}</td>
                <td style="white-space: nowrap">{{$result['start'] . ' ' . '-' . ' '. $result['end']}}</td>
                {{-- 番組ホームページのURLがAPIに記述されていない番組があるため場合分け --}}
                @if ($result['url'] !== '')
                <td><a href="{{$result['url']}}">公式HP</a></td>
                @else
                <td></td>
                @endif
                <td>
                    <button class="btn btn-sm btn-warning live-recording-btn"
                            data-station-id="{{ $result['station_id'] }}"
                            data-title="{{ $result['title'] }}"
                            data-start="{{ str_replace(':', '', $result['start']) }}"
                            data-end="{{ str_replace(':', '', $result['end']) }}">
                        録音開始
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
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let activeRecordings = new Map(); // recording_id -> {button, intervalId}

    // 放送中番組の録音ボタンのイベントリスナー
    document.querySelectorAll('.live-recording-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const stationId = this.dataset.stationId;
            const title = this.dataset.title;
            const startTime = this.dataset.start;
            const endTime = this.dataset.end;

            // 現在の日付を取得（YYYYMMDD形式）
            const now = new Date();
            const today = now.getFullYear() +
                         String(now.getMonth() + 1).padStart(2, '0') +
                         String(now.getDate()).padStart(2, '0');

            // ボタンを無効化
            this.disabled = true;
            this.textContent = '録音開始中...';

            // 録音時間を計算（分単位）
            const startMinutes = parseInt(startTime.substring(0, 2)) * 60 + parseInt(startTime.substring(2, 4));
            const endMinutes = parseInt(endTime.substring(0, 2)) * 60 + parseInt(endTime.substring(2, 4));
            const durationMinutes = endMinutes - startMinutes;

            // AJAX リクエストでタイムフリー録音開始（放送中番組用）
            fetch('{{ route("recording.timefree.start") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    station_id: stationId,
                    title: title,
                    start_time: today + startTime,
                    end_time: today + endTime
                })
            })
            .then(response => response.json())
            .then(data => {
                const currentButton = this;
                alert('録音開始レスポンス: success=' + data.success + ', recording_id=' + data.recording_id);

                if (data.success) {
                    // ボタンを非表示にして、進行状況表示を表示
                    alert('ボタンを非表示にします');
                    currentButton.style.display = 'none';
                    const statusDiv = currentButton.nextElementSibling;

                    if (!statusDiv) {
                        alert('エラー: statusDivが見つかりません');
                        return;
                    }

                    alert('進行状況エリアを表示します');
                    statusDiv.style.display = 'block';

                    // 停止ボタンのイベントリスナーを設定
                    const stopBtn = statusDiv.querySelector('.stop-recording-btn');
                    if (stopBtn) {
                        stopBtn.onclick = function() {
                            stopRecording(data.recording_id, currentButton, statusDiv);
                        };
                    } else {
                        alert('警告: 停止ボタンが見つかりません');
                    }

                    // 録音状態を監視開始（録音時間も渡す）
                    alert('監視を開始します: ' + data.recording_id);
                    startRecordingMonitor(data.recording_id, currentButton, data.filename, statusDiv, durationMinutes);
                } else {
                    currentButton.disabled = false;
                    currentButton.textContent = '録音開始';
                    alert('録音開始に失敗しました: ' + data.message);
                }
            }.bind(this))
            .catch(error => {
                this.disabled = false;
                this.textContent = '録音開始';
                alert('エラーが発生しました: ' + error);
            }.bind(this));
        });
    });

    // 録音状態監視開始
    function startRecordingMonitor(recordingId, button, filename, statusDiv, durationMinutes) {
        const startTime = Date.now();
        const totalSeconds = durationMinutes * 60;

        // 録音情報を保存
        activeRecordings.set(recordingId, {
            button: button,
            statusDiv: statusDiv,
            filename: filename,
            startTime: startTime,
            totalSeconds: totalSeconds
        });

        // 総録音時間を表示
        const totalTimeSpan = statusDiv.querySelector('.total-time');
        totalTimeSpan.textContent = formatTime(totalSeconds);

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

                // 経過時間を計算（APIレスポンスの値を優先、整数に変換）
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

                // ファイルサイズを表示（APIレスポンスの値を使用）
                if (data.file_size !== undefined && data.file_size > 0) {
                    const fileSizeSpan = statusDiv.querySelector('.file-size');
                    if (fileSizeSpan) {
                        fileSizeSpan.textContent = data.file_size_formatted || formatFileSize(data.file_size);
                    }
                }

                // 録音完了判定（APIの is_recording を使用）
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
                    button.classList.remove('btn-warning', 'btn-danger');
                    button.classList.add('btn-success');
                    button.disabled = false;

                    // ダウンロード完了ポップアップ
                    showDownloadCompletePopup(filename, recordingId);

                    // ボタンをダウンロードボタンに変更
                    button.onclick = function() {
                        downloadRecording(recordingId);
                    };
                }
            }
        })
        .catch(error => {
            // エラー時は画面にアラート表示
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
                button.textContent = '録音開始';
                button.classList.remove('btn-danger', 'btn-success');
                button.classList.add('btn-warning');
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

    // ダウンロード完了ポップアップ表示
    function showDownloadCompletePopup(filename, recordingId) {
        const popup = document.createElement('div');
        popup.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border: 2px solid #28a745;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            z-index: 10000;
            text-align: center;
            min-width: 300px;
        `;

        popup.innerHTML = `
            <h4 style="color: #28a745; margin-bottom: 15px;">📻 録音完了！</h4>
            <p style="margin-bottom: 15px;">ファイル: ${filename}</p>
            <button id="downloadNow" class="btn btn-success" style="margin-right: 10px;">
                今すぐダウンロード
            </button>
            <button id="closePopup" class="btn btn-secondary">
                閉じる
            </button>
        `;

        document.body.appendChild(popup);

        // ダウンロードボタンのイベント
        document.getElementById('downloadNow').onclick = function() {
            downloadRecording(recordingId);
            document.body.removeChild(popup);
        };

        // 閉じるボタンのイベント
        document.getElementById('closePopup').onclick = function() {
            document.body.removeChild(popup);
        };

        // 10秒後に自動で閉じる
        setTimeout(() => {
            if (document.body.contains(popup)) {
                document.body.removeChild(popup);
            }
        }, 10000);
    }

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
                    // デフォルト保存先 D:\ミュージック\radio を指定して保存ダイアログを表示
                    const fileHandle = await window.showSaveFilePicker({
                        suggestedName: filename,
                        startIn: 'music', // 音楽フォルダから開始
                        types: [{
                            description: 'ラジオ録音ファイル',
                            accept: {
                                'audio/mp4': ['.m4a'],
                                'audio/mpeg': ['.mp3']
                            }
                        }]
                    });

                    const writable = await fileHandle.createWritable();
                    await writable.write(blob);
                    await writable.close();

                    showSaveSuccessPopup(fileHandle.name);
                    return;
                } catch (e) {
                    if (e.name !== 'AbortError') {
                        console.error('Save picker error:', e);
                    }
                    // ユーザーがキャンセルした場合はそのまま終了
                    return;
                }
            }

            // File System Access API未対応の場合のフォールバック
            // この場合はブラウザのデフォルトダウンロードフォルダに保存される
            downloadWithCustomName(blob, filename);
            showBrowserDownloadInfo();

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

        // 一時的にDOMに追加してクリック
        document.body.appendChild(a);
        a.click();

        // クリーンアップ
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);

        // ダウンロード後に保存先の案内を表示
        showBrowserDownloadInfo();
    }

    // 保存成功ポップアップ表示
    function showSaveSuccessPopup(filename) {
        const popup = document.createElement('div');
        popup.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border: 2px solid #28a745;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            z-index: 10000;
            text-align: center;
            min-width: 350px;
        `;

        popup.innerHTML = `
            <div style="font-size: 24px; margin-bottom: 15px;">💾✅</div>
            <h4 style="color: #28a745; margin-bottom: 10px;">保存完了！</h4>
            <p style="margin-bottom: 15px; word-break: break-all;">
                <strong>${filename}</strong><br>
                が選択した場所に保存されました
            </p>
            <button id="closeSuccessPopup" class="btn btn-success">
                OK
            </button>
        `;

        document.body.appendChild(popup);

        // OKボタンのイベント
        document.getElementById('closeSuccessPopup').onclick = function() {
            document.body.removeChild(popup);
        };

        // 5秒後に自動で閉じる
        setTimeout(() => {
            if (document.body.contains(popup)) {
                document.body.removeChild(popup);
            }
        }, 5000);
    }

    // ブラウザダウンロードの案内表示
    function showBrowserDownloadInfo() {
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
            max-width: 400px;
        `;

        info.innerHTML = `
            <div style="font-weight: bold; margin-bottom: 8px;">💾 ダウンロード完了</div>
            <div style="font-size: 14px; line-height: 1.4;">
                <strong>推奨保存場所:</strong><br>
                D:\\ミュージック\\radio フォルダ<br>
                <small style="opacity: 0.8;">ブラウザ設定から変更できます</small>
            </div>
        `;

        document.body.appendChild(info);

        // クリックで閉じる
        info.onclick = function() {
            if (document.body.contains(info)) {
                document.body.removeChild(info);
            }
        };

        // 10秒後に自動で消す
        setTimeout(() => {
            if (document.body.contains(info)) {
                document.body.removeChild(info);
            }
        }, 10000);
    }

    // 録音IDからファイル名を取得
    function getFilenameFromRecordingId(recordingId) {
        // recording_idの形式: "TBC_202509292200_20250929224930"
        const parts = recordingId.split('_');
        if (parts.length >= 3) {
            const station = parts[0];
            const datetime = parts[1];
            const timestamp = parts[2];

            // 日時をフォーマット
            const year = datetime.substring(0, 4);
            const month = datetime.substring(4, 6);
            const day = datetime.substring(6, 8);
            const hour = datetime.substring(8, 10);
            const minute = datetime.substring(10, 12);

            return `${station}_${year}${month}${day}_${hour}${minute}.m4a`;
        }
        return recordingId + '.m4a';
    }

});
</script>
@endsection
