@extends('layouts.header')
@section('content')
<span>
    {{ Breadcrumbs::render('weekly_schedule') }}
</span>
<title>{{ $broadcast_name }}の週間番組表</title>
@include('includes.search')
<h3 style="text-align:center">週間番組表({{ $broadcast_name }})</h3>
<div class="timetable">
    @for ($i = 0; $i < count($thisWeek) - 1; $i++) <div class="tablebox">
        <div class="table">
            <table class="table table-bordered table-responsive">
                <thead>
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
                        </td>
                        </tr>
                        @elseif(intval($thisWeek[$i]) + 1 === intval($entry['date']) && intval($entry['start']) >= 24)
                        <tr>
                            <td>
                                <a
                                    href="{{ url('list/' . $entry['id'] . '/' . $entry['title'])}}">{{$entry['title']}}</a>
                                    @if ($entry['cast'] !== '')
                                    <br>
                                    {{ $entry['cast'] }}
                                     @endif
                                <br>
                                {{ $entry['start'] . ' ' . '-' . ' '. $entry['end'] }}
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
@endsection
<style>
    .tablebox {
        width: 217px;
        float: left;
    }

    .timetable {
        clear: both;
    }
    .table{

    }

</style>
