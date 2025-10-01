<style>
/* 検索フォームスタイル */
.search-container {
    max-width: 600px;
    margin: 30px auto;
}

.search-form {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-radius: 8px;
    overflow: hidden;
}

.search-form .input-group {
    display: flex;
}

.search-form .form-control {
    border: none;
    padding: 12px 20px;
    font-size: 16px;
    flex: 1;
}

.search-form .form-control:focus {
    outline: none;
    box-shadow: none;
}

.search-form .btn-search {
    background-color: #007bff;
    color: white;
    border: none;
    padding: 12px 24px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.search-form .btn-search:hover {
    background-color: #0056b3;
}

.search-form .btn-search i {
    font-size: 18px;
}

/* レスポンシブ対応 */
@media (max-width: 768px) {
    .search-container {
        width: 90% !important;
        margin: 20px auto;
    }
    .search-form .form-control {
        font-size: 14px;
        padding: 10px 15px;
    }
    .search-form .btn-search {
        padding: 10px 20px;
    }
}
</style>
<form method="get" action="{{ route('program.search') }}">
<div class="search-container">
    <div class="input-group search-form">
        <input type="text" name="title" class="form-control" placeholder="番組名で検索する" value="{{ request('title') }}">
        <button type="submit" class="btn-search"><i class="fas fa-search"></i></button>
    </div>
</div>
</form>
<br>
