@extends('layouts.home')
@section('content')

<head>
    <title>放送中の番組</title>
</head>

<body>
    <span>
        {{ Breadcrumbs::render('radioProgramGuide') }}
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
                    <td><a href="{{ url('station/' . $result['id']) }}">{{$result['station']}}</a></td>
                    <td>
                        <a href="{{ url('list/' . $result['id'] . '/' . $result['title'])}}">{{$result['title']}}</a>
                    </td>
                    <td style="width:25%">{{ $result['cast'] }}</td>
                    <td>{{$result['start'] . ' ' . '-' . ' '. $result['end']}}</td>
                    {{-- 番組ホームページのURLがAPIに記述されていない番組があるため場合分け --}}
                    @if ($result['url'] !== '')
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
