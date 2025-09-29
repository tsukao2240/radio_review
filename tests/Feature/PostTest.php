<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * ホームページが正常に表示されるかテスト
     */
    public function test_home_page_displays_successfully()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertViewIs('home.top');
    }

    /**
     * 投稿一覧ページが正常に表示されるかテスト
     */
    public function test_posts_index_displays_successfully()
    {
        $response = $this->get('/post');
        $response->assertStatus(200);
        $response->assertViewIs('post.index');
    }

    /**
     * 認証済みユーザーが投稿作成ページにアクセスできるかテスト
     */
    public function test_authenticated_user_can_access_post_create()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/post/create');
        $response->assertStatus(200);
        $response->assertViewIs('post.create');
    }

    /**
     * 未認証ユーザーが投稿作成ページにアクセスできないかテスト
     */
    public function test_unauthenticated_user_cannot_access_post_create()
    {
        $response = $this->get('/post/create');
        $response->assertRedirect('/login');
    }

    /**
     * 投稿が正常に作成されるかテスト
     */
    public function test_authenticated_user_can_create_post()
    {
        $user = User::factory()->create();

        $postData = [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
            'radio_program_id' => 1, // 実際のradio_programがある場合
        ];

        $response = $this->actingAs($user)->post('/post', $postData);

        // 投稿後のリダイレクトをテスト
        $response->assertRedirect();

        // データベースに保存されているかテスト
        $this->assertDatabaseHas('posts', [
            'title' => $postData['title'],
            'content' => $postData['content'],
            'user_id' => $user->id,
        ]);
    }

    /**
     * バリデーションエラーのテスト
     */
    public function test_post_creation_validation()
    {
        $user = User::factory()->create();

        // タイトルが空の場合
        $response = $this->actingAs($user)->post('/post', [
            'title' => '',
            'content' => $this->faker->paragraph(),
        ]);

        $response->assertSessionHasErrors(['title']);
    }
}