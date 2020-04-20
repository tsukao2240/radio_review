@extends('layouts.home')
@section('content')

<head>
    @if(isset($entries))
    @foreach ($entries as $entry)
    <title>{{ $entry['title'] }}</title>
    @endforeach
    @elseif(isset($results))
    @foreach ($results as $result)
    <title>{{ $result->title }}</title>
    @endforeach
    @endif
</head>

<body>
    {{ Breadcrumbs::render('list/{title}') }}
    <div class="d-flex justify-content-between">
        @if(isset($entries))
        @foreach ($entries as $entry)
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
        @endforeach
        @elseif(isset($results))
        @foreach ($results as $result)
        <section>
            <h2>{{ $result->title }}</h2>
            <h4>{{ $result->cast }}</h4>
            @if (!empty($result->image))
            <div>
                <img src="{!! $result->image !!}">
            </div>
            @endif
            @if (!empty($result->info))
            <div>
                {!! $result->info !!}
            </div>
            @endif
        </section>
        @endforeach
        @endif
        <section>
            <h4>レビューはこちら</h4>
            {{-- {{ Form::open(['method' => 'POST','action' => 'PostCommentsController@post']) }}
            <div class="form-group">
                {{ Form::textarea('review',null,['class' => 'form-control','rows' => 10,'placeholder' => 'レビューを書き込んでください！']) }}
            </div>
            <div class="form-group">
                {{ Form::submit('レビューを投稿',['class' => 'btn btn-dark']) }}
            </div>
            {{ Form::close() }} --}}
            <h4>レビュー一覧</h4>
        </section>
    </div>
</body>
@endsection
