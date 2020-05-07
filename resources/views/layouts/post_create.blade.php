@if(Auth::check())
<a href="{{ route('review',$program_id) }}"><button class="btn btn-dark">レビューを投稿する</button></a>
@else
<a href="{{ route('login') }}"><button class="btn btn-dark">レビューを投稿する</button></a>
@endif
