<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * ログインページが正常に表示されるかテスト
     */
    public function test_login_page_displays_successfully()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /**
     * 会員登録ページが正常に表示されるかテスト
     */
    public function test_register_page_displays_successfully()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
    }

    /**
     * 正しい認証情報でログインできるかテスト
     */
    public function test_user_can_login_with_correct_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/home');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * 間違った認証情報でログインできないかテスト
     */
    public function test_user_cannot_login_with_incorrect_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /**
     * 新規ユーザー登録ができるかテスト
     */
    public function test_user_can_register()
    {
        $userData = [
            'name' => 'テストユーザー',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        // リダイレクトをテスト
        $response->assertRedirect('/home');

        // データベースに保存されているかテスト
        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'newuser@example.com',
        ]);

        // ログイン状態をテスト
        $this->assertAuthenticated();
    }

    /**
     * 会員登録のバリデーションテスト
     */
    public function test_registration_validation()
    {
        // メールアドレスが既に存在する場合
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post('/register', [
            'name' => 'テストユーザー',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /**
     * ログアウトができるかテスト
     */
    public function test_user_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}