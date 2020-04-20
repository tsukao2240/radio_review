@extends('layouts.home')
@section('content')

<head>
    <title>レビューの投稿</title>
</head>
{{ csrf_field() }}
{{ Form::open(['route' => 'search','method' => 'get']) }}
<div class="input-group">
    {{ Form::text('title','',['class' => 'form-control','placeholder' => '番組名を検索する']) }}
    <div class="input-group-append">
        {{ Form::button('<i class="fas fa-search"></i>',['class' => 'btn','type' => 'submit']) }}
    </div>
</div>
{{ Form::close() }}
<div class="main">
    <h4>番組一覧（{{ $results->total() }}件）</h4>
</div>
<div class="card">
    @foreach ($results as $result)
    <div class="card-header">{{ $result->title }}
    </div>
    <div class="card-body">
        {{ $result->cast }}
    </div>
    @endforeach
</div>
{{ $results->render() }}
@endsection
