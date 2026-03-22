@extends('layouts.header')
@section('content')
@include('includes.search')

<x-breadcrumbs :items="[
    ['label' => '放送中の番組', 'url' => route('program.schedule')],
    ['label' => $broadcast_name . ' - 2週間番組表']
]" />

<title>{{ $broadcast_name }} - 2週間番組表</title>

<div class="max-w-7xl mx-auto">
    <!-- ヘッダー -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6 space-y-4 md:space-y-0">
        <a href="{{ route('schedule.twoweek', ['area' => $selectedArea]) }}" 
           class="touch-target inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition w-fit">
            <i class="fas fa-arrow-left mr-2"></i>放送局選択に戻る
        </a>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white">
            <i class="fas fa-calendar-alt mr-2"></i>{{ $broadcast_name }} - 2週間番組表
        </h1>
        <div class="md:w-32"></div>
    </div>

    <!-- 日付ナビゲーション改善版 -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-3">
            <i class="fas fa-calendar mr-2"></i>日付を選択
        </h2>
        <div class="relative">
            <!-- 左スクロールボタン -->
            <button id="scroll-left"
                    class="absolute left-0 top-1/2 -translate-y-1/2 z-10 touch-target bg-white dark:bg-gray-800 shadow-lg rounded-full p-3 hover:bg-gray-100 dark:hover:bg-gray-700 transition hidden md:block"
                    aria-label="前の日付へ">
                <i class="fas fa-chevron-left text-gray-600 dark:text-gray-300"></i>
            </button>

            <!-- 日付ボタン（横スクロール） -->
            <div id="date-scroll-container"
                 class="flex overflow-x-auto snap-x snap-mandatory scrollbar-hide space-x-2 px-0 md:px-12">
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
                <a href="#date-{{ $date }}"
                   class="snap-center flex-shrink-0 touch-target min-w-[100px] px-4 py-3 rounded-lg text-center font-medium transition
                       {{ $isToday ? 'bg-primary-500 text-white shadow-lg' : ($isPast ? 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-primary-100 dark:hover:bg-primary-900') }}">
                    <div class="text-xs {{ $isSunday && !$isToday ? 'text-red-500' : ($isSaturday && !$isToday ? 'text-blue-500' : '') }}">
                        {{ $dateObj->format('m月d日') }}
                    </div>
                    <div class="text-sm font-bold {{ $isSunday && !$isToday ? 'text-red-600' : ($isSaturday && !$isToday ? 'text-blue-600' : '') }}">
                        ({{ $dayOfWeek }})
                    </div>
                    @if($isToday)
                    <div class="text-xs mt-1 bg-yellow-400 text-gray-800 rounded px-1">今日</div>
                    @endif
                </a>
                @endforeach
            </div>

            <!-- 右スクロールボタン -->
            <button id="scroll-right"
                    class="absolute right-0 top-1/2 -translate-y-1/2 z-10 touch-target bg-white dark:bg-gray-800 shadow-lg rounded-full p-3 hover:bg-gray-100 dark:hover:bg-gray-700 transition hidden md:block"
                    aria-label="次の日付へ">
                <i class="fas fa-chevron-right text-gray-600 dark:text-gray-300"></i>
            </button>
        </div>
    </div>

    <!-- エリア選択（エリアフリー用） -->
    <div class="card-base mb-6">
        <div class="flex flex-col md:flex-row md:items-center space-y-3 md:space-y-0 md:space-x-4">
            <label class="font-semibold text-gray-800 dark:text-white whitespace-nowrap">
                <i class="fas fa-map-marker-alt mr-2"></i>録音エリア:
            </label>
            <select id="global-area-select" class="flex-1 md:max-w-md form-select bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2">
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
            <small class="text-gray-600 dark:text-gray-400 flex items-center">
                <i class="fas fa-info-circle mr-1"></i>
                エリアフリー機能で他地域の番組も録音できます
            </small>
        </div>
    </div>

    <!-- 番組カードリスト -->
    <div class="space-y-8">
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

            $startTime = \Carbon\Carbon::createFromFormat('YmdHis', $entry['ft']);
            $endTime = \Carbon\Carbon::createFromFormat('YmdHis', $entry['to']);

            $isPast = $endTime->isPast();
            $isFuture = $startTime->isFuture();
            $isNow = $startTime->isPast() && $endTime->isFuture();

            $canTimefree = $isPast && $endTime->diffInDays($now) <= 7;
        @endphp

        @if($showDateHeader)
        <div class="sticky top-20 z-10 bg-white dark:bg-gray-900 py-3 border-b-2 border-gray-200 dark:border-gray-700" 
             id="date-{{ $entryDate }}">
            <h2 class="text-xl md:text-2xl font-bold {{ $isSunday ? 'text-red-600 dark:text-red-400' : ($isSaturday ? 'text-blue-600 dark:text-blue-400' : 'text-gray-800 dark:text-white') }}">
                <i class="fas fa-calendar-day mr-2"></i>
                {{ $dateObj->format('Y年n月j日') }}（{{ $dayOfWeek }}）
                @if($entryDate === $todayStr)
                <span class="ml-2 inline-flex items-center px-2 py-1 text-xs font-medium bg-yellow-400 text-gray-800 rounded">今日</span>
                @endif
            </h2>
        </div>
        @endif

        <div class="card-base {{ $isNow ? 'border-2 border-red-500' : '' }}">
            <div class="flex flex-col md:flex-row md:items-start gap-4">
                <!-- 時間バッジ -->
                <div class="flex md:flex-col items-center md:items-start space-x-2 md:space-x-0 md:space-y-1 md:min-w-[80px]">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold whitespace-nowrap
                        {{ $isNow ? 'bg-red-500 text-white' : ($isPast ? 'bg-gray-500 text-white' : 'bg-primary-500 text-white') }}">
                        {{ $entry['start'] }}
                    </span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">〜 {{ $entry['end'] }}</span>
                </div>

                <!-- 番組情報 -->
                <div class="flex-1">
                    <a href="{{ url('list/' . $entry['id'] . '/' . urlencode($entry['title'])) }}?from=timefree&date={{ $entry['date'] }}"
                       class="text-lg font-bold text-gray-800 dark:text-white hover:text-primary-600 dark:hover:text-primary-400 transition hover:underline">
                        {{ $entry['title'] }}
                    </a>
                    @if($entry['cast'])
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        <i class="fas fa-microphone mr-1"></i>{{ $entry['cast'] }}
                    </p>
                    @endif
                    @if($entry['desc'])
                    <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">{{ $entry['desc'] }}...</p>
                    @endif
                </div>

                <!-- アクション -->
                <div class="flex md:flex-col space-x-2 md:space-x-0 md:space-y-2 md:min-w-[200px]">
                    @if($isNow)
                        <span class="inline-flex items-center px-4 py-2 rounded-lg bg-red-500 text-white font-semibold whitespace-nowrap">
                            <span class="relative flex h-2 w-2 mr-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
                            </span>
                            放送中
                        </span>
                    @elseif($canTimefree)
                        <div class="recording-controls flex-1" data-entry-id="{{ $entry['ft'] }}">
                            <button class="w-full touch-target bg-gradient-to-r from-green-500 to-green-700 text-white font-semibold py-3 rounded-lg hover:shadow-lg transition timefree-btn"
                                    data-station-id="{{ $entry['id'] }}"
                                    data-station-name="{{ $station_id }}"
                                    data-title="{{ $entry['title'] }}"
                                    data-cast="{{ $entry['cast'] ?? '' }}"
                                    data-ft="{{ $entry['ft'] }}"
                                    data-to="{{ $entry['to'] }}">
                                <i class="fas fa-download mr-2"></i>タイムフリー録音
                            </button>
                            <div class="recording-progress mt-2" style="display: none;">
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-5 mb-2">
                                    <div class="progress-bar bg-gradient-to-r from-green-500 to-green-600 h-5 rounded-full text-xs font-medium text-white text-center leading-5 transition-all duration-300"
                                         style="width: 0%">0%</div>
                                </div>
                                <small class="block text-xs text-gray-600 dark:text-gray-400 mb-2">
                                    <span class="file-size">0 MB</span> | <span class="elapsed-time">00:00</span>
                                </small>
                                <button class="w-full touch-target bg-red-500 hover:bg-red-600 text-white font-semibold py-2 rounded-lg transition stop-btn" style="display: none;">
                                    <i class="fas fa-stop mr-2"></i>停止
                                </button>
                                <button class="w-full touch-target bg-primary-500 hover:bg-primary-600 text-white font-semibold py-2 rounded-lg transition download-btn" style="display: none;">
                                    <i class="fas fa-download mr-2"></i>ダウンロード
                                </button>
                            </div>
                        </div>
                    @elseif($isFuture)
                        @if(Auth::check())
                        <button class="w-full touch-target bg-gradient-to-r from-yellow-400 to-yellow-600 text-gray-800 font-semibold py-3 rounded-lg hover:shadow-lg transition schedule-btn"
                                data-station-id="{{ $entry['id'] }}"
                                data-title="{{ $entry['title'] }}"
                                data-ft="{{ $entry['ft'] }}"
                                data-to="{{ $entry['to'] }}">
                            <i class="fas fa-calendar-check mr-2"></i>録音予約
                        </button>
                        @else
                        <a href="{{ route('login') }}"
                           class="w-full touch-target text-center bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold py-3 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition block">
                            <i class="fas fa-sign-in-alt mr-2"></i>ログインして予約
                        </a>
                        @endif
                    @elseif($isPast && !$canTimefree)
                        <span class="inline-flex items-center px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 font-medium whitespace-nowrap">
                            <i class="fas fa-clock mr-2"></i>期間外
                        </span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach

        @if(count($entries) === 0)
        <div class="card-base text-center py-12">
            <i class="fas fa-info-circle text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-600 dark:text-gray-400">番組データがありません。</p>
        </div>
        @endif
    </div>
</div>

<!-- スクロールバー非表示CSS -->
<style>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 録音APIのルートURLを設定（共通モジュールで使用）
    window.recordingStatusUrl = '{{ route("recording.status") }}';
    window.recordingStopUrl = '{{ route("recording.stop") }}';
    window.recordingDownloadUrl = '{{ route("recording.download") }}';

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // 通知許可をリクエスト
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    // スムーズスクロール
    const container = document.getElementById('date-scroll-container');
    const scrollLeft = document.getElementById('scroll-left');
    const scrollRight = document.getElementById('scroll-right');

    scrollLeft?.addEventListener('click', () => {
        container.scrollBy({ left: -200, behavior: 'smooth' });
    });

    scrollRight?.addEventListener('click', () => {
        container.scrollBy({ left: 200, behavior: 'smooth' });
    });

    // 今日の日付に自動スクロール
    const todayElement = container?.querySelector('.bg-primary-500');
    if (todayElement) {
        setTimeout(() => {
            todayElement.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
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

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>開始中...';

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

                    const startMinutes = parseInt(ft.substring(8, 10)) * 60 + parseInt(ft.substring(10, 12));
                    const endMinutes = parseInt(to.substring(8, 10)) * 60 + parseInt(to.substring(10, 12));
                    const durationMinutes = endMinutes - startMinutes;

                    window.startRecordingMonitor(data.recording_id, this, data.filename, progressDiv, durationMinutes);

                    const stopBtn = progressDiv.querySelector('.stop-btn');
                    stopBtn.style.display = 'block';
                    stopBtn.onclick = function() {
                        window.stopRecording(data.recording_id, btn, progressDiv);
                    };
                } else {
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-download mr-2"></i>タイムフリー録音';
                    alert('録音開始に失敗しました: ' + data.message);
                }
            })
            .catch(err => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-download mr-2"></i>タイムフリー録音';
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
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>予約中...';

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
                    this.innerHTML = '<i class="fas fa-check mr-2"></i>予約完了';
                    this.classList.remove('from-yellow-400', 'to-yellow-600', 'text-gray-800');
                    this.classList.add('bg-gray-400', 'text-white', 'cursor-not-allowed');
                } else {
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-calendar-check mr-2"></i>録音予約';
                    alert('予約に失敗しました: ' + data.message);
                }
            })
            .catch(err => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-calendar-check mr-2"></i>録音予約';
                alert('エラー: ' + err);
            });
        });
    });
});
</script>
@endsection
