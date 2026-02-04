<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\User;
use App\FavoriteProgram;

class FavoriteProgramTest extends TestCase
{
    use RefreshDatabase;

    // お気に入り登録テスト
    public function test_user_can_add_favorite_program()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/favorites', [
            'station_id' => 'TBS',
            'program_title' => 'テスト番組'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'お気に入りに登録しました'
                 ]);

        $this->assertDatabaseHas('favorite_programs', [
            'user_id' => $user->id,
            'station_id' => 'TBS',
            'program_title' => 'テスト番組'
        ]);
    }

    // 重複登録防止テスト
    public function test_user_cannot_add_duplicate_favorite_program()
    {
        $user = User::factory()->create();

        // 1回目の登録
        FavoriteProgram::create([
            'user_id' => $user->id,
            'station_id' => 'TBS',
            'program_title' => 'テスト番組'
        ]);

        // 2回目の登録（重複）
        $response = $this->actingAs($user)->postJson('/favorites', [
            'station_id' => 'TBS',
            'program_title' => 'テスト番組'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => false,
                     'message' => 'すでにお気に入りに登録されています'
                 ]);
    }

    // お気に入り削除テスト
    public function test_user_can_delete_favorite_program()
    {
        $user = User::factory()->create();

        $favorite = FavoriteProgram::create([
            'user_id' => $user->id,
            'station_id' => 'TBS',
            'program_title' => 'テスト番組'
        ]);

        $response = $this->actingAs($user)->postJson('/favorites/delete', [
            'id' => $favorite->id
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'お気に入りを削除しました'
                 ]);

        $this->assertDatabaseMissing('favorite_programs', [
            'id' => $favorite->id
        ]);
    }

    // 未ログイン時のリダイレクトテスト
    public function test_guest_cannot_access_favorites()
    {
        $response = $this->get('/favorites');
        $response->assertRedirect('/login');
    }

    // お気に入り確認APIテスト
    public function test_user_can_check_favorite_status()
    {
        $user = User::factory()->create();

        FavoriteProgram::create([
            'user_id' => $user->id,
            'station_id' => 'TBS',
            'program_title' => 'テスト番組'
        ]);

        $response = $this->actingAs($user)->getJson('/favorites/check?station_id=TBS&program_title=テスト番組');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'is_favorite' => true
                 ]);
    }

    // お気に入り一覧表示テスト
    public function test_user_can_view_favorites_list()
    {
        $user = User::factory()->create();

        FavoriteProgram::create([
            'user_id' => $user->id,
            'station_id' => 'TBS',
            'program_title' => 'テスト番組1'
        ]);

        FavoriteProgram::create([
            'user_id' => $user->id,
            'station_id' => 'QRR',
            'program_title' => 'テスト番組2'
        ]);

        $response = $this->actingAs($user)->get('/favorites');

        $response->assertStatus(200)
                 ->assertSee('テスト番組1')
                 ->assertSee('テスト番組2');
    }
}