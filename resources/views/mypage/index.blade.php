@extends('layouts.header')
@section('content')
<title>
    {{ Auth::user()->name }}さんが投稿したレビュー
</title>
@include('includes.search')
@if (Session::has('message'))
<div id="app">
    <toast-component message="{{ session('message') }}" type="success"></toast-component>
</div>
@endif
@if (!$posts->isEmpty())
<span>
    {{ Breadcrumbs::render('my_review') }}
</span>
<h3 class="caption">{{ Auth::user()->name }}さんが投稿したレビュー</h3>
<br>
@foreach ($posts as $post)
<div id="reviews">
    <div class="card">
        <div class="card-header"><a
                href="{{ route('program.detail',['station_id' => $post->station_id,'title' => $post->program_title]) }}">{{ $post->program_title }}</a>
            <div class="float-right">
                <div class="btn-toolbar">
                    <div class="btn-group">
                        <div class="form-group">
                            {{ Form::hidden('program_id',$post->program_id) }}
                        </div>
                        <a href="{{ route('myreview.edit',$post->id) }}"><button class="btn btn-primary">編集</button></a>
                        {{ csrf_field() }}
                        {{ Form::open(['route' => ['myreview.delete'],'method' => 'POST']) }}
                        {{ Form::hidden('id',$post->id) }}
                        {{ Form::submit('削除',['class' => 'btn btn-danger']) }}
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body card_review_body">
            <dt>
                {{ $post->title }}
            </dt>
            <dd>
                {{ $post->body }}
            </dd>
        </div>
        <div class="card-footer card_review_footer">
            <span class="review_meta">
                投稿日:{{ date('Y年m月d日',strtotime($post->created_at)) }}
            </span>
        </div>

    </div>
</div>
@endforeach
{{ $posts->links() }}
@else

<h3 class="caption">
    まだ投稿がありません
</h3>
@endif
@endsection
