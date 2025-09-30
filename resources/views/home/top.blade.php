@extends('layouts.header')
@section('content')
<style>
/* レスポンシブ対応 */
@media (max-width: 768px) {
    .img_body {
        padding: 20px 10px;
    }
}
</style>

<title>RadioProgram Review</title>

<div class="img_body">
    @include('includes.search')
</div>
@endsection
