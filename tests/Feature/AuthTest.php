<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_user_can_view_login_page()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
    }

    public function test_user_can_view_register_page()
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
    }

    public function test_user_can_register()
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);
    }

    public function test_user_can_login_with_correct_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_incorrect_password()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('logout'));

        $this->assertGuest();
    }

    public function test_unverified_user_cannot_create_post()
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $response = $this->actingAs($user)
            ->get(route('post.review', ['id' => 1]));

        $response->assertRedirect(route('verification.notice'));
    }

    public function test_verified_user_can_create_post()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)
            ->get(route('post.review', ['id' => 1]));

        $response->assertStatus(200);
    }

    /**
     * ログインページが正常に表示されるかテスト
     */
    public function test_login_page_displays_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /**
     * 会員登録ページが正常に表示されるかテスト
     */
    public function test_register_page_displays_successfully(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
    }

    /**
     * 認証機能の基本テスト
     */
    public function test_authentication_basic_functionality(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // 未認証状態をテスト
        $this->assertGuest();

        // actingAsでログイン状態をシミュレート
        $this->actingAs($user);
        $this->assertAuthenticatedAs($user);
    }

    /**
     * ユーザー作成とデータベース操作テスト
     */
    public function test_user_creation_and_database(): void
    {
        $userData = [
            'name' => 'テストユーザー',
            'email' => 'newuser@example.com',
            'password' => Hash::make('password123'),
        ];

        $user = User::create($userData);

        // データベースに保存されているかテスト
        $this->assertDatabaseHas('users', [
            'name' => 'テストユーザー',
            'email' => 'newuser@example.com',
        ]);

        // ユーザーオブジェクトの確認
        $this->assertEquals('テストユーザー', $user->name);
        $this->assertEquals('newuser@example.com', $user->email);
    }

    /**
     * パスワードハッシュ化テスト
     */
    public function test_password_hashing(): void
    {
        $plainPassword = 'password123';
        $hashedPassword = Hash::make($plainPassword);

        $this->assertTrue(Hash::check($plainPassword, $hashedPassword));
        $this->assertFalse(Hash::check('wrongpassword', $hashedPassword));
    }

    /**
     * メール検証機能の基本テスト
     */
    public function test_email_verification_basic(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // 未検証状態
        $this->assertFalse($user->hasVerifiedEmail());

        // 検証済み状態にする
        $user->markEmailAsVerified();
        $this->assertTrue($user->hasVerifiedEmail());
    }

    /**
     * ユーザーとポストの関係テスト
     */
    public function test_user_posts_relationship(): void
    {
        $user = User::factory()->create();

        // リレーションが定義されているかテスト
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->posts());
    }
}
