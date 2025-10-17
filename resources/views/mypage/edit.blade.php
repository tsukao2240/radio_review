@extends('layouts.header')
@section('content')

<div class="d-flex flex-column align-items-center">
    <br>
    <h3 class="caption">レビューの編集</h3>
    <div>
        <form method="POST" action="{{ route('myreview.update', $post->id) }}">
            @csrf
        <div class="form-group">
            <input type="hidden" name="id" value="{{ $post->id }}">
        </div>
        <div class="form-group">
            <input type="hidden" name="user_id" value="{{ $post->user_id }}">
        </div>
        <div class="form-group">
            <input type="hidden" name="program_id" value="{{ $post->program_id }}">
        </div>
        <div class="form-group">
            <input type="hidden" name="program_title" value="{{ $post->program_title }}">
        </div>
        <div class="form-group">
            <label for="title">タイトル</label>
            <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $post->title) }}">
            @if ($errors->has('title'))
            <span class="text-danger">{{ $errors->first('title')}}</span>
            @endif
        </div>
        <div class="form-group">
            <label for="body">内容</label>
            <textarea name="body" id="body" class="form-control" rows="10">{{ old('body', $post->body) }}</textarea>
            @if ($errors->has('body'))
            <span class="text-danger">{{ $errors->first('body')}}</span>
            @endif
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">編集する</button>
        </div>
        </form>
    </div>
</div>
@endsection
