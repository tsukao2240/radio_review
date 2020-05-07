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
        <thead>
            <tr>
                <th>放送局</th>
                <th>番組名</th>
                <th style="width:25%">出演者</th>
                <th>放送時間</th>
                <th style="white-space: nowrap">ホームページ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results as $result)
            <tr>
                <td><a href="{{ url('station/' . $result['station_id']) }}">{{$result['station_id']}}</a></td>
                <td>
                    <a
                        href="{{ url('list/' . $result['station_id'] . '/' . $result['title'])}}">{{$result['title']}}</a>
                </td>
                <td style="width:25%">{{ $result['cast'] }}</td>
                <td style="white-space: nowrap">{{$result['start'] . ' ' . '-' . ' '. $result['end']}}</td>
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
@endsection
