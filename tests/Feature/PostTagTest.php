<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\Post;
use App\PostTag;
use App\RadioProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class PostTagTest extends TestCase
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
            ['name' => '考えさせられた', 'display_order' => 4, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    #[Test]
    public function user_can_create_post_with_tags()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $program = RadioProgram::factory()->create();
        $tag1 = PostTag::where('name', '感動した')->first();
        $tag2 = PostTag::where('name', '笑った')->first();
        
        $response = $this->actingAs($user)->post(route('post.store', ['id' => $program->id]), [
            'user_id' => $user->id,
            'program_id' => $program->id,
            'program_title' => 'テスト番組',
            'title' => 'テストレビュー',
            'body' => 'とても良い番組でした',
            'rating' => 5.0,
            'station_id' => 'TBS',
            'tags' => [$tag1->id, $tag2->id],
        ]);

        $response->assertRedirect();
        
        $post = Post::where('title', 'テストレビュー')->first();
        $this->assertNotNull($post);
        
        // タグが正しく関連付けられている
        $this->assertEquals(2, $post->tags->count());
        $this->assertTrue($post->tags->contains('name', '感動した'));
        $this->assertTrue($post->tags->contains('name', '笑った'));
    }

    #[Test]
    public function user_can_create_post_without_tags()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $program = RadioProgram::factory()->create();
        
        $response = $this->actingAs($user)->post(route('post.store', ['id' => $program->id]), [
            'user_id' => $user->id,
            'program_id' => $program->id,
            'program_title' => 'テスト番組',
            'title' => 'タグなしレビュー',
            'body' => 'タグをつけない投稿',
            'rating' => 3.0,
            'station_id' => 'TBS',
            // tagsを指定しない
        ]);

        $response->assertRedirect();
        
        $post = Post::where('title', 'タグなしレビュー')->first();
        $this->assertNotNull($post);
        $this->assertEquals(0, $post->tags->count());
    }

    #[Test]
    public function user_can_filter_posts_by_tag()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $tag1 = PostTag::where('name', '感動した')->first();
        $tag2 = PostTag::where('name', '笑った')->first();
        
        // タグ1を持つ投稿
        $program1 = RadioProgram::factory()->create();
        $post1 = Post::factory()->create([
            'user_id' => $user->id,
            'program_id' => $program1->id,
            'program_title' => '感動番組',
            'rating' => 5.0,
        ]);
        $post1->tags()->attach($tag1->id);
        
        // タグ2を持つ投稿
        $program2 = RadioProgram::factory()->create();
        $post2 = Post::factory()->create([
            'user_id' => $user->id,
            'program_id' => $program2->id,
            'program_title' => 'お笑い番組',
            'rating' => 4.0,
        ]);
        $post2->tags()->attach($tag2->id);
        
        // タグなしの投稿
        $program3 = RadioProgram::factory()->create();
        $post3 = Post::factory()->create([
            'user_id' => $user->id,
            'program_id' => $program3->id,
            'program_title' => 'その他番組',
            'rating' => 3.0,
        ]);

        // タグ1でフィルタ
        $response = $this->actingAs($user)->get(route('review.view') . '?tag_id=' . $tag1->id);
        
        $response->assertStatus(200);
        $response->assertSee('感動番組');
        $response->assertDontSee('お笑い番組');
        $response->assertDontSee('その他番組');
    }

    #[Test]
    public function user_can_update_tags_on_existing_post()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $tag1 = PostTag::where('name', '感動した')->first();
        $tag2 = PostTag::where('name', '笑った')->first();
        $tag3 = PostTag::where('name', '勉強になった')->first();
        
        // 最初にタグ1とタグ2を持つ投稿を作成
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'rating' => 4.0,
        ]);
        $post->tags()->attach([$tag1->id, $tag2->id]);
        
        $this->assertEquals(2, $post->tags->count());

        // タグ2とタグ3に更新
        $response = $this->actingAs($user)->post(route('myreview.update', $post->id), [
            'id' => $post->id,
            'user_id' => $user->id,
            'program_id' => $post->program_id,
            'program_title' => $post->program_title,
            'title' => '更新されたタイトル',
            'body' => '更新された本文',
            'rating' => 4.0,
            'tags' => [$tag2->id, $tag3->id],
        ]);

        $response->assertRedirect();
        
        $post->refresh();
        $this->assertEquals(2, $post->tags->count());
        $this->assertFalse($post->tags->contains('id', $tag1->id));
        $this->assertTrue($post->tags->contains('id', $tag2->id));
        $this->assertTrue($post->tags->contains('id', $tag3->id));
    }

    #[Test]
    public function user_can_remove_all_tags_from_post()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $tag1 = PostTag::where('name', '感動した')->first();
        
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'rating' => 4.0,
        ]);
        $post->tags()->attach($tag1->id);
        
        $this->assertEquals(1, $post->tags->count());

        // タグを空で更新
        $response = $this->actingAs($user)->post(route('myreview.update', $post->id), [
            'id' => $post->id,
            'user_id' => $user->id,
            'program_id' => $post->program_id,
            'program_title' => $post->program_title,
            'title' => '更新されたタイトル',
            'body' => '更新された本文',
            'rating' => 4.0,
            'tags' => [], // 空の配列
        ]);

        $response->assertRedirect();
        
        $post->refresh();
        $this->assertEquals(0, $post->tags->count());
    }

    #[Test]
    public function tags_validation_requires_valid_tag_ids()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $program = RadioProgram::factory()->create();
        
        // 存在しないタグIDで投稿
        $response = $this->actingAs($user)->post(route('post.store', ['id' => $program->id]), [
            'user_id' => $user->id,
            'program_id' => $program->id,
            'program_title' => 'テスト番組',
            'title' => 'テストレビュー',
            'body' => 'テスト本文',
            'rating' => 5.0,
            'station_id' => 'TBS',
            'tags' => [9999], // 存在しないID
        ]);

        $response->assertSessionHasErrors('tags.0');
    }

    #[Test]
    public function tags_are_displayed_in_post_list()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $tag = PostTag::where('name', '感動した')->first();
        $program = RadioProgram::factory()->create();
        
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'program_id' => $program->id,
            'program_title' => 'テスト番組',
            'rating' => 5.0,
        ]);
        $post->tags()->attach($tag->id);

        $response = $this->actingAs($user)->get(route('review.view'));
        
        $response->assertStatus(200);
        $response->assertSee('感動した');
    }

    #[Test]
    public function tags_are_displayed_in_mypage()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $tag = PostTag::where('name', '笑った')->first();
        $program = RadioProgram::factory()->create();
        
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'program_id' => $program->id,
            'program_title' => 'テスト番組',
            'rating' => 4.0,
        ]);
        $post->tags()->attach($tag->id);

        $response = $this->actingAs($user)->get(route('myreview.view'));
        
        $response->assertStatus(200);
        $response->assertSee('笑った');
    }

    #[Test]
    public function tags_are_ordered_by_display_order()
    {
        $tags = PostTag::ordered()->get();
        
        $this->assertEquals('感動した', $tags[0]->name);
        $this->assertEquals('笑った', $tags[1]->name);
        $this->assertEquals('勉強になった', $tags[2]->name);
        $this->assertEquals('考えさせられた', $tags[3]->name);
    }
}
