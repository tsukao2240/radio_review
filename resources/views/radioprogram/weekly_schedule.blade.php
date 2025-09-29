@extends('layouts.header')
@section('content')
@include('includes.search')
<span>
    {{ Breadcrumbs::render('weekly_schedule') }}
</span>
<title>{{ $broadcast_name }}の週間番組表</title>
<h3 style="text-align:center">週間番組表({{ $broadcast_name }})</h3>
<div class="timetable">
    @for ($i = 0; $i < count($thisWeek) - 1; $i++) <div class="tablebox">
        <div class="table">
            <table class="table table-bordered table-responsive">
                <thead class="thead-light">
                    <tr>
                        <th>{{ date('m月d日(D)',strtotime($thisWeek[$i])) }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($entries as $entry)
                    @if ($thisWeek[$i] === $entry['date'] && intval($entry['start']) >= 5 && intval($entry['start'] < 24))
                    <tr>
                        <td>
                            <a href="{{ url('list/' . $entry['id'] . '/' . $entry['title'])}}">{{$entry['title']}}</a>
                            @if ($entry['cast'] !== '')
                            <br>
                            {{ $entry['cast'] }}
                            @endif
                            <br>
                            {{ $entry['start'] . ' ' . '-' . ' '. $entry['end'] }}
                            <br>
                            @php
                                $programEndTime = \Carbon\Carbon::createFromFormat('Ymd H:i', $entry['date'] . ' ' . $entry['end']);
                                $canRecord = $programEndTime->diffInDays(now()) <= 7;
                            @endphp
                            @if($canRecord && $programEndTime->isPast())
                                <button class="btn btn-sm btn-success recording-btn"
                                        data-station-id="{{ $entry['id'] }}"
                                        data-title="{{ $entry['title'] }}"
                                        data-date="{{ $entry['date'] }}"
                                        data-start="{{ str_replace(':', '', $entry['start']) }}"
                                        data-end="{{ str_replace(':', '', $entry['end']) }}">
                                    タイムフリー録音
                                </button>
                            @elseif($programEndTime->isFuture())
                                <span class="text-muted small">まだ放送されていません</span>
                            @else
                                <span class="text-muted small">タイムフリー期間終了</span>
                            @endif
                        </td>
                    </tr>
                        @elseif(intval($thisWeek[$i]) + 1 === intval($entry['date']) && intval($entry['start']) >= 24)
                        <tr>
                            <td>
                                <a href="{{ url('list/' . $entry['id'] . '/' . $entry['title'])}}">{{$entry['title']}}</a>
                                @if ($entry['cast'] !== '')
                                <br>
                                {{ $entry['cast'] }}
                                @endif
                                <br>
                                {{ $entry['start'] . ' ' . '-' . ' '. $entry['end'] }}
                                <br>
                                @php
                                    $programEndTime = \Carbon\Carbon::createFromFormat('Ymd H:i', $entry['date'] . ' ' . $entry['end']);
                                    $canRecord = $programEndTime->diffInDays(now()) <= 7;
                                @endphp
                                @if($canRecord && $programEndTime->isPast())
                                    <button class="btn btn-sm btn-success recording-btn"
                                            data-station-id="{{ $entry['id'] }}"
                                            data-title="{{ $entry['title'] }}"
                                            data-date="{{ $entry['date'] }}"
                                            data-start="{{ str_replace(':', '', $entry['start']) }}"
                                            data-end="{{ str_replace(':', '', $entry['end']) }}">
                                        タイムフリー録音
                                    </button>
                                @elseif($programEndTime->isFuture())
                                    <span class="text-muted small">まだ放送されていません</span>
                                @else
                                    <span class="text-muted small">タイムフリー期間終了</span>
                                @endif
                            </td>
                        </tr>
                        @endif
                        @endforeach
                </tbody>
            </table>
        </div>
</div>
@endfor
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // タイムフリー録音ボタンのイベントリスナー
    document.querySelectorAll('.recording-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const stationId = this.dataset.stationId;
            const title = this.dataset.title;
            const date = this.dataset.date;
            const startTime = this.dataset.start;
            const endTime = this.dataset.end;

            // ボタンを無効化
            this.disabled = true;
            this.textContent = '録音開始中...';

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
                    this.textContent = '録音中';
                    this.classList.remove('btn-success');
                    this.classList.add('btn-warning');
                    alert('タイムフリー録音を開始しました: ' + data.filename);
                } else {
                    this.disabled = false;
                    this.textContent = 'タイムフリー録音';
                    alert('録音開始に失敗しました: ' + data.message);
                }
            })
            .catch(error => {
                this.disabled = false;
                this.textContent = 'タイムフリー録音';
                alert('エラーが発生しました: ' + error);
            });
        });
    });
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
</script>
@endsection
