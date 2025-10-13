<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\RadioProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_search_page()
    {
        $response = $this->get(route('program.search'));

        $response->assertStatus(200);
    }

    public function test_search_returns_results_with_valid_keyword()
    {
        RadioProgram::factory()->create(['title' => 'ラジオ番組テスト']);
        RadioProgram::factory()->create(['title' => '別の番組']);

        $response = $this->get(route('program.search', ['keyword' => 'ラジオ']));

        $response->assertStatus(200);
        $response->assertSee('ラジオ番組テスト');
    }

    public function test_search_with_empty_keyword_shows_validation_error()
    {
        $response = $this->get(route('program.search', ['keyword' => '']));

        $response->assertStatus(302);
    }

    public function test_search_excludes_rerun_programs()
    {
        RadioProgram::factory()->create(['title' => '通常番組']);
        RadioProgram::factory()->create(['title' => '番組（再）']);
        RadioProgram::factory()->create(['title' => '番組【再】']);

        $response = $this->get(route('program.search', ['keyword' => '番組']));

        $response->assertStatus(200);
        $response->assertSee('通常番組');
        $response->assertDontSee('番組（再）');
        $response->assertDontSee('番組【再】');
    }

    public function test_api_search_returns_json()
    {
        RadioProgram::factory()->create(['title' => 'APIテスト番組']);

        $response = $this->getJson('/api/programs?keyword=API');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => ['id', 'title', 'station_id']
            ]);
    }

    public function test_search_rate_limiting_works()
    {
        for ($i = 0; $i < 61; $i++) {
            $response = $this->get(route('program.search', ['keyword' => 'test']));
        }

        $response->assertStatus(429);
    }
}
