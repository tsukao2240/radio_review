<?php
//トップページ

use DaveJamesMiller\Breadcrumbs\Facades\Breadcrumbs;

Breadcrumbs::for('/', function ($trail) {
    $trail->push('ホーム', url('/'));
});
//トップページ->放送中の番組
Breadcrumbs::for('schedule', function ($trail) {
    $trail->parent('/');
    $trail->push('放送中の番組', url('schedule'));
});
//トップページ->番組一覧
Breadcrumbs::for('search', function ($trail) {
    $trail->parent('/');
    $trail->push('番組一覧', route('search'));
});
//トップページ->{title}
Breadcrumbs::for('detail', function ($trail,$station_id,$title) {
    $trail->parent('/');
    $trail->push($title, url('list/' . $station_id . '/' . $title));
});
//トップページ->{title}->レビュー一覧
Breadcrumbs::for('review.list', function ($trail,$station_id,$title) {
    $trail->parent('detail',$station_id,$title);
    $trail->push('投稿されたレビュー', url('/list'. $station_id . '/' . $title . '/review'));
});
//トップページ->検索結果
Breadcrumbs::for('result', function ($trail) {
    $trail->parent('/');
    $trail->push('検索結果', url('result'));
});
//トップページ->投稿したレビュー
Breadcrumbs::for('my_review', function ($trail) {
    $trail->parent('/');
    $trail->push('投稿したレビュー', url('my_review'));
});
//トップページ->レビュー一覧
Breadcrumbs::for('review', function ($trail) {
    $trail->parent('/');
    $trail->push('レビュー一覧', url('review'));
});
//トップページ->放送中の番組->1週間の番組表
Breadcrumbs::for('weekly_schedule', function ($trail) {
    $trail->parent('schedule');
    $trail->push('週間番組表', url('weekly_schedule'));
});

