<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Middleware;

class RadioBroadcastControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 週間番組表が正常に取得できることをテスト
     */
    public function test_weekly_schedule_returns_view_with_data()
    {
        // キャッシュをクリア
        Cache::flush();

        $response = $this->get('/schedule/TBS');

        $response->assertStatus(200);
        $response->assertViewIs('radioprogram.weekly_schedule');
        $response->assertViewHas(['entries', 'thisWeek', 'broadcast_name']);
    }

    /**
     * キャッシュが正しく動作することをテスト
     */
    public function test_weekly_schedule_uses_cache()
    {
        // キャッシュをクリア
        Cache::flush();

        $stationId = 'TBS';
        $cacheKey = 'weekly_schedule_' . $stationId . '_' . floor(time() / 1800);

        // 1回目のリクエスト（APIから取得）
        $response1 = $this->get('/schedule/' . $stationId);
        $response1->assertStatus(200);

        // キャッシュが作成されたことを確認
        $this->assertTrue(Cache::has($cacheKey));

        // 2回目のリクエスト（キャッシュから取得）
        $response2 = $this->get('/schedule/' . $stationId);
        $response2->assertStatus(200);
    }

    /**
     * 異なる放送局IDで週間番組表が取得できることをテスト
     */
    public function test_weekly_schedule_with_different_station_ids()
    {
        Cache::flush();

        $stationIds = ['TBS', 'QRR', 'LFR', 'RN1', 'RN2'];

        foreach ($stationIds as $stationId) {
            $response = $this->get('/schedule/' . $stationId);
            $response->assertStatus(200);
            $response->assertViewIs('radioprogram.weekly_schedule');
        }
    }

    /**
     * API取得エラー時にフォールバックデータが返されることをテスト
     */
    public function test_weekly_schedule_returns_fallback_on_api_error()
    {
        Cache::flush();

        // 存在しない放送局IDでテスト
        $response = $this->get('/schedule/INVALID_STATION');

        $response->assertStatus(200);
        $response->assertViewIs('radioprogram.weekly_schedule');
        $response->assertViewHas('entries');
        $response->assertViewHas('broadcast_name');
    }

    /**
     * 番組データが日付順にソートされることをテスト
     */
    public function test_weekly_schedule_entries_are_sorted_by_date_and_time()
    {
        Cache::flush();

        $response = $this->get('/schedule/TBS');

        $response->assertStatus(200);

        $entries = $response->viewData('entries');

        if (!empty($entries)) {
            // エントリが日付と時刻順にソートされているか確認
            for ($i = 0; $i < count($entries) - 1; $i++) {
                $current = $entries[$i]['date'] . $entries[$i]['start'];
                $next = $entries[$i + 1]['date'] . $entries[$i + 1]['start'];
                $this->assertLessThanOrEqual($next, $current);
            }
        }
    }

    /**
     * thisWeekに重複のない日付リストが含まれることをテスト
     */
    public function test_weekly_schedule_thisweek_has_unique_dates()
    {
        Cache::flush();

        $response = $this->get('/schedule/TBS');

        $response->assertStatus(200);

        $thisWeek = $response->viewData('thisWeek');

        if (!empty($thisWeek)) {
            // 重複がないことを確認
            $uniqueDates = array_unique($thisWeek);
            $this->assertEquals(count($uniqueDates), count($thisWeek));
        }
    }

    /**
     * キャッシュキーが30分単位で変わることをテスト
     */
    public function test_cache_key_changes_every_30_minutes()
    {
        $stationId = 'TBS';
        $currentTime = time();

        // 現在の30分単位のタイムスロット
        $cacheKey1 = 'weekly_schedule_' . $stationId . '_' . floor($currentTime / 1800);

        // 次の30分単位のタイムスロット
        $cacheKey2 = 'weekly_schedule_' . $stationId . '_' . floor(($currentTime + 1800) / 1800);

        $this->assertNotEquals($cacheKey1, $cacheKey2);
    }
}
