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
    <h3 class="caption">{{ $program_title }}</h3>
    <div>
        <form method="POST" action="{{ route('post.store', $program_id) }}">
            @csrf
        <div class="form-group">
            <input type="hidden" name="user_id" value="{{ $user_id }}">
        </div>
        <div class="form-group">
            <input type="hidden" name="program_id" value="{{ $program_id }}">
        </div>
        <div class="form-group">
            <input type="hidden" name="program_title" value="{{ $program_title }}">
        </div>
        <div class="form-group">
            <label for="title">タイトル</label>
            <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}">
            @if ($errors->has('title'))
            <span class="text-danger">{{ $errors->first('title')}}</span>
            @endif
        </div>
        <div class="form-group">
            <label for="body">内容</label>
            <textarea name="body" id="body" class="form-control" rows="10">{{ old('body') }}</textarea>
            @if ($errors->has('body'))
            <span class="text-danger">{{ $errors->first('body')}}</span>
            @endif
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">投稿する</button>
        </div>
        </form>
    </div>
</div>
@endsection

