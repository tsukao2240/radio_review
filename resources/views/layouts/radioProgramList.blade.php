@extends('layouts.home')
@section('content')
<body>
    <span>
        {{ Breadcrumbs::render('radioProgramList') }}
    </span>
    <h2>放送中の番組</h2>
    <div>
        <table class="table table-bordered table-responsive">
            <thead>
                <tr>
                    <th>放送局</th>
                    <th>番組名</th>
                    <th style="width:25%">出演者</th>
                    <th>放送時間</th>
                    <th>ホームページ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results as $result)
                    <tr>
                        <td>{{$result['station']}}</td>
                        <td>{{$result['title']}}</td>
                        <td style="width:25%">{{ $result['cast'] }}</td>
                        <td>{{$result['start'] . ' ' . '-' . ' '. $result['end']}}</td>
                        {{-- 番組ホームページのURLが取得できない番組があるため場合分け --}}
                        @if ($result['url'] !== '')
                            <a href="http://"></a>
                            <td><a href="{{$result['url']}}">公式HP</a></td>
                        @else
                            <td></td>
                        @endif
                    </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</body>
@endsection
