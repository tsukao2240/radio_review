<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\RadioProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class ViewProgramDetailsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 番組詳細ページが正常に表示されることをテスト
     */
    public function test_program_detail_page_displays_successfully()
    {
        // テスト用の番組データを作成
        $program = RadioProgram::create([
            'station_id' => 'TBS',
            'title' => 'テスト番組',
            'cast' => 'テストキャスト',
            'info' => 'テスト番組の情報',
            'image' => 'https://example.com/test.jpg',
            'start' => '2025-10-02 10:00:00',
            'end' => '2025-10-02 11:00:00'
        ]);

        // 番組詳細ページにアクセス
        $response = $this->get(route('program.detail', [
            'station_id' => $program->station_id,
            'title' => $program->title
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('radioprogram.detail');
    }

    /**
     * キャッシュが正しく機能することをテスト
     */
    public function test_program_detail_uses_cache()
    {
        Cache::flush();

        $program = RadioProgram::create([
            'station_id' => 'TBS',
            'title' => 'キャッシュテスト番組',
            'cast' => 'テストキャスト',
            'info' => 'キャッシュテストの情報',
            'start' => '2025-10-02 12:00:00',
            'end' => '2025-10-02 13:00:00'
        ]);

        $cacheKey = 'program_detail_' . $program->station_id . '_' . md5($program->title);

        // 初回アクセス（キャッシュなし）
        $this->assertFalse(Cache::has($cacheKey));
        
        $response = $this->get(route('program.detail', [
            'station_id' => $program->station_id,
            'title' => $program->title
        ]));

        $response->assertStatus(200);

        // 2回目アクセス（キャッシュあり）
        $this->assertTrue(Cache::has($cacheKey));
    }

    /**
     * 存在しない番組のアクセス時の動作をテスト
     */
    public function test_nonexistent_program_returns_empty_results()
    {
        Cache::flush();

        $response = $this->get(route('program.detail', [
            'station_id' => 'NONEXISTENT',
            'title' => '存在しない番組'
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('radioprogram.detail');
    }

    /**
     * 番組データがDBから正しく取得されることをテスト
     */
    public function test_program_data_retrieved_from_database()
    {
        Cache::flush();

        $program = RadioProgram::create([
            'station_id' => 'QRR',
            'title' => 'データベーステスト番組',
            'cast' => 'DBテストキャスト',
            'info' => 'DBから取得するテスト',
            'image' => 'https://example.com/db-test.jpg',
            'start' => '2025-10-02 14:00:00',
            'end' => '2025-10-02 15:00:00'
        ]);

        $response = $this->get(route('program.detail', [
            'station_id' => $program->station_id,
            'title' => $program->title
        ]));

        $response->assertStatus(200);
        $response->assertSee($program->title);
        $response->assertSee($program->cast);
    }
}
