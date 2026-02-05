@extends('layouts.header')
@section('content')

<!--APIからデータが取得できた場合-->
@if(isset($entries))
@foreach ($entries as $entry)
<title>{{ $entry['title'] }}</title>
@include('includes.search')
@if(request('from') === 'search' && request('keyword'))
    {{ Breadcrumbs::render('detail.from_search', $entry['id'], $entry['title'], request('keyword')) }}
@elseif(request('from') === 'schedule')
    {{ Breadcrumbs::render('detail.from_schedule', $entry['id'], $entry['title']) }}
@elseif(request('from') === 'weekly' && request('station_id'))
    {{ Breadcrumbs::render('detail.from_weekly', $entry['id'], $entry['title']) }}
@elseif(request('from') === 'timefree')
    {{ Breadcrumbs::render('detail.from_timefree', $entry['id'], $entry['title']) }}
@elseif(request('from') === 'mypage')
    {{ Breadcrumbs::render('detail.from_mypage', $entry['id'], $entry['title']) }}
@elseif(request('from') === 'review')
    {{ Breadcrumbs::render('detail.from_review', $entry['id'], $entry['title']) }}
@elseif(request('from') === 'favorites')
    {{ Breadcrumbs::render('detail.from_favorites', $entry['id'], $entry['title']) }}
@else
    {{ Breadcrumbs::render('detail', $entry['id'], $entry['title']) }}
@endif
@endforeach
<!--APIから取得できず、DBからデータを取得した場合-->
@elseif(isset($results))
@foreach ($results as $result)
<title>{{ $result->title }}</title>
@include('includes.search')
@if(request('from') === 'search' && request('keyword'))
    {{ Breadcrumbs::render('detail.from_search', $result->station_id, $result->title, request('keyword')) }}
@elseif(request('from') === 'schedule')
    {{ Breadcrumbs::render('detail.from_schedule', $result->station_id, $result->title) }}
@elseif(request('from') === 'weekly' && request('station_id'))
    {{ Breadcrumbs::render('detail.from_weekly', $result->station_id, $result->title) }}
@elseif(request('from') === 'timefree')
    {{ Breadcrumbs::render('detail.from_timefree', $result->station_id, $result->title) }}
@elseif(request('from') === 'mypage')
    {{ Breadcrumbs::render('detail.from_mypage', $result->station_id, $result->title) }}
@elseif(request('from') === 'review')
    {{ Breadcrumbs::render('detail.from_review', $result->station_id, $result->title) }}
@elseif(request('from') === 'favorites')
    {{ Breadcrumbs::render('detail.from_favorites', $result->station_id, $result->title) }}
@else
    {{ Breadcrumbs::render('detail', $result->station_id, $result->title) }}
@endif
@endforeach
@endif

<!--APIからデータが取得できた場合-->
@if(isset($entries))
<div class="d-flex justify-content-sm-around">
    <div class="col-lg-4">
        @foreach ($entries as $entry)
        <section>
            <h3>{{ $entry['title'] }}</h3>
            <h5>{{ $entry['cast'] }}</h5>
            @if (!empty($entry['image']))
            <div>
                <img src="{!! $entry['image'] !!}" loading="lazy" alt="{{ $entry['title'] }}">
            </div>
            @endif
            @if(!empty($entry['desc']))
            <div>
                {!! $entry['desc'] !!}
            </div>
            @endif
            @if (!empty($entry['info']))
            <br>
            <div>
                {!! $entry['info'] !!}
            </div>
            @endif
            <br>
            <!-- お気に入りボタン -->
            <button id="favorite-btn" class="btn btn-outline-danger mb-3" style="width: 100%;">
                <i class="fas fa-heart"></i> お気に入りに追加
            </button>
            
            <!-- タイムフリー録音ボタン -->
            @if($latestBroadcast)
            <div class="timefree-recording-section mb-3">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 直近の放送：{{ $latestBroadcast['date'] }} {{ $latestBroadcast['start'] }}～{{ $latestBroadcast['end'] }}
                </div>
                <button class="btn btn-success recording-btn"
                        data-station-id="{{ $entry['id'] }}"
                        data-station-name="{{ $entry['id'] }}"
                        data-title="{{ $entry['title'] }}"
                        data-cast="{{ $entry['cast'] }}"
                        data-date="{{ $latestBroadcast['date'] }}"
                        data-start="{{ $latestBroadcast['start'] }}"
                        data-end="{{ $latestBroadcast['end'] }}">
                    <i class="fas fa-microphone"></i> この放送を録音する
                </button>
            </div>
            @else
            <div class="alert alert-secondary mb-3">
                <i class="fas fa-exclamation-circle"></i> タイムフリー期間内の放送がありません
            </div>
            @endif
            
            @include('layouts.post_create',['program_id' => $program_id])
            @include('layouts.post_view',['station_id' => $entry['id'],'program_title' => $entry['title']])
        </section>
        @endforeach
    </div>
