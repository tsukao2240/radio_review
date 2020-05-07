@extends('layouts.header')
@section('content')
<title>
    {{ Auth::user()->name }}さんが投稿したレビュー
</title>
@if (Session::has('message'))
<div id="app">
    <toast-component message="{{ session('message') }}" type="success"></toast-component>
</div>
@endif
@if (!$posts->isEmpty())

<span>
    {{ Breadcrumbs::render('my_review') }}
</span>
<h3 style="text-align:center">{{ Auth::user()->name }}さんが投稿したレビュー</h3>
<br>
@foreach ($posts as $post)
<div class="card">
    <div class="card-header"><a
            href="{{ route('detail',['station_id' => $post->station_id,'title' => $post->program_title]) }}">{{ $post->program_title }}</a>
        <div class="float-right">
            <div class="btn-toolbar">
                <div class="btn-group">
                    <div class="form-group">
                        {{ Form::hidden('program_id',$post->program_id) }}
                    </div>
                    <a href="{{ route('myreview_edit',$post->id) }}"><button class="btn btn-primary">編集</button></a>
                    {{ csrf_field() }}
                    {{ Form::open(['route' => ['myreview_delete'],'method' => 'POST']) }}
                    {{ Form::hidden('id',$post->id) }}
                    {{ Form::submit('削除',['class' => 'btn btn-danger']) }}
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        {{ $post->title }}
    </div>
    <div class="card-body">
        {{ $post->body }}
    </div>
</div>
@endforeach
@else

<h3 style="text-align:center">
    まだ投稿がありません
</h3>
@endif
@endsection
