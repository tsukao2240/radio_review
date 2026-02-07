<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\Post;
use App\PostTag;
use App\RadioProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostRatingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // タグをシード
        PostTag::insert([
            ['name' => '感動した', 'display_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => '笑った', 'display_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => '勉強になった', 'display_order' => 3, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /** @test */
    public function user_can_create_review_with_rating()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $program = RadioProgram::factory()->create();
        $programId = $program->id;
        
        $response = $this->actingAs($user)->post(route('post.store', ['id' => $programId]), [
            'user_id' => $user->id,
            'program_id' => $programId,
            'program_title' => 'テスト番組',
            'title' => 'テストレビュー',
            'body' => 'とても面白い番組でした',
            'rating' => 5.0,
            'station_id' => 'TBS',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('message', '投稿が完了しました');
        
        $this->assertDatabaseHas('posts', [
            'user_id' => $user->id,
            'program_id' => $programId,
            'title' => 'テストレビュー',
            'rating' => 5.0,
        ]);
    }

    /** @test */
    public function rating_must_be_between_1_and_5()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $program = RadioProgram::factory()->create();
        
        // 0.5の評価は無効
        $response = $this->actingAs($user)->post(route('post.store', ['id' => $program->id]), [
            'user_id' => $user->id,
            'program_id' => $program->id,
            'program_title' => 'テスト番組',
            'title' => 'テストレビュー',
            'body' => 'テスト本文',
            'rating' => 0.5,
            'station_id' => 'TBS',
        ]);

        $response->assertSessionHasErrors('rating');

        // 5.5の評価は無効
        $response = $this->actingAs($user)->post(route('post.store', ['id' => $program->id]), [
            'user_id' => $user->id,
            'program_id' => $program->id,
            'program_title' => 'テスト番組',
            'title' => 'テストレビュー2',
            'body' => 'テスト本文',
            'rating' => 5.5,
            'station_id' => 'TBS',
        ]);

        $response->assertSessionHasErrors('rating');
    }

    /** @test */
    public function user_can_filter_posts_by_minimum_rating()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        
        // 異なる評価の投稿を作成
        Post::factory()->create([
            'user_id' => $user->id,
            'program_id' => 'program1',
            'program_title' => '番組1',
            'rating' => 5.0,
        ]);
        
        Post::factory()->create([
            'user_id' => $user->id,
            'program_id' => 'program2',
            'program_title' => '番組2',
            'rating' => 3.0,
        ]);
        
        Post::factory()->create([
            'user_id' => $user->id,
            'program_id' => 'program3',
            'program_title' => '番組3',
            'rating' => 2.0,
        ]);

        // 4つ星以上でフィルタ
        $response = $this->actingAs($user)->get(route('post.view') . '?min_rating=4');
        
        $response->assertStatus(200);
        $response->assertSee('番組1');
        $response->assertDontSee('番組2');
        $response->assertDontSee('番組3');
    }

    /** @test */
    public function posts_can_be_sorted_by_rating()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        
        $post1 = Post::factory()->create([
            'user_id' => $user->id,
            'program_id' => 'program1',
            'program_title' => '低評価番組',
            'rating' => 2.0,
        ]);
        
        $post2 = Post::factory()->create([
            'user_id' => $user->id,
            'program_id' => 'program2',
            'program_title' => '高評価番組',
            'rating' => 5.0,
        ]);
        
        $post3 = Post::factory()->create([
            'user_id' => $user->id,
            'program_id' => 'program3',
            'program_title' => '中評価番組',
            'rating' => 3.5,
        ]);

        // 評価順でソート（降順）
        $response = $this->actingAs($user)->get(route('post.view') . '?sort_by=rating_desc');
        
        $response->assertStatus(200);
        
        // 高評価が最初に表示される
        $content = $response->getContent();
        $pos1 = strpos($content, '高評価番組');
        $pos2 = strpos($content, '中評価番組');
        $pos3 = strpos($content, '低評価番組');
        
        $this->assertTrue($pos1 < $pos2);
        $this->assertTrue($pos2 < $pos3);
    }

    /** @test */
    public function program_average_rating_is_calculated_correctly()
    {
        $user1 = User::factory()->create(['email_verified_at' => now()]);
        $user2 = User::factory()->create(['email_verified_at' => now()]);
        
        // 同じ番組に複数のレビュー
        Post::factory()->create([
            'user_id' => $user1->id,
            'program_id' => 'test_program',
            'program_title' => 'テスト番組',
            'rating' => 5.0,
        ]);
        
        Post::factory()->create([
            'user_id' => $user2->id,
            'program_id' => 'test_program',
            'program_title' => 'テスト番組',
            'rating' => 3.0,
        ]);

        // 平均評価APIをテスト
        $response = $this->actingAs($user1)->get(route('api.program.rating', ['program_id' => 'test_program']));
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        
        $data = $response->json('data');
        $this->assertEquals(4.0, $data['average_rating']); // (5.0 + 3.0) / 2 = 4.0
        $this->assertEquals(2, $data['review_count']);
    }

    /** @test */
    public function user_can_update_rating_on_existing_post()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'rating' => 3.0,
        ]);

        $response = $this->actingAs($user)->post(route('myreview.update', $post->id), [
            'id' => $post->id,
            'user_id' => $user->id,
            'program_id' => $post->program_id,
            'program_title' => $post->program_title,
            'title' => '更新されたタイトル',
            'body' => '更新された本文',
            'rating' => 5.0,
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'rating' => 5.0,
        ]);
    }

    /** @test */
    public function existing_posts_have_default_rating()
    {
        // マイグレーションで既存の投稿には3.0のデフォルト評価が付与される
        $user = User::factory()->create();
        
        // 評価なしで投稿を直接作成（マイグレーション前の状態をシミュレート）
        $post = Post::create([
            'user_id' => $user->id,
            'program_id' => 'test_program',
            'program_title' => 'テスト番組',
            'title' => 'テストタイトル',
            'body' => 'テスト本文',
            'station_id' => 'TBS',
            // ratingを指定しない
        ]);

        $post->refresh();
        
        // デフォルトでnullが設定されるはず
        // 実際のマイグレーションでは既存データを3.0で更新する
        $this->assertNotNull($post->rating);
    }
}
