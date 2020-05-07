@extends('layouts.header')
@section('content')

@if (Session::has('message'))
<div id="app">
    <toast-component message="{{ session('message') }}" type="success"></toast-component>
</div>
@endif
<title>レビュー投稿 | {{ $program_title }}</title>


<div class="d-flex flex-column align-items-center">
    <br>
    <h3>{{ $program_title }}</h3>
    <div>
        {{ csrf_field() }}
        {{ Form::open(['route' => ['store',$program_id],'method' => 'POST','action' => 'PostController@store']) }}
        <div class="form-group">
            {{ Form::hidden('user_id',$user_id) }}
        </div>
        <div class="form-group">
            {{ Form::hidden('program_id',$program_id) }}
        </div>
        <div class="form-group">
            {{ Form::hidden('program_title',$program_title) }}
        </div>
        <div class="form-group">
            {{ Form::label('title','タイトル') }}
            {{ Form::text('title',null,['class' => 'form-control']) }}
            @if ($errors->has('title'))
            <span class="text-danger">{{ $errors->first('title')}}</span>
            @endif
        </div>
        <div class="form-group">
            {{ Form::label('body','内容') }}
            {{ Form::textarea('body',null,['class' => 'form-control','rows' => 10]) }}
            @if ($errors->has('body'))
            <span class="text-danger">{{ $errors->first('body')}}</span>
            @endif
        </div>
        <div class="form-group">
            {{ Form::submit('投稿する',['class' => 'btn btn-primary']) }}
        </div>
        {{ Form::close() }}
    </div>
</div>
@endsection
