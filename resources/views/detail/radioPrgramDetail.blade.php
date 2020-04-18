@extends('layouts.home')
@section('content')

<head>
    @foreach ($entries as $entry)
    <title>{{ $entry['title'] }}</title>
    @endforeach
</head>

<body>
    {{ Breadcrumbs::render('list/{title}') }}
    @foreach ($entries as $entry)
    <div class="d-flex justify-content-between">
        <section>
            <h2>{{ $entry['title'] }}</h2>
            <h4>{{ $entry['cast'] }}</h4>
            @if (!empty($entry['image']))
            <div>
                <img src="{!! $entry['image'] !!}">
            </div>
            @endif
            @if (!empty($entry['info']))
            <div>
                {!! $entry['info'] !!}
            </div>
            @endif
        </section>
        <section>
            <h4>レビューはこちら</h4>
            {{ Form::textarea('review',null,['class' => 'form-control','rows' => 10,'placeholder' => 'レビューを書き込んでください！']) }}
        </section>
    </div>
    @endforeach
</body>
@endsection
