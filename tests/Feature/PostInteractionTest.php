<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\Post;
use App\PostLike;
use App\PostComment;
use App\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostInteractionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_like_a_post()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $author = User::factory()->create(['email_verified_at' => now()]);
        
        $post = Post::factory()->create([
            'user_id' => $author->id,
            'rating' => 4.0,
            'likes_count' => 0,
        ]);

        $response = $this->actingAs($user)->postJson('/api/posts/like', [
            'post_id' => $post->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        
        // いいねが記録されている
        $this->assertDatabaseHas('post_likes', [
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);
        
        // カウントが増加している
        $post->refresh();
        $this->assertEquals(1, $post->likes_count);
        
        // 通知が作成されている（自分以外）
        $this->assertDatabaseHas('notifications', [
            'user_id' => $author->id,
            'type' => 'post_liked',
        ]);
    }

    /** @test */
    public function user_can_unlike_a_post()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $author = User::factory()->create(['email_verified_at' => now()]);
        
        $post = Post::factory()->create([
            'user_id' => $author->id,
            'rating' => 4.0,
            'likes_count' => 1,
        ]);
        
        // 事前にいいねを作成
        PostLike::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->postJson('/api/posts/unlike', [
            'post_id' => $post->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        
        // いいねが削除されている
        $this->assertDatabaseMissing('post_likes', [
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);
        
        // カウントが減少している
        $post->refresh();
        $this->assertEquals(0, $post->likes_count);
    }

    /** @test */
    public function user_cannot_like_same_post_twice()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $author = User::factory()->create(['email_verified_at' => now()]);
        
        $post = Post::factory()->create([
            'user_id' => $author->id,
            'rating' => 4.0,
            'likes_count' => 0,
        ]);

        // 1回目のいいね
        $this->actingAs($user)->postJson('/api/posts/like', [
            'post_id' => $post->id,
        ]);

        // 2回目のいいね（エラーになる）
        $response = $this->actingAs($user)->postJson('/api/posts/like', [
            'post_id' => $post->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
        ]);
        
        // カウントは1のまま
        $post->refresh();
        $this->assertEquals(1, $post->likes_count);
    }

    /** @test */
    public function user_can_check_if_they_liked_a_post()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $post = Post::factory()->create(['rating' => 4.0]);
        
        // いいねしていない状態
        $response = $this->actingAs($user)->getJson('/api/posts/check-like?post_id=' . $post->id);
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => ['liked' => false],
        ]);
        
        // いいねする
        PostLike::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);
        
        // いいねしている状態
        $response = $this->actingAs($user)->getJson('/api/posts/check-like?post_id=' . $post->id);
        
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => ['liked' => true],
        ]);
    }

    /** @test */
    public function user_cannot_like_without_authentication()
    {
        $post = Post::factory()->create(['rating' => 4.0]);

        $response = $this->postJson('/api/posts/like', [
            'post_id' => $post->id,
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function user_can_add_comment_to_post()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $author = User::factory()->create(['email_verified_at' => now()]);
        
        $post = Post::factory()->create([
            'user_id' => $author->id,
            'rating' => 4.0,
            'comments_count' => 0,
        ]);

        $response = $this->actingAs($user)->postJson('/api/posts/comment', [
            'post_id' => $post->id,
            'body' => '素晴らしいレビューですね！',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        
        // コメントが記録されている
        $this->assertDatabaseHas('post_comments', [
            'post_id' => $post->id,
            'user_id' => $user->id,
            'body' => '素晴らしいレビューですね！',
        ]);
        
        // カウントが増加している
        $post->refresh();
        $this->assertEquals(1, $post->comments_count);
        
        // 通知が作成されている
        $this->assertDatabaseHas('notifications', [
            'user_id' => $author->id,
            'type' => 'post_commented',
        ]);
    }

    /** @test */
    public function comment_body_is_required()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $post = Post::factory()->create(['rating' => 4.0]);

        $response = $this->actingAs($user)->postJson('/api/posts/comment', [
            'post_id' => $post->id,
            'body' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('body');
    }

    /** @test */
    public function comment_body_cannot_exceed_1000_characters()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $post = Post::factory()->create(['rating' => 4.0]);

        $response = $this->actingAs($user)->postJson('/api/posts/comment', [
            'post_id' => $post->id,
            'body' => str_repeat('あ', 1001),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('body');
    }

    /** @test */
    public function user_can_delete_own_comment()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $post = Post::factory()->create([
            'rating' => 4.0,
            'comments_count' => 1,
        ]);
        
        $comment = PostComment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'body' => 'テストコメント',
        ]);

        $response = $this->actingAs($user)->postJson('/api/posts/comment/delete', [
            'comment_id' => $comment->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        
        // コメントが削除されている
        $this->assertDatabaseMissing('post_comments', [
            'id' => $comment->id,
        ]);
        
        // カウントが減少している
        $post->refresh();
        $this->assertEquals(0, $post->comments_count);
    }

    /** @test */
    public function user_cannot_delete_others_comment()
    {
        $user1 = User::factory()->create(['email_verified_at' => now()]);
        $user2 = User::factory()->create(['email_verified_at' => now()]);
        $post = Post::factory()->create([
            'rating' => 4.0,
            'comments_count' => 1,
        ]);
        
        $comment = PostComment::create([
            'post_id' => $post->id,
            'user_id' => $user1->id,
            'body' => 'user1のコメント',
        ]);

        // user2がuser1のコメントを削除しようとする
        $response = $this->actingAs($user2)->postJson('/api/posts/comment/delete', [
            'comment_id' => $comment->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false,
        ]);
        
        // コメントは削除されていない
        $this->assertDatabaseHas('post_comments', [
            'id' => $comment->id,
        ]);
    }

    /** @test */
    public function user_can_get_comments_for_post()
    {
        $user1 = User::factory()->create(['email_verified_at' => now(), 'name' => 'ユーザー1']);
        $user2 = User::factory()->create(['email_verified_at' => now(), 'name' => 'ユーザー2']);
        $post = Post::factory()->create(['rating' => 4.0]);
        
        PostComment::create([
            'post_id' => $post->id,
            'user_id' => $user1->id,
            'body' => '最初のコメント',
            'created_at' => now()->subHours(2),
        ]);
        
        PostComment::create([
            'post_id' => $post->id,
            'user_id' => $user2->id,
            'body' => '2番目のコメント',
            'created_at' => now()->subHours(1),
        ]);

        $response = $this->actingAs($user1)->getJson('/api/posts/comments?post_id=' . $post->id);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        
        $comments = $response->json('data.comments');
        $this->assertCount(2, $comments);
        
        // 新しいコメントが最初に表示される
        $this->assertEquals('2番目のコメント', $comments[0]['body']);
        $this->assertEquals('最初のコメント', $comments[1]['body']);
    }

    /** @test */
    public function liking_own_post_does_not_create_notification()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'rating' => 4.0,
        ]);

        // 自分の投稿にいいね
        $response = $this->actingAs($user)->postJson('/api/posts/like', [
            'post_id' => $post->id,
        ]);

        $response->assertStatus(200);
        
        // 通知は作成されない
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $user->id,
            'type' => 'post_liked',
        ]);
    }

    /** @test */
    public function commenting_on_own_post_does_not_create_notification()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        
        $post = Post::factory()->create([
            'user_id' => $user->id,
            'rating' => 4.0,
        ]);

        // 自分の投稿にコメント
        $response = $this->actingAs($user)->postJson('/api/posts/comment', [
            'post_id' => $post->id,
            'body' => '追記です',
        ]);

        $response->assertStatus(200);
        
        // 通知は作成されない
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $user->id,
            'type' => 'post_commented',
        ]);
    }
}
