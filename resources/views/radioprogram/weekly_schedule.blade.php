@extends('layouts.home')
@section('content')

<head>
    <title>本日の番組表</title>
</head>

<body>
    <table class="table table-bordered table-responsive">
        <thead>
            <tr>
                <th>{{ date('m月d日',strtotime($thisWeek[0])) }}</th>
            </tr>
        </thead>
        <tbody>
            @for($i = 0;$i < count($thisWeek);$i++) @foreach ($entries as $entry) @if ($thisWeek[$i]===$entry['date'])
                <tr>
                <td>
                    <div>
                        <a href="{{ url('list/' . $entry['id'] . '/' . $entry['title'])}}">{{$entry['title']}}</a>
                    </div>
                    <div>
                        {{ $entry['cast'] }}
                    </div>
                    <div>
                        {{ $entry['start'] . ' ' . '-' . ' '. $entry['end'] }}
                    </div>
                </td>
                </tr>
                @elseif(intval($thisWeek[$i]) + 1 === intval($entry['date']) && intval($entry['start']) >= 24)
                <tr>
                    <td>
                        <div>
                            <h6><a
                                    href="{{ url('list/' . $entry['id'] . '/' . $entry['title'])}}">{{$entry['title']}}</a>
                            </h6>
                        </div>
                        <div>
                            <h6>{{ $entry['cast'] }}</h6>
                        </div>
                        <div>
                            <h6>{{ $entry['start'] . ' ' . '-' . ' '. $entry['end'] }}</h6>
                        </div>
                    </td>
                </tr>
                @else
                {{-- @for ($count = 0; $count < $loop->index; $count++)
                    @php unset($entries[$count]) @endphp
                    @endfor --}}
                @break
                @endif
                @endforeach
                @endfor
        </tbody>

    </table>
</body>

@endsection
