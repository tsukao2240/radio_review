<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\RadioProgram;
use Illuminate\Support\Facades\Cache;

class CrudControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // テスト用の番組データを作成
        RadioProgram::create([
            'id' => 'test_prog_001',
            'title' => 'テスト番組',
            'cast' => 'テスト出演者',
            'date' => '20250101',
            'start' => '12:00',
            'end' => '13:00',
            'station_id' => 'TBS'
        ]);

        RadioProgram::create([
            'id' => 'test_prog_002',
            'title' => 'ニュース番組',
            'cast' => 'アナウンサー',
            'date' => '20250101',
            'start' => '18:00',
            'end' => '19:00',
            'station_id' => 'TBS'
        ]);

        RadioProgram::create([
            'id' => 'test_prog_003',
            'title' => '音楽番組',
            'cast' => 'DJ',
            'date' => '20250101',
            'start' => '20:00',
            'end' => '21:00',
            'station_id' => 'TBS'
        ]);

        // 除外される番組（新番組）
        RadioProgram::create([
            'id' => 'test_prog_004',
            'title' => '【新】新番組',
            'cast' => 'テスト',
            'date' => '20250101',
            'start' => '14:00',
            'end' => '15:00',
            'station_id' => 'TBS'
        ]);

        // 除外される番組（終了番組）
        RadioProgram::create([
            'id' => 'test_prog_005',
            'title' => '【終】最終回番組',
            'cast' => 'テスト',
            'date' => '20250101',
            'start' => '15:00',
            'end' => '16:00',
            'station_id' => 'TBS'
        ]);

        // 除外される番組（再放送）
        RadioProgram::create([
            'id' => 'test_prog_006',
            'title' => '（再）再放送番組',
            'cast' => 'テスト',
            'date' => '20250101',
            'start' => '16:00',
            'end' => '17:00',
            'station_id' => 'TBS'
        ]);
    }

    /**
     * 検索キーワードが入力されていない場合は前のページに戻る
     */
    public function test_index_redirects_back_when_no_keyword()
    {
        $response = $this->get('/search');

        $response->assertRedirect();
    }

    /**
     * 検索キーワードで番組が検索できることをテスト
     */
    public function test_index_searches_programs_by_keyword()
    {
        Cache::flush();

        $response = $this->get('/search?title=テスト');

        $response->assertStatus(200);
        $response->assertViewIs('post.index');
        $response->assertViewHas('programs');

        $programs = $response->viewData('programs');
        $this->assertGreaterThan(0, $programs->count());
        
        // 「テスト番組」が含まれることを確認
        $found = false;
        foreach ($programs as $program) {
            if (strpos($program->title, 'テスト') !== false) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);
    }

    /**
     * 部分一致検索が動作することをテスト
     */
    public function test_index_performs_partial_match_search()
    {
        Cache::flush();

        $response = $this->get('/search?title=ニュース');

        $response->assertStatus(200);
        $programs = $response->viewData('programs');
        
        $this->assertGreaterThan(0, $programs->count());
        $this->assertStringContainsString('ニュース', $programs->first()->title);
    }

    /**
     * 新番組マークが付いた番組が除外されることをテスト
     */
    public function test_index_excludes_new_program_markers()
    {
        Cache::flush();

        $response = $this->get('/search?title=番組');

        $response->assertStatus(200);
        $programs = $response->viewData('programs');

        // 【新】マークがある番組は除外されているはず
        foreach ($programs as $program) {
            $this->assertStringNotContainsString('【新】', $program->title);
        }
    }

    /**
     * 終了番組マークが付いた番組が除外されることをテスト
     */
    public function test_index_excludes_final_program_markers()
    {
        Cache::flush();

        $response = $this->get('/search?title=番組');

        $response->assertStatus(200);
        $programs = $response->viewData('programs');

        // 【終】マークがある番組は除外されているはず
        foreach ($programs as $program) {
            $this->assertStringNotContainsString('【終】', $program->title);
            $this->assertStringNotContainsString('【最終回】', $program->title);
        }
    }

    /**
     * 再放送マークが付いた番組が除外されることをテスト
     */
    public function test_index_excludes_rerun_program_markers()
    {
        Cache::flush();

        $response = $this->get('/search?title=番組');

        $response->assertStatus(200);
        $programs = $response->viewData('programs');

        // （再）マークがある番組は除外されているはず
        foreach ($programs as $program) {
            $this->assertStringNotContainsString('（再）', $program->title);
            $this->assertStringNotContainsString('再放送', $program->title);
        }
    }

    /**
     * 検索結果がキャッシュされることをテスト
     */
    public function test_index_caches_search_results()
    {
        Cache::flush();

        $keyword = 'テスト';
        $cacheKey = 'search_programs_' . md5($keyword);

        // キャッシュが存在しないことを確認
        $this->assertFalse(Cache::has($cacheKey));

        // 検索実行
        $response = $this->get('/search?title=' . $keyword);
        $response->assertStatus(200);

        // キャッシュが作成されたことを確認
        $this->assertTrue(Cache::has($cacheKey));
    }

    /**
     * ページネーションが動作することをテスト
     */
    public function test_index_paginates_results()
    {
        Cache::flush();

        // 15件の番組を追加作成（既存3件 + 新規15件 = 18件）
        for ($i = 1; $i <= 15; $i++) {
            RadioProgram::create([
                'id' => 'paginate_test_' . $i,
                'title' => 'ページネーションテスト番組' . $i,
                'cast' => 'テスト',
                'date' => '20250101',
                'start' => '12:00',
                'end' => '13:00',
                'station_id' => 'TBS'
            ]);
        }

        $response = $this->get('/search?title=ページネーションテスト');

        $response->assertStatus(200);
        $programs = $response->viewData('programs');

        // 10件ずつページネーションされているか確認
        $this->assertEquals(10, $programs->perPage());
        $this->assertEquals(15, $programs->total());
    }

    /**
     * 空の検索結果が正しく処理されることをテスト
     */
    public function test_index_handles_empty_search_results()
    {
        Cache::flush();

        $response = $this->get('/search?title=存在しない番組名');

        $response->assertStatus(200);
        $response->assertViewIs('post.index');
        
        $programs = $response->viewData('programs');
        $this->assertEquals(0, $programs->count());
    }

    /**
     * 特殊文字を含む検索キーワードが正しく処理されることをテスト
     */
    public function test_index_handles_special_characters_in_keyword()
    {
        Cache::flush();

        RadioProgram::create([
            'id' => 'special_char_test',
            'title' => 'テスト！番組？',
            'cast' => 'テスト',
            'date' => '20250101',
            'start' => '12:00',
            'end' => '13:00',
            'station_id' => 'TBS'
        ]);

        $response = $this->get('/search?title=テスト！');

        $response->assertStatus(200);
        $programs = $response->viewData('programs');
        $this->assertGreaterThan(0, $programs->count());
    }
}
