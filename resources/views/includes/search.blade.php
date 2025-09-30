<style>
/* レスポンシブ対応 */
@media (max-width: 768px) {
    .search-container {
        width: 90% !important;
        max-width: 100%;
    }
    .search-container .form-control {
        font-size: 14px;
    }
}
</style>
<form method="get" action="{{ route('program.search') }}">
<div class="mx-auto search-container" style="width:400px">
    <div class="input-group">
        <input type="text" name="title" class="form-control" placeholder="番組名で検索する" value="{{ request('title') }}">
        <button type="submit" class="btn"><i class="fas fa-search"></i></button>
    </div>
</div>
</form>
<br>
