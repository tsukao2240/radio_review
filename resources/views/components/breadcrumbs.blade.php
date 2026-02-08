{{--
    パンくずリストコンポーネント
    
    使用例:
    <x-breadcrumbs :items="[
        ['label' => '放送中の番組', 'url' => route('program.schedule')],
        ['label' => '週間番組表']
    ]" />
--}}

@props(['items'])

<nav aria-label="パンくずリスト" class="mb-6">
    <ol class="flex flex-wrap items-center space-x-2 text-sm md:text-base">
        <li>
            <a href="/" class="text-primary-600 hover:text-primary-700 transition">
                <i class="fas fa-home mr-1"></i>ホーム
            </a>
        </li>
        @foreach($items as $item)
        <li class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-2 text-xs"></i>
            @if(isset($item['url']))
            <a href="{{ $item['url'] }}" class="text-primary-600 hover:text-primary-700 transition">
                {{ $item['label'] }}
            </a>
            @else
            <span class="text-gray-700 dark:text-gray-300">{{ $item['label'] }}</span>
            @endif
        </li>
        @endforeach
    </ol>
</nav>
