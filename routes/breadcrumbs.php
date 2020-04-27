<?php
//トップページ
Breadcrumbs::for('/', function ($trail) {
    $trail->push('ホーム', url('/'));
});
//トップページ->放送中の番組
Breadcrumbs::for('radioProgramGuide', function ($trail) {
    $trail->parent('/');
    $trail->push('放送中の番組', url('radioProgramGuide'));
});
//トップページ->番組一覧
Breadcrumbs::for('Search', function ($trail) {
    $trail->parent('/');
    $trail->push('番組一覧', url('Search'));
});
//トップページ->放送中の番組->番組情報
Breadcrumbs::for('list/{title}', function ($trail) {
    $trail->parent('/');
    $trail->push('番組情報', url('list/{title}'));
});
//トップページ->検索結果
Breadcrumbs::for('result', function ($trail) {
    $trail->parent('/');
    $trail->push('検索結果', url('Search'));
});
