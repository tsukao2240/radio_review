<?php
//トップページ

use Diglactic\Breadcrumbs\Breadcrumbs;

Breadcrumbs::for('/', function ($trail) {
    $trail->push('ホーム', url('/'));
});
//トップページ->放送中の番組
Breadcrumbs::for('schedule', function ($trail) {
    $trail->parent('/');
    $trail->push('放送中の番組', url('schedule'));
});
//トップページ->番組検索
Breadcrumbs::for('search', function ($trail) {
    $trail->parent('/');
    $trail->push('番組検索', route('program.search'));
});
//トップページ->番組検索->検索結果
Breadcrumbs::for('search.result', function ($trail, $keyword) {
    $trail->parent('search');
    $trail->push('検索結果', route('program.search', ['title' => $keyword]));
});
//トップページ->{title}
Breadcrumbs::for('detail', function ($trail,$station_id,$title) {
    $trail->parent('/');
    $trail->push($title, url('list/' . $station_id . '/' . $title));
});
//トップページ->番組検索->検索結果->{title}
Breadcrumbs::for('detail.from_search', function ($trail,$station_id,$title,$keyword) {
    $trail->parent('search.result', $keyword);
    $trail->push($title, url('list/' . $station_id . '/' . $title));
});
//トップページ->放送中の番組->{title}
Breadcrumbs::for('detail.from_schedule', function ($trail,$station_id,$title) {
    $trail->parent('schedule');
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
//トップページ->放送中の番組->週間番組表
Breadcrumbs::for('weekly_schedule', function ($trail, $station_id) {
    $trail->parent('schedule');
    $trail->push('週間番組表', route('schedule.weekly', ['station_id' => $station_id]));
});
//トップページ->放送中の番組->週間番組表->{title}
Breadcrumbs::for('detail.from_weekly', function ($trail,$station_id,$title) {
    $trail->parent('weekly_schedule', $station_id);
    $trail->push($title, url('list/' . $station_id . '/' . $title));
});
//トップページ->タイムフリー録音
Breadcrumbs::for('timefree', function ($trail) {
    $trail->parent('/');
    $trail->push('タイムフリー録音', route('schedule.twoweek'));
});
//トップページ->タイムフリー録音->{title}
Breadcrumbs::for('detail.from_timefree', function ($trail,$station_id,$title) {
    $trail->parent('timefree');
    $trail->push($title, url('list/' . $station_id . '/' . $title));
});
//トップページ->投稿したレビュー->{title}
Breadcrumbs::for('detail.from_mypage', function ($trail,$station_id,$title) {
    $trail->parent('my_review');
    $trail->push($title, url('list/' . $station_id . '/' . $title));
});
//トップページ->レビュー一覧->{title}
Breadcrumbs::for('detail.from_review', function ($trail,$station_id,$title) {
    $trail->parent('review');
    $trail->push($title, url('list/' . $station_id . '/' . $title));
});
//トップページ->お気に入り番組
Breadcrumbs::for('favorites', function ($trail) {
    $trail->parent('/');
    $trail->push('お気に入り番組', route('favorites.index'));
});
//トップページ->お気に入り番組->{title}
Breadcrumbs::for('detail.from_favorites', function ($trail,$station_id,$title) {
    $trail->parent('favorites');
    $trail->push($title, url('list/' . $station_id . '/' . $title));
});

