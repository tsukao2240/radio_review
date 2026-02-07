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
            <label for="rating">評価</label>
            <div id="rating-container"></div>
            <input type="hidden" name="rating" id="rating" value="{{ old('rating', 3) }}">
            @if ($errors->has('rating'))
            <span class="text-danger">{{ $errors->first('rating')}}</span>
            @endif
        </div>
        <div class="form-group">
            <label for="tags">タグ（複数選択可）</label>
            <div id="tags-container"></div>
            <input type="hidden" name="tags" id="tags" value="">
            @if ($errors->has('tags'))
            <span class="text-danger">{{ $errors->first('tags')}}</span>
            @endif
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">投稿する</button>
        </div>
        </form>
    </div>
</div>
@endsection


<script>
document.addEventListener('DOMContentLoaded', function() {
    // StarRatingコンポーネント
    const ratingContainer = document.getElementById('rating-container');
    const ratingInput = document.getElementById('rating');
    if (ratingContainer && window.StarRating && window.React && window.createRoot) {
        const root = window.createRoot(ratingContainer);
        root.render(
            window.React.createElement(window.StarRating, {
                value: parseInt(ratingInput.value) || 3,
                onChange: (value) => {
                    ratingInput.value = value;
                },
                size: 24
            })
        );
    }

    // TagSelectorコンポーネント
    const tagsContainer = document.getElementById('tags-container');
    const tagsInput = document.getElementById('tags');
    const availableTags = @json($tags ?? []);
    
    if (tagsContainer && window.TagSelector && window.React && window.createRoot) {
        const root = window.createRoot(tagsContainer);
        root.render(
            window.React.createElement(window.TagSelector, {
                availableTags: availableTags,
                selectedTags: [],
                onChange: (selectedIds) => {
                    // 配列をJSON形式で格納し、サーバー側でデコード
                    const form = tagsInput.closest('form');
                    // 既存のhidden inputsを削除
                    form.querySelectorAll('input[name="tags[]"]').forEach(el => el.remove());
                    // 新しいhidden inputsを追加
                    selectedIds.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'tags[]';
                        input.value = id;
                        form.appendChild(input);
                    });
                }
            })
        );
    }
});
</script>