</div>
<!--APIから取得できず、DBからデータを取得した場合-->
@elseif(isset($results))
<div class="d-flex justify-content-sm-around">
    <div class="col-md-4">
        @foreach ($results as $result)
        <section>
            <h3>{{ $result->title }}</h3>
            <h5>{{ $result->cast }}</h5>
            @if (!empty($result->image))
            <div>
                <img src="{!! $result->image !!}" loading="lazy" alt="{{ $result->title }}">
            </div>
            @endif
            @if (!empty($result->info))
            <div>
                {!! $result->info !!}
            </div>
            @endif
            <br>
            <!-- お気に入りボタン -->
            <button id="favorite-btn" class="btn btn-outline-danger mb-3" style="width: 100%;">
                <i class="fas fa-heart"></i> お気に入りに追加
            </button>
            
            <!-- タイムフリー録音ボタン -->
            @if($latestBroadcast)
            <div class="timefree-recording-section mb-3">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 直近の放送：{{ $latestBroadcast['date'] }} {{ $latestBroadcast['start'] }}～{{ $latestBroadcast['end'] }}
                </div>
                <button class="btn btn-success recording-btn"
                        data-station-id="{{ $result->station_id }}"
                        data-station-name="{{ $result->station_id }}"
                        data-title="{{ $result->title }}"
                        data-cast="{{ $result->cast }}"
                        data-date="{{ $latestBroadcast['date'] }}"
                        data-start="{{ $latestBroadcast['start'] }}"
                        data-end="{{ $latestBroadcast['end'] }}">
                    <i class="fas fa-microphone"></i> この放送を録音する
                </button>
            </div>
            @else
            <div class="alert alert-secondary mb-3">
                <i class="fas fa-exclamation-circle"></i> タイムフリー期間内の放送がありません
            </div>
            @endif
            
            @include('layouts.post_create',['program_id' => $result->id])
            @include('layouts.post_view',['station_id' => $result->station_id,'program_title' => $result->title])
        </section>
        @endforeach
    </div>
</div>
</section>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const favoriteBtn = document.getElementById('favorite-btn');

    @if(isset($entries))
    @foreach ($entries as $entry)
    const stationId = "{{ $entry['id'] }}";
    const programTitle = "{{ $entry['title'] }}";
    @endforeach
    @elseif(isset($results))
    @foreach ($results as $result)
    const stationId = "{{ $result->station_id }}";
    const programTitle = "{{ $result->title }}";
    @endforeach
    @endif

    // ログイン確認
    @guest
    // 未ログイン時はボタンを無効化
    favoriteBtn.disabled = true;
    favoriteBtn.innerHTML = '<i class="fas fa-heart"></i> お気に入りに追加（要ログイン）';
    favoriteBtn.addEventListener('click', function() {
        alert('お気に入り機能を使うにはログインが必要です');
        window.location.href = "{{ route('login') }}";
    });
    @else
    // ログイン済み：お気に入り状態を確認
    fetch(`{{ route('favorites.check') }}?station_id=${encodeURIComponent(stationId)}&program_title=${encodeURIComponent(programTitle)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.is_favorite) {
                favoriteBtn.classList.remove('btn-outline-danger');
                favoriteBtn.classList.add('btn-danger');
                favoriteBtn.innerHTML = '<i class="fas fa-heart"></i> お気に入り登録済み';
            }
        });

    // ボタンクリックイベント
    favoriteBtn.addEventListener('click', function() {
        const isFavorite = this.classList.contains('btn-danger');

        if (isFavorite) {
            alert('お気に入りの削除は「お気に入り番組」ページから行ってください');
            return;
        }

        // お気に入り登録
        fetch('{{ route("favorites.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                station_id: stationId,
                program_title: programTitle
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('お気に入りに登録しました');
                favoriteBtn.classList.remove('btn-outline-danger');
                favoriteBtn.classList.add('btn-danger');
                favoriteBtn.innerHTML = '<i class="fas fa-heart"></i> お気に入り登録済み';
            } else {
                alert(data.message || 'お気に入りの登録に失敗しました');
            }
        })
        .catch(error => {
            alert('エラーが発生しました: ' + error);
        });
    });
    @endguest

    // タイムフリー録音機能
    const recordingBtn = document.querySelector('.recording-btn');
    if (recordingBtn) {
        recordingBtn.addEventListener('click', function() {
            const stationId = this.dataset.stationId;
            const stationName = this.dataset.stationName;
            const title = this.dataset.title;
            const cast = this.dataset.cast;
            const date = this.dataset.date;
            const startTime = this.dataset.start.replace(':', '');
            const endTime = this.dataset.end.replace(':', '');

            if (confirm(`「${title}」を録音しますか？`)) {
                const requestBody = {
                    station_id: stationId,
                    station_name: stationName,
                    title: title,
                    cast: cast,
                    start_time: date + startTime,
                    end_time: date + endTime
                };

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
                        alert('録音を開始しました。録音履歴ページで進捗を確認できます。');
                        // 録音履歴ページへ移動するか確認
                        if (confirm('録音履歴ページを開きますか？')) {
                            window.location.href = '{{ route("recording.history") }}';
                        }
                    } else {
                        alert('エラー: ' + (data.message || '録音の開始に失敗しました'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('録音リクエスト中にエラーが発生しました。');
                });
            }
        });
    }
});
</script>
@endsection
