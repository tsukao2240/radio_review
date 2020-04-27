@extends('layouts.home')
@section('content')
<div class="d-flex flex-column align-items-center">
    <br>
    <h2>レビューを投稿する</h2>
    <div>
        {{ csrf_field() }}
        {{ Form::open(['route' => ['store',$id],'method' => 'POST','action' => 'PostCommentsController@store']) }}
        <div class="form-group">
            {{ Form::hidden('user_id',1) }}
        </div>
        <div class="form-group">
            {{ Form::hidden('program_id',$id) }}
        </div>
        <div class="form-group">
            {{ Form::label('title','タイトル') }}
            {{ Form::text('title',null,['class' => 'form-control']) }}
        </div>
        <div class="form-group">
            {{ Form::label('body','内容') }}
            {{ Form::textarea('body',null,['class' => 'form-control','rows' => 10]) }}
        </div>
        <div class="form-group">
            {{ Form::submit('投稿する',['class' => 'btn btn-primary']) }}
        </div>
        {{ Form::close() }}
    </div>
</div>
@endsection
