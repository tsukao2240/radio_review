<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\User;
use App\Post;
use App\RadioProgram;
use Illuminate\Support\Facades\Hash;

class MypageControllerTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $otherUser;
    private $program;
    private $post;

    protected function setUp(): void
    {
        parent::setUp();

        // テストユーザー作成
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->otherUser = User::create([
            'name' => 'Other User',
            'email' => 'other@example.com',
            'password' => Hash::make('password'),
        ]);

        // テスト用番組データ作成
        $this->program = RadioProgram::create([
            'id' => 'test_program_001',
            'title' => 'テスト番組',
            'cast' => 'テスト出演者',
            'date' => '20250101',
            'start' => '12:00',
            'end' => '13:00',
            'station_id' => 'TBS'
        ]);

        // テスト用投稿作成
        $this->post = Post::create([
            'program_id' => $this->program->id,
            'user_id' => $this->user->id,
            'program_title' => $this->program->title,
            'title' => 'テストレビュー',
            'body' => 'テストレビュー本文'
        ]);
    }

    /**
     * 未認証ユーザーはマイページにアクセスできない
     */
    public function test_unauthenticated_user_cannot_access_mypage()
    {
        $response = $this->get('/my');

        $response->assertRedirect('/login');
    }

    /**
     * 認証済みユーザーは自分の投稿一覧を表示できる
     */
    public function test_authenticated_user_can_view_own_posts()
    {
        $response = $this->actingAs($this->user)->get('/my');

        $response->assertStatus(200);
        $response->assertViewIs('mypage.index');
        $response->assertViewHas('posts');
        
        $posts = $response->viewData('posts');
        $this->assertEquals(1, $posts->count());
        $this->assertEquals($this->post->id, $posts->first()->id);
    }

    /**
     * 他のユーザーの投稿は表示されない
     */
    public function test_user_only_sees_own_posts()
    {
        // 他のユーザーの投稿を作成
        $otherPost = Post::create([
            'program_id' => $this->program->id,
            'user_id' => $this->otherUser->id,
            'program_title' => $this->program->title,
            'title' => '他のユーザーのレビュー',
            'body' => '他のユーザーのレビュー本文'
        ]);

        $response = $this->actingAs($this->user)->get('/my');

        $response->assertStatus(200);
        $posts = $response->viewData('posts');
        
        // 自分の投稿のみが表示される
        $this->assertEquals(1, $posts->count());
        $this->assertEquals($this->post->id, $posts->first()->id);
    }

    /**
     * 投稿一覧がページネーションされる
     */
    public function test_posts_are_paginated()
    {
        // 15件の投稿を追加作成
        for ($i = 1; $i <= 15; $i++) {
            Post::create([
                'program_id' => $this->program->id,
                'user_id' => $this->user->id,
                'program_title' => $this->program->title,
                'title' => 'テストレビュー' . $i,
                'body' => 'テストレビュー本文' . $i
            ]);
        }

        $response = $this->actingAs($this->user)->get('/my');

        $response->assertStatus(200);
        $posts = $response->viewData('posts');
        
        // 10件ずつページネーション
        $this->assertEquals(10, $posts->perPage());
        $this->assertEquals(16, $posts->total()); // 既存1件 + 新規15件
    }

    /**
     * 編集画面が表示できる
     */
    public function test_user_can_view_edit_page()
    {
        $response = $this->actingAs($this->user)
            ->get('/my/edit/' . $this->post->program_id);

        $response->assertStatus(200);
        $response->assertViewIs('mypage.edit');
        $response->assertViewHas(['post', 'program']);
    }

    /**
     * 未認証ユーザーは編集画面にアクセスできない
     */
    public function test_unauthenticated_user_cannot_view_edit_page()
    {
        $response = $this->get('/my/edit/' . $this->post->program_id);

        $response->assertRedirect('/login');
    }

    /**
     * レビューを編集できる
     */
    public function test_user_can_update_own_post()
    {
        $updatedData = [
            'id' => $this->post->id,
            'title' => '更新されたレビュー',
            'body' => '更新されたレビュー本文'
        ];

        $response = $this->actingAs($this->user)
            ->post('/my/edit/' . $this->post->program_id, $updatedData);

        $response->assertRedirect('/my');
        $response->assertSessionHas('message', '編集が完了しました');

        // データベースが更新されているか確認
        $this->assertDatabaseHas('posts', [
            'id' => $this->post->id,
            'title' => '更新されたレビュー',
            'body' => '更新されたレビュー本文'
        ]);
    }

    /**
     * 未認証ユーザーはレビューを編集できない
     */
    public function test_unauthenticated_user_cannot_update_post()
    {
        $updatedData = [
            'id' => $this->post->id,
            'title' => '更新されたレビュー',
            'body' => '更新されたレビュー本文'
        ];

        $response = $this->post('/my/edit/' . $this->post->program_id, $updatedData);

        $response->assertRedirect('/login');
    }

    /**
     * レビューを削除できる
     */
    public function test_user_can_delete_own_post()
    {
        $response = $this->actingAs($this->user)
            ->post('/my', ['id' => $this->post->id]);

        $response->assertRedirect();
        $response->assertSessionHas('message', '削除しました');

        // データベースから削除されているか確認
        $this->assertDatabaseMissing('posts', [
            'id' => $this->post->id
        ]);
    }

    /**
     * 未認証ユーザーはレビューを削除できない
     */
    public function test_unauthenticated_user_cannot_delete_post()
    {
        $response = $this->post('/my', ['id' => $this->post->id]);

        $response->assertRedirect('/login');

        // データベースに残っているか確認
        $this->assertDatabaseHas('posts', [
            'id' => $this->post->id
        ]);
    }

    /**
     * 存在しない投稿を編集しようとすると404エラー
     */
    public function test_editing_nonexistent_post_returns_404()
    {
        $response = $this->actingAs($this->user)
            ->get('/my/edit/nonexistent_program_id');

        $response->assertStatus(404);
    }

    /**
     * 存在しない投稿を更新しようとすると404エラー
     */
    public function test_updating_nonexistent_post_returns_404()
    {
        $response = $this->actingAs($this->user)
            ->post('/my/edit/' . $this->post->program_id, [
                'id' => 99999,
                'title' => 'テスト',
                'body' => 'テスト'
            ]);

        $response->assertStatus(404);
    }

    /**
     * 存在しない投稿を削除しようとすると404エラー
     */
    public function test_deleting_nonexistent_post_returns_404()
    {
        $response = $this->actingAs($this->user)
            ->post('/my', ['id' => 99999]);

        $response->assertStatus(404);
    }

    /**
     * 投稿一覧に放送局IDが含まれることを確認
     */
    public function test_posts_include_station_id()
    {
        $response = $this->actingAs($this->user)->get('/my');

        $response->assertStatus(200);
        $posts = $response->viewData('posts');
        
        $this->assertNotNull($posts->first()->station_id);
        $this->assertEquals('TBS', $posts->first()->station_id);
    }

    /**
     * 投稿がない場合でもページが正常に表示される
     */
    public function test_mypage_displays_correctly_with_no_posts()
    {
        // 既存の投稿を削除
        Post::where('user_id', $this->user->id)->delete();

        $response = $this->actingAs($this->user)->get('/my');

        $response->assertStatus(200);
        $response->assertViewIs('mypage.index');
        
        $posts = $response->viewData('posts');
        $this->assertEquals(0, $posts->count());
    }
}
