@if(isset($program_id) && $program_id)
    @if(Auth::check())
    <a href="{{ route('post.review',$program_id) }}"><button class="btn btn-dark">レビューを投稿する</button></a>
    @else
    <a href="{{ route('login') }}"><button class="btn btn-dark">レビューを投稿する</button></a>
    @endif
@else
    <div class="alert alert-info mb-3">
        <i class="fas fa-info-circle"></i> この番組はまだデータベースに登録されていません。しばらくしてから再度お試しください。
    </div>
@endif
