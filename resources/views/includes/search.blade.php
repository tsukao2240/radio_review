<form method="get" action="{{ route('program.search') }}">
<div class="mx-auto" style="width:400">
    <div class="input-group">
        <input type="text" name="title" class="form-control" placeholder="番組名で検索する" value="{{ request('title') }}">
        <button type="submit" class="btn"><i class="fas fa-search"></i></button>
    </div>
</div>
</form>
<br>
