@extends('layouts.header')
@section('content')
@include('includes.search')

<x-breadcrumbs :items="[
    ['label' => '放送中の番組', 'url' => route('program.schedule')],
    ['label' => $broadcast_name . ' 週間番組表']
]" />

<title>{{ $broadcast_name }}の週間番組表</title>

<div class="max-w-7xl mx-auto">
    <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white mb-6">
        <i class="fas fa-calendar-week mr-2"></i>{{ $broadcast_name }} 週間番組表
    </h1>

    <!-- 日付ナビゲーション（横スクロール） -->
    <div class="mb-8 -mx-4 px-4 md:mx-0 md:px-0">
        <div class="flex overflow-x-auto snap-x snap-mandatory scrollbar-hide space-x-2 pb-2">
            @foreach($thisWeek as $index => $date)
            @php
                $dateObj = \Carbon\Carbon::createFromFormat('Ymd', $date);
                $dayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][$dateObj->dayOfWeek];
                $isToday = $date === \Carbon\Carbon::now()->format('Ymd');
                $isSunday = $dateObj->dayOfWeek === 0;
                $isSaturday = $dateObj->dayOfWeek === 6;
            @endphp
            <a href="#date-{{ $date }}"
               class="snap-start flex-shrink-0 touch-target px-4 py-3 rounded-lg text-center font-medium transition min-w-[100px]
                   {{ $isToday ? 'bg-primary-500 text-white shadow-lg' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-primary-100 dark:hover:bg-primary-900' }}">
                <div class="text-xs {{ $isSunday ? 'text-red-500' : ($isSaturday ? 'text-blue-500' : '') }} {{ $isToday ? 'text-white' : '' }}">
                    {{ $dateObj->format('m月d日') }}
                </div>
                <div class="text-sm font-bold {{ $isSunday ? 'text-red-600' : ($isSaturday ? 'text-blue-600' : '') }} {{ $isToday ? 'text-white' : '' }}">
                    ({{ $dayOfWeek }})
                </div>
                @if($isToday)
                <div class="text-xs mt-1 bg-yellow-400 text-gray-800 rounded px-1">今日</div>
                @endif
            </a>
            @endforeach
        </div>
    </div>

    <!-- 番組カードグリッド -->
    @foreach($thisWeek as $date)
    @php
        // この日に表示すべき番組があるかチェック
        $currentDate = $date;
        $nextDate = date('Ymd', strtotime($currentDate . ' +1 day'));
        $hasPrograms = false;
        
        foreach ($entries as $entry) {
            $entryDate = $entry['date'];
            $startTimeInt = (int)str_replace(':', '', $entry['start']);
            $isCurrentDayProgram = ($currentDate === $entryDate && $startTimeInt >= 500 && $startTimeInt < 2400);
            $isCurrentDayLateNightProgram = ($entryDate === $nextDate && $startTimeInt >= 2400 && $startTimeInt < 2900);
            
            if ($isCurrentDayProgram || $isCurrentDayLateNightProgram) {
                $hasPrograms = true;
                break;
            }
        }

        $dateObj = \Carbon\Carbon::createFromFormat('Ymd', $date);
        $dayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][$dateObj->dayOfWeek];
        $isSunday = $dateObj->dayOfWeek === 0;
        $isSaturday = $dateObj->dayOfWeek === 6;
    @endphp

    @if($hasPrograms)
    <div id="date-{{ $date }}" class="mb-12">
        <h2 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white mb-4 sticky top-20 bg-white dark:bg-gray-900 py-2 z-10 {{ $isSunday ? 'text-red-600 dark:text-red-400' : ($isSaturday ? 'text-blue-600 dark:text-blue-400' : '') }}">
            {{ $dateObj->format('Y年m月d日') }}（{{ $dayOfWeek }}）
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($entries as $entry)
            @php
                $entryDate = $entry['date'];
                $startTimeInt = (int)str_replace(':', '', $entry['start']);
                $isCurrentDayProgram = ($currentDate === $entryDate && $startTimeInt >= 500 && $startTimeInt < 2400);
                $isCurrentDayLateNightProgram = ($entryDate === $nextDate && $startTimeInt >= 2400 && $startTimeInt < 2900);
                $shouldDisplay = $isCurrentDayProgram || $isCurrentDayLateNightProgram;

                if (!$shouldDisplay) continue;

                $programStartTime = \Carbon\Carbon::createFromFormat('Ymd H:i', $entry['date'] . ' ' . $entry['start']);
                $programEndTime = \Carbon\Carbon::createFromFormat('Ymd H:i', $entry['date'] . ' ' . $entry['end']);
                $canRecord = $programEndTime->diffInDays(now()) <= 7;
                $isFuture = !$programStartTime->isPast();
                $isPast = $programEndTime->isPast();
            @endphp

            <div class="card-base group">
                <!-- 番組時間バッジ -->
                <div class="flex items-center justify-between mb-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300">
                        <i class="fas fa-clock mr-1"></i>
                        {{ $entry['start'] }} - {{ $entry['end'] }}
                    </span>
                </div>

                <!-- 番組タイトル -->
                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2 group-hover:text-primary-600 transition">
                    <a href="{{ url('list/' . $entry['id'] . '/' . $entry['title']) }}?from=weekly&station_id={{ $station_id }}&date={{ $entryDate }}" 
                       class="hover:underline">
                        {{ $entry['title'] }}
                    </a>
                </h3>

                <!-- 出演者 -->
                @if(!empty($entry['cast']))
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    <i class="fas fa-microphone mr-1"></i>{{ $entry['cast'] }}
                </p>
                @endif

                <!-- アクション -->
                <div class="flex flex-col space-y-2 mt-4">
                    @if($isFuture)
                        <!-- 未来の番組：録音予約 -->
                        @if(Auth::check())
                        <button class="touch-target w-full bg-gradient-to-r from-yellow-400 to-yellow-600 text-gray-800 font-semibold py-3 rounded-lg hover:shadow-lg transition schedule-recording-btn"
                                data-station-id="{{ $entry['id'] }}"
                                data-station-name="{{ $station_id }}"
                                data-title="{{ $entry['title'] }}"
                                data-cast="{{ $entry['cast'] ?? '' }}"
                                data-start="{{ $entry['date'] . str_replace(':', '', $entry['start']) }}"
                                data-end="{{ $entry['date'] . str_replace(':', '', $entry['end']) }}">
                            <i class="fas fa-calendar-check mr-2"></i>録音予約
                        </button>
                        @else
                        <a href="{{ route('login') }}"
                           class="touch-target w-full text-center bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-semibold py-3 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                            <i class="fas fa-sign-in-alt mr-2"></i>ログインして録音予約
                        </a>
                        @endif
                    @elseif($canRecord && $isPast)
                        <!-- 過去7日以内：タイムフリー録音 -->
                        <div class="recording-controls-wrapper">
                            <div class="flex flex-col space-y-2 recording-btn-wrapper">
                                <select class="form-select form-select-sm area-select bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm" 
                                        data-entry-id="{{ $entry['id'] }}">
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
                                <button class="touch-target w-full bg-gradient-to-r from-green-500 to-green-700 text-white font-semibold py-3 rounded-lg hover:shadow-lg transition recording-btn"
                                        data-station-id="{{ $entry['id'] }}"
                                        data-station-name="{{ $station_id }}"
                                        data-title="{{ $entry['title'] }}"
                                        data-cast="{{ $entry['cast'] ?? '' }}"
                                        data-date="{{ $entry['date'] }}"
                                        data-start="{{ str_replace(':', '', $entry['start']) }}"
                                        data-end="{{ str_replace(':', '', $entry['end']) }}">
                                    <i class="fas fa-download mr-2"></i>タイムフリー録音
                                </button>
                            </div>
                            <div class="recording-status" style="display:none; margin-top:0.5rem;">
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-5 mb-2">
                                    <div class="progress-bar bg-gradient-to-r from-green-500 to-green-600 h-5 rounded-full text-xs font-medium text-white text-center leading-5 transition-all duration-300"
                                         style="width: 0%">0%</div>
                                </div>
                                <small class="block text-gray-600 dark:text-gray-400 text-xs mb-2 recording-info">
                                    サイズ: <span class="file-size">0 MB</span> |
                                    時間: <span class="elapsed-time">00:00</span> / <span class="total-time">--:--</span>
                                </small>
                                <button class="w-full touch-target bg-red-500 hover:bg-red-600 text-white font-semibold py-2 rounded-lg transition stop-recording-btn">
                                    <i class="fas fa-stop mr-2"></i>録音停止
                                </button>
                            </div>
                        </div>
                    @endif

                    <a href="{{ url('list/' . $entry['id'] . '/' . $entry['title']) }}?from=weekly&station_id={{ $station_id }}&date={{ $entryDate }}"
                       class="touch-target w-full text-center border-2 border-primary-500 text-primary-600 dark:text-primary-400 font-semibold py-3 rounded-lg hover:bg-primary-50 dark:hover:bg-primary-900/20 transition">
                        <i class="fas fa-info-circle mr-2"></i>詳細を見る
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    @endforeach
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

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>予約中...';

            const currentButton = this;

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
                    currentButton.innerHTML = '<i class="fas fa-check mr-2"></i>予約完了';
                    currentButton.classList.remove('from-yellow-400', 'to-yellow-600', 'text-gray-800');
                    currentButton.classList.add('bg-gray-400', 'text-white', 'cursor-not-allowed');
                    alert('録音予約が完了しました');
                } else {
                    currentButton.disabled = false;
                    currentButton.innerHTML = '<i class="fas fa-calendar-check mr-2"></i>録音予約';
                    alert('録音予約に失敗しました: ' + data.message);
                }
            })
            .catch(error => {
                currentButton.disabled = false;
                currentButton.innerHTML = '<i class="fas fa-calendar-check mr-2"></i>録音予約';
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

            const wrapper = this.closest('.recording-controls-wrapper');
            const areaSelect = wrapper ? wrapper.querySelector('.area-select[data-entry-id="' + stationId + '"]') : null;
            const areaId = areaSelect ? areaSelect.value : '';

            const startMinutes = parseInt(startTime.substring(0, 2)) * 60 + parseInt(startTime.substring(2, 4));
            const endMinutes = parseInt(endTime.substring(0, 2)) * 60 + parseInt(endTime.substring(2, 4));
            const durationMinutes = endMinutes - startMinutes;

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>録音開始中...';

            const currentButton = this;

            const requestBody = {
                station_id: stationId,
                station_name: stationName,
                title: title,
                cast: cast,
                start_time: date + startTime,
                end_time: date + endTime
            };

            if (areaId) {
                requestBody.area_id = areaId;
            }

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
                    const btnWrapper = currentButton.closest('.recording-btn-wrapper');
                    if (btnWrapper) {
                        btnWrapper.style.display = 'none';
                    }

                    const statusDiv = currentButton.closest('.recording-controls-wrapper').querySelector('.recording-status');

                    if (statusDiv) {
                        statusDiv.style.display = 'block';

                        const stopBtn = statusDiv.querySelector('.stop-recording-btn');
                        if (stopBtn) {
                            stopBtn.onclick = function() {
                                window.stopRecording(data.recording_id, currentButton, statusDiv);
                            };
                        }

                        window.startRecordingMonitor(data.recording_id, currentButton, data.filename, statusDiv, durationMinutes);
                    }
                } else {
                    const btnWrapper = currentButton.closest('.recording-btn-wrapper');
                    if (btnWrapper) {
                        btnWrapper.style.display = 'flex';
                    }
                    currentButton.disabled = false;
                    currentButton.innerHTML = '<i class="fas fa-download mr-2"></i>タイムフリー録音';
                    alert('録音開始に失敗しました: ' + data.message);
                }
            })
            .catch(error => {
                const btnWrapper = currentButton.closest('.recording-btn-wrapper');
                if (btnWrapper) {
                    btnWrapper.style.display = 'flex';
                }
                currentButton.disabled = false;
                currentButton.innerHTML = '<i class="fas fa-download mr-2"></i>タイムフリー録音';
                alert('エラーが発生しました: ' + error);
            });
        });
    });
});

// 録音ファイルをダウンロード（週間番組表用：共通モジュールを使用）
async function downloadRecording(recordingId) {
    const filename = window.getFilenameFromRecordingId(recordingId);
    await window.downloadRecording(recordingId, filename);
}
</script>
@endsection
