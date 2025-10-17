<form method="get" action="{{ route('program.search') }}">
<div class="search-container">
    <div class="input-group search-form">
        <input type="text" name="title" class="form-control" placeholder="番組名で検索する" value="{{ request('title') }}">
        <button type="submit" class="btn-search"><i class="fas fa-search"></i></button>
    </div>
</div>
</form>
<br>
