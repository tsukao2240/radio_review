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
});
</script>
@endsection
