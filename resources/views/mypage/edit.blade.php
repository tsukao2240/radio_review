@extends('layouts.header')
@section('content')

<div class="d-flex flex-column align-items-center">
    <br>
    <h3 class="caption">レビューの編集</h3>
    <div>
        {{ csrf_field() }}
        {{ Form::open(['route' => ['myreview.update',$post->id],'method' => 'POST','action' => 'MypageController@update']) }}
        <div class="form-group">
            {{ Form::hidden('id',$post->id) }}
        </div>
        <div class="form-group">
            {{ Form::hidden('user_id',$post->user_id) }}
        </div>
        <div class="form-group">
            {{ Form::hidden('program_id',$post->program_id) }}
        </div>
        <div class="form-group">
            {{ Form::hidden('program_title',$post->program_title) }}
        </div>
        <div class="form-group">
            {{ Form::label('title','タイトル') }}
            {{ Form::text('title',$post->title,['class' => 'form-control']) }}
            @if ($errors->has('title'))
            <span class="text-danger">{{ $errors->first('title')}}</span>
            @endif
        </div>
        <div class="form-group">
            {{ Form::label('body','内容') }}
            {{ Form::textarea('body',$post->body,['class' => 'form-control','rows' => 10]) }}
            @if ($errors->has('body'))
            <span class="text-danger">{{ $errors->first('body')}}</span>
            @endif
        </div>
        <div class="form-group">
            {{ Form::submit('編集する',['class' => 'btn btn-primary']) }}
        </div>
        {{ Form::close() }}
    </div>
</div>
@endsection
