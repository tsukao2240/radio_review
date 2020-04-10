@extends('layouts.home')
@section('content')
<body>
    {{ Breadcrumbs::render('Search') }}
    @if(!empty($existResult))
        <div class="container">
            <div class="row border-bottom text-center">
                <h2>検索結果</h2>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>番組名</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- TODO  同じ番組を表示させないようにする--}}
                    @foreach ($keyword as $item)
                        @php
                            $zenToHan = mb_convert_kana($item->title,'a');
                            $zenToHan = str_replace(array(" ", "　","\n"), "", $zenToHan);
                        @endphp
                        <tr>
                            <td>{{ $zenToHan }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $keyword->links() }}
        </div>
    @else
        <div class="container">
            <h2>番組名が一致しませんでした</h2>
        </div>
    @endif
</body>
@endsection

