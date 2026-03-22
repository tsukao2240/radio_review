@extends('layouts.header')
@section('content')

<title>番組検索</title>

<div class="max-w-7xl mx-auto">
    @if(request('title'))
        <x-breadcrumbs :items="[
            ['label' => '番組検索', 'url' => route('program.search')],
            ['label' => '検索結果: ' . request('title')]
        ]" />
    @else
        <x-breadcrumbs :items="[
            ['label' => '番組検索']
        ]" />
    @endif
    
    <div class="text-center mb-8">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-800 dark:text-white mb-3">
            <i class="fas fa-search mr-2"></i>番組検索
        </h1>
        <p class="text-gray-600 dark:text-gray-400">番組名で検索できます</p>
    </div>

    <!-- 検索フォーム -->
    <div class="mb-12">
        <div class="relative w-full max-w-2xl mx-auto">
            <form method="GET" action="{{ route('program.search') }}" class="relative">
                <div class="flex items-center bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden border-2 border-transparent focus-within:border-primary-500 transition-all">
                    <input
                        type="text"
                        name="title"
                        value="{{ request('title') }}"
                        placeholder="番組名を入力してください"
                        class="flex-1 px-4 py-3 md:px-6 md:py-4 text-base md:text-lg focus:outline-none bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 border-0"
                        autocomplete="off"
                        autofocus
                    />
                    <button
                        type="submit"
                        class="touch-target px-6 md:px-8 bg-primary-500 hover:bg-primary-600 text-white font-semibold transition">
                        <i class="fas fa-search"></i>
                        <span class="ml-2 hidden md:inline">検索</span>
                    </button>
                </div>
            </form>
            <!-- オートコンプリート候補がここに動的に追加されます -->
            <div id="autocomplete-suggestions" class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 max-h-96 overflow-y-auto" style="display: none;"></div>
        </div>
    </div>

    <!-- 検索フィルタ（検索後に表示） -->
    @if(request('title'))
    <div class="card-base mb-6">
        <h3 class="font-semibold text-gray-800 dark:text-white mb-4 flex items-center">
            <i class="fas fa-filter mr-2"></i>フィルター
        </h3>
        <form method="GET" action="{{ route('program.search') }}" class="space-y-4">
            <input type="hidden" name="title" value="{{ request('title') }}">

            <!-- 放送局選択 -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    放送局
                </label>
                <div class="space-y-3">
                    <!-- 関東エリア -->
                    <div>
                        <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">関東</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach([
                                'TBS' => 'TBSラジオ',
                                'QRR' => '文化放送',
                                'LFR' => 'ニッポン放送',
                                'RN1' => 'ラジオNIKKEI第1',
                                'RN2' => 'ラジオNIKKEI第2',
                                'JOAK' => 'NHKラジオ第1',
                                'JOAB' => 'NHK-FM',
                                'FMT' => 'TOKYO FM',
                                'FMJ' => 'J-WAVE',
                                'INT' => 'interfm'
                            ] as $stationId => $stationName)
                            <label class="inline-flex items-center touch-target px-3 py-2 rounded-lg border-2 cursor-pointer transition text-sm
                                {{ in_array($stationId, (array)request('stations', [])) ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' : 'border-gray-300 dark:border-gray-600 hover:border-primary-300 text-gray-700 dark:text-gray-300' }}">
                                <input
                                    type="checkbox"
                                    name="stations[]"
                                    value="{{ $stationId }}"
                                    {{ in_array($stationId, (array)request('stations', [])) ? 'checked' : '' }}
                                    class="mr-2 text-primary-500 focus:ring-primary-500"
                                >
                                <span class="text-xs md:text-sm font-medium">{{ $stationName }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- 関西エリア -->
                    <div>
                        <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">関西</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach([
                                'MBS' => 'MBSラジオ',
                                'ABC' => 'ABCラジオ',
                                'OBC' => 'ラジオ大阪',
                                'CCL' => 'FM COCOLO',
                                'FM802' => 'FM802',
                                'FMO' => 'FM OSAKA'
                            ] as $stationId => $stationName)
                            <label class="inline-flex items-center touch-target px-3 py-2 rounded-lg border-2 cursor-pointer transition text-sm
                                {{ in_array($stationId, (array)request('stations', [])) ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' : 'border-gray-300 dark:border-gray-600 hover:border-primary-300 text-gray-700 dark:text-gray-300' }}">
                                <input
                                    type="checkbox"
                                    name="stations[]"
                                    value="{{ $stationId }}"
                                    {{ in_array($stationId, (array)request('stations', [])) ? 'checked' : '' }}
                                    class="mr-2 text-primary-500 focus:ring-primary-500"
                                >
                                <span class="text-xs md:text-sm font-medium">{{ $stationName }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- 中京エリア -->
                    <div>
                        <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">中京</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach([
                                'CBC' => 'CBCラジオ',
                                'SF' => '東海ラジオ',
                                'FMNA' => 'FM AICHI'
                            ] as $stationId => $stationName)
                            <label class="inline-flex items-center touch-target px-3 py-2 rounded-lg border-2 cursor-pointer transition text-sm
                                {{ in_array($stationId, (array)request('stations', [])) ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' : 'border-gray-300 dark:border-gray-600 hover:border-primary-300 text-gray-700 dark:text-gray-300' }}">
                                <input
                                    type="checkbox"
                                    name="stations[]"
                                    value="{{ $stationId }}"
                                    {{ in_array($stationId, (array)request('stations', [])) ? 'checked' : '' }}
                                    class="mr-2 text-primary-500 focus:ring-primary-500"
                                >
                                <span class="text-xs md:text-sm font-medium">{{ $stationName }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- その他主要都市 -->
                    <div>
                        <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-2">その他</h4>
                        <div class="flex flex-wrap gap-2">
                            @foreach([
                                'HBC' => 'HBCラジオ',
                                'RKK' => 'RKKラジオ',
                                'RBC' => 'RBCiラジオ',
                                'FMF' => 'FM福岡'
                            ] as $stationId => $stationName)
                            <label class="inline-flex items-center touch-target px-3 py-2 rounded-lg border-2 cursor-pointer transition text-sm
                                {{ in_array($stationId, (array)request('stations', [])) ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' : 'border-gray-300 dark:border-gray-600 hover:border-primary-300 text-gray-700 dark:text-gray-300' }}">
                                <input
                                    type="checkbox"
                                    name="stations[]"
                                    value="{{ $stationId }}"
                                    {{ in_array($stationId, (array)request('stations', [])) ? 'checked' : '' }}
                                    class="mr-2 text-primary-500 focus:ring-primary-500"
                                >
                                <span class="text-xs md:text-sm font-medium">{{ $stationName }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- 除外オプション -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    除外設定
                </label>
                <div class="space-y-2">
                    <label class="flex items-center touch-target cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg px-2 py-1 transition">
                        <input
                            type="checkbox"
                            name="exclude_new"
                            value="1"
                            {{ request('exclude_new') ? 'checked' : '' }}
                            class="mr-3 text-primary-500 focus:ring-primary-500"
                        >
                        <span class="text-sm text-gray-700 dark:text-gray-300">新番組を除外（【新】タグ）</span>
                    </label>
                    <label class="flex items-center touch-target cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg px-2 py-1 transition">
                        <input
                            type="checkbox"
                            name="exclude_final"
                            value="1"
                            {{ request('exclude_final') ? 'checked' : '' }}
                            class="mr-3 text-primary-500 focus:ring-primary-500"
                        >
                        <span class="text-sm text-gray-700 dark:text-gray-300">終了番組を除外（【終】【最終回】タグ）</span>
                    </label>
                    <label class="flex items-center touch-target cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg px-2 py-1 transition">
                        <input
                            type="checkbox"
                            name="exclude_rerun"
                            value="1"
                            {{ request('exclude_rerun') ? 'checked' : '' }}
                            class="mr-3 text-primary-500 focus:ring-primary-500"
                        >
                        <span class="text-sm text-gray-700 dark:text-gray-300">再放送を除外（（再）タグ）</span>
                    </label>
                </div>
            </div>

            <button
                type="submit"
                class="w-full touch-target bg-primary-500 hover:bg-primary-600 text-white font-semibold py-3 rounded-lg transition">
                <i class="fas fa-sync-alt mr-2"></i>フィルターを適用
            </button>
        </form>
    </div>
    @endif

    <!-- 検索結果 -->
    @if(isset($programs))
        @if($programs->total() > 0)
        <div class="mb-6">
            <h2 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white mb-4">
                検索結果（{{ $programs->total() }}件）
            </h2>
            
            <!-- 番組カードグリッド -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($programs as $item)
                <div class="card-base group hover:scale-105 transition-transform duration-200">
                    <!-- 放送局バッジ -->
                    <div class="mb-3">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300">
                            <i class="fas fa-broadcast-tower mr-1"></i>{{ $item->station_id }}
                        </span>
                    </div>

                    <!-- 番組名 -->
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-2 group-hover:text-primary-600 transition">
                        <a href="{{ route('program.detail', ['station_id' => $item->station_id, 'title' => $item->title, 'from' => 'search', 'keyword' => request('title')]) }}"
                           class="hover:underline">
                            {{ $item->title }}
                        </a>
                    </h3>

                    <!-- 出演者 -->
                    @if(!empty($item->cast))
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        <i class="fas fa-microphone mr-1"></i>{{ $item->cast }}
                    </p>
                    @endif

                    <!-- アクション -->
                    <a href="{{ route('program.detail', ['station_id' => $item->station_id, 'title' => $item->title, 'from' => 'search', 'keyword' => request('title')]) }}"
                       class="touch-target w-full block text-center border-2 border-primary-500 text-primary-600 dark:text-primary-400 font-semibold py-3 rounded-lg hover:bg-primary-50 dark:hover:bg-primary-900/20 transition">
                        <i class="fas fa-info-circle mr-2"></i>詳細を見る
                    </a>
                </div>
                @endforeach
            </div>

            <!-- ページネーション -->
            <div class="mt-8">
                {{ $programs->appends(request()->query())->links('vendor.pagination.custom') }}
            </div>
        </div>
        @else
        <div class="card-base text-center py-12">
            <i class="fas fa-search text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
            <p class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-2">
                「{{ request('title') }}」に一致する番組が見つかりませんでした
            </p>
            <p class="text-gray-600 dark:text-gray-400">別のキーワードで検索してみてください</p>
        </div>
        @endif
    @endif
</div>

<!-- オートコンプリート機能のJavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.querySelector('input[name="title"]');
    const suggestionsDiv = document.getElementById('autocomplete-suggestions');
    let debounceTimer;

    if (!searchInput || !suggestionsDiv) return;

    searchInput.addEventListener('input', function(e) {
        const query = e.target.value;

        clearTimeout(debounceTimer);

        if (query.length < 2) {
            suggestionsDiv.style.display = 'none';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetchSuggestions(query);
        }, 300);
    });

    async function fetchSuggestions(query) {
        try {
            const response = await fetch(`/api/programs/suggest?q=${encodeURIComponent(query)}`);
            const data = await response.json();

            if (data.suggestions && data.suggestions.length > 0) {
                displaySuggestions(data.suggestions);
            } else {
                suggestionsDiv.style.display = 'none';
            }
        } catch (error) {
            console.error('Failed to fetch suggestions:', error);
            suggestionsDiv.style.display = 'none';
        }
    }

    function displaySuggestions(suggestions) {
        suggestionsDiv.innerHTML = '';
        
        suggestions.forEach(suggestion => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'w-full text-left px-4 py-3 hover:bg-gray-100 dark:hover:bg-gray-700 transition border-b border-gray-100 dark:border-gray-700 last:border-b-0';
            
            let html = `<div class="font-semibold text-gray-800 dark:text-white">${suggestion.title}</div>`;
            
            if (suggestion.cast) {
                html += `<div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    <i class="fas fa-microphone mr-1"></i>${suggestion.cast}
                </div>`;
            }
            
            if (suggestion.station_id) {
                html += `<div class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                    <i class="fas fa-broadcast-tower mr-1"></i>${suggestion.station_id}
                </div>`;
            }
            
            button.innerHTML = html;
            
            button.addEventListener('click', () => {
                searchInput.value = suggestion.title;
                suggestionsDiv.style.display = 'none';
                searchInput.form.submit();
            });
            
            suggestionsDiv.appendChild(button);
        });
        
        suggestionsDiv.style.display = 'block';
    }

    // 外側クリックで候補を閉じる
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
            suggestionsDiv.style.display = 'none';
        }
    });
});
</script>

@endsection
