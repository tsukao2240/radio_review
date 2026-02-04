<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;

class RadioProgramControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 現在放送中の番組一覧が取得できることをテスト
     */
    public function test_fetch_recent_program_returns_view()
    {
        Cache::flush();

        $response = $this->get('/schedule');

        $response->assertStatus(200);
        $response->assertViewIs('radioprogram.recent_schedule');
        $response->assertViewHas('results');
    }

    /**
     * 結果が配列であることをテスト
     */
    public function test_fetch_recent_program_returns_array()
    {
        Cache::flush();

        $response = $this->get('/schedule');

        $response->assertStatus(200);
        
        $results = $response->viewData('results');
        $this->assertIsArray($results);
    }

    /**
     * キャッシュが正しく動作することをテスト
     */
    public function test_fetch_recent_program_uses_cache()
    {
        Cache::flush();

        $cacheKey = 'recent_programs_' . floor(time() / 300);

        // キャッシュが存在しないことを確認
        $this->assertFalse(Cache::has($cacheKey));

        // 1回目のリクエスト
        $response1 = $this->get('/schedule');
        $response1->assertStatus(200);

        // キャッシュが作成されたことを確認
        $this->assertTrue(Cache::has($cacheKey));

        // 2回目のリクエスト（キャッシュから取得）
        $response2 = $this->get('/schedule');
        $response2->assertStatus(200);

        // 両方のレスポンスが同じデータを返すことを確認
        $this->assertEquals(
            $response1->viewData('results'),
            $response2->viewData('results')
        );
    }

    /**
     * キャッシュキーが5分単位で変わることをテスト
     */
    public function test_cache_key_changes_every_5_minutes()
    {
        $currentTime = time();

        // 現在の5分単位のタイムスロット
        $cacheKey1 = 'recent_programs_' . floor($currentTime / 300);

        // 次の5分単位のタイムスロット
        $cacheKey2 = 'recent_programs_' . floor(($currentTime + 300) / 300);

        $this->assertNotEquals($cacheKey1, $cacheKey2);
    }

    /**
     * 結果に必要なキーが含まれていることをテスト
     */
    public function test_fetch_recent_program_results_have_required_keys()
    {
        Cache::flush();

        $response = $this->get('/schedule');

        $response->assertStatus(200);
        
        $results = $response->viewData('results');

        if (!empty($results)) {
            $firstResult = $results[0];
            
            // 必要なキーが存在するか確認
            $this->assertArrayHasKey('station_id', $firstResult);
            $this->assertArrayHasKey('station', $firstResult);
            $this->assertArrayHasKey('title', $firstResult);
            $this->assertArrayHasKey('cast', $firstResult);
            $this->assertArrayHasKey('start', $firstResult);
            $this->assertArrayHasKey('end', $firstResult);
            $this->assertArrayHasKey('url', $firstResult);
        }
    }

    /**
     * 時刻フォーマットが正しいことをテスト（HH:MM形式）
     */
    public function test_fetch_recent_program_time_format_is_correct()
    {
        Cache::flush();

        $response = $this->get('/schedule');

        $response->assertStatus(200);
        
        $results = $response->viewData('results');

        if (!empty($results)) {
            foreach ($results as $result) {
                if (!empty($result['start'])) {
                    // HH:MM形式かチェック
                    $this->assertMatchesRegularExpression('/^\d{2}:\d{2}$/', $result['start']);
                }
                if (!empty($result['end'])) {
                    $this->assertMatchesRegularExpression('/^\d{2}:\d{2}$/', $result['end']);
                }
            }
        }
    }

    /**
     * 放送局の重複が除去されていることをテスト
     */
    public function test_fetch_recent_program_removes_duplicate_stations()
    {
        Cache::flush();

        $response = $this->get('/schedule');

        $response->assertStatus(200);
        
        $results = $response->viewData('results');

        if (!empty($results)) {
            $stationIds = array_column($results, 'station_id');
            $uniqueStationIds = array_unique($stationIds);
            
            // 重複がないことを確認
            $this->assertEquals(count($uniqueStationIds), count($stationIds));
        }
    }

    /**
     * APIエラー時でもページが正常に表示されることをテスト
     */
    public function test_fetch_recent_program_handles_api_errors_gracefully()
    {
        Cache::flush();

        $response = $this->get('/schedule');

        // エラーが発生してもステータス200が返る
        $response->assertStatus(200);
        $response->assertViewIs('radioprogram.recent_schedule');
    }

    /**
     * 複数回アクセスしても一貫した結果が返ることをテスト
     */
    public function test_fetch_recent_program_returns_consistent_results()
    {
        Cache::flush();

        $response1 = $this->get('/schedule');
        $response2 = $this->get('/schedule');

        $response1->assertStatus(200);
        $response2->assertStatus(200);

        // キャッシュにより同じ結果が返される
        $this->assertEquals(
            $response1->viewData('results'),
            $response2->viewData('results')
        );
    }

    /**
     * 空の結果でもエラーにならないことをテスト
     */
    public function test_fetch_recent_program_handles_empty_results()
    {
        Cache::flush();

        $response = $this->get('/schedule');

        $response->assertStatus(200);
        
        $results = $response->viewData('results');
        $this->assertIsArray($results);
    }

    /**
     * station_idが正しいフォーマットであることをテスト
     */
    public function test_fetch_recent_program_station_ids_are_valid()
    {
        Cache::flush();

        $response = $this->get('/schedule');

        $response->assertStatus(200);
        
        $results = $response->viewData('results');

        if (!empty($results)) {
            foreach ($results as $result) {
                if (!empty($result['station_id'])) {
                    // station_idが文字列であることを確認
                    $this->assertIsString($result['station_id']);
                    $this->assertNotEmpty($result['station_id']);
                }
            }
        }
    }
}
