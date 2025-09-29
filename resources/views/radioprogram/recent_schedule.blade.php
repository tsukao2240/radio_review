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
                    <a href="{{ url('list/' . $result['station_id'] . '/' . $result['title'])}}">{{$result['title']}}</a>
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
                if (data.success) {
                    this.textContent = '録音中';
                    this.classList.remove('btn-warning');
                    this.classList.add('btn-danger');
                    alert('録音を開始しました: ' + data.filename);

                    // 録音状態を監視開始
                    startRecordingMonitor(data.recording_id, this, data.filename);
                } else {
                    this.disabled = false;
                    this.textContent = '録音開始';
                    alert('録音開始に失敗しました: ' + data.message);
                }
            })
            .catch(error => {
                this.disabled = false;
                this.textContent = '録音開始';
                alert('エラーが発生しました: ' + error);
            });
        });
    });

    // 録音状態監視開始
    function startRecordingMonitor(recordingId, button, filename) {
        // 5秒間隔で状態をチェック
        const intervalId = setInterval(() => {
            checkRecordingStatus(recordingId, button, filename, intervalId);
        }, 5000);

        activeRecordings.set(recordingId, {
            button: button,
            intervalId: intervalId,
            filename: filename
        });
    }

    // 録音状態をチェック
    function checkRecordingStatus(recordingId, button, filename, intervalId) {
        fetch('{{ route("recording.status") }}?' + new URLSearchParams({
            recording_id: recordingId
        }))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.status === 'completed' || data.file_exists) {
                    // 録音完了
                    clearInterval(intervalId);
                    activeRecordings.delete(recordingId);

                    button.textContent = 'ダウンロード';
                    button.classList.remove('btn-danger');
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
            console.log('録音状態チェックエラー:', error);
        });
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
