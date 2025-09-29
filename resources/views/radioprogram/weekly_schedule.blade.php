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
</script>
@endsection
