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
    <!--APIからデータが取得できた場合-->
    @if(isset($entries))
    <div class="d-flex justify-content-sm-around">
        <div class="col-lg-4">
            @foreach ($entries as $entry)
            <section>
                <h3>{{ $entry['title'] }}</h3>
                <h5>{{ $entry['cast'] }}</h5>
                @if (!empty($entry['image']))
                <div>
                    <img src="{!! $entry['image'] !!}">
                </div>
                @endif
                @if(!empty($entry['desc']))
                <div>
                    {!! $entry['desc'] !!}
                </div>
                @endif
                @if (!empty($entry['info']))
                <br>
                <div>
                    {!! $entry['info'] !!}
                </div>
                @endif
                <br>
                @include('layouts.post_create',['id' => $id])
            </section>
            @endforeach
        </div>
    </div>
    <!--APIから取得できず、DBからデータを取得した場合-->
    @elseif(isset($results))
    <div class="d-flex justify-content-sm-around">
        <div class="col-md-4">
            @foreach ($results as $result)
            <section>
                <h3>{{ $result->title }}</h3>
                <h5>{{ $result->cast }}</h5>
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
                <br>
                @include('layouts.post_create',['id' => $result->id])
            </section>
            @endforeach
        </div>
    </div>
    </section>
    @endif
</body>
@endsection
