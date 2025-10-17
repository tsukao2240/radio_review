<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RadioProgramSearchService
{
    /**
     * 除外する番組タイトルのパターン
     *
     * @var array
     */
    private $excludePatterns = [
        '%（新）%', '%(新)%', '%［新］%', '%[新]%',
        '%【新番組】%', '%＜新番組＞%',
        '%（終）%', '%［終］%', '%≪終≫%', '%【終】%',
        '%【最終回】%', '%＜最終回＞%',
        '%(再)%', '%【再】%', '%≪再≫%', '%[再]%',
        '%（再放送）%', '%再放送%'
    ];

    /**
     * 除外パターンをクエリに適用する
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    private function applyExcludePatterns($query)
    {
        foreach ($this->excludePatterns as $pattern) {
            $query->where('title', 'not like', $pattern);
        }
        return $query;
    }

    /**
     * 番組をタイトルで検索する
     *
     * @param string $keyword
     * @param string|null $stationId
     * @return \Illuminate\Database\Query\Builder
     */
    public function searchByTitle($keyword, $stationId = null)
    {
        $query = DB::table('radio_programs')
            ->where('title', 'LIKE', '%' . $keyword . '%');

        $query = $this->applyExcludePatterns($query);

        if (!empty($stationId)) {
            $query->where('station_id', $stationId);
        }

        return $query;
    }

    /**
     * 番組を出演者で検索する
     *
     * @param string $cast
     * @param string|null $stationId
     * @return \Illuminate\Database\Query\Builder
     */
    public function searchByCast($cast, $stationId = null)
    {
        $query = DB::table('radio_programs')
            ->where('cast', 'LIKE', '%' . $cast . '%');

        $query = $this->applyExcludePatterns($query);

        if (!empty($stationId)) {
            $query->where('station_id', $stationId);
        }

        return $query;
    }

    /**
     * 番組を統合検索する（タイトル、出演者、投稿を結合）
     *
     * @param string $keyword
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function searchProgramsWithPosts($keyword, $perPage = 10)
    {
        try {
            // タイトルで検索
            $titleQuery = $this->searchByTitle($keyword)
                ->select(['id', 'station_id', 'title', 'cast', 'start', 'end', 'info', 'url', 'image']);

            // 出演者で検索
            $castQuery = $this->searchByCast($keyword)
                ->select(['id', 'station_id', 'title', 'cast', 'start', 'end', 'info', 'url', 'image']);

            // 投稿がある番組
            $postsQuery = DB::table('radio_programs as r')
                ->join('posts', 'r.id', '=', 'posts.program_id')
                ->select(['r.id as id', 'station_id', 'r.title', 'cast', 'start', 'end', 'info', 'url', 'image']);

            // クエリを結合してページネーション
            $programs = $titleQuery
                ->union($castQuery)
                ->union($postsQuery)
                ->paginate($perPage);

            Log::info('Program search executed', [
                'keyword' => $keyword,
                'results_count' => $programs->total()
            ]);

            return $programs;

        } catch (\Exception $e) {
            Log::error('Program search error: ' . $e->getMessage(), [
                'keyword' => $keyword,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * 番組を統合検索する（API用、ページネーションなし）
     *
     * @param string|null $keyword
     * @param string|null $cast
     * @param string|null $stationId
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function searchProgramsForApi($keyword = null, $cast = null, $stationId = null, $limit = 20)
    {
        try {
            $queries = [];

            // タイトルで検索
            if (!empty($keyword)) {
                $queries[] = $this->searchByTitle($keyword, $stationId)
                    ->select(['id', 'station_id', 'title', 'cast', 'start', 'end', 'info', 'url', 'image']);
            }

            // 出演者で検索
            if (!empty($cast)) {
                $queries[] = $this->searchByCast($cast, $stationId)
                    ->select(['id', 'station_id', 'title', 'cast', 'start', 'end', 'info', 'url', 'image']);
            }

            // クエリが1つもない場合は空のコレクションを返す
            if (empty($queries)) {
                return collect([]);
            }

            // 最初のクエリを取得
            $finalQuery = array_shift($queries);

            // 残りのクエリをunionで結合
            foreach ($queries as $query) {
                $finalQuery = $finalQuery->union($query);
            }

            // 結果を取得
            $programs = $finalQuery
                ->distinct()
                ->limit($limit)
                ->get();

            Log::info('API Program search executed', [
                'keyword' => $keyword,
                'cast' => $cast,
                'station_id' => $stationId,
                'results_count' => $programs->count()
            ]);

            return $programs;

        } catch (\Exception $e) {
            Log::error('API Program search error: ' . $e->getMessage(), [
                'keyword' => $keyword,
                'cast' => $cast,
                'station_id' => $stationId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * すべての番組を取得する（投稿画面用）
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllPrograms($perPage = 10)
    {
        try {
            $query = DB::table('radio_programs');
            $query = $this->applyExcludePatterns($query);

            $programs = $query->paginate($perPage);

            Log::info('All programs retrieved', [
                'results_count' => $programs->total()
            ]);

            return $programs;

        } catch (\Exception $e) {
            Log::error('Get all programs error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
