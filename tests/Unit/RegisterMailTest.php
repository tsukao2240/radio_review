<?php

namespace Tests\Unit;

use App\Mail\RegisterMail;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterMailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * RegisterMailが正しく構築されるかテスト
     */
    public function test_register_mail_can_be_built(): void
    {
        $user = User::factory()->create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
        ]);

        $mail = new RegisterMail($user);
        $builtMail = $mail->build();

        // メールの件名をテスト
        $this->assertEquals('会員登録完了のお知らせ - ラジオレビューアプリ', $builtMail->subject);

        // メールビューをテスト
        $this->assertEquals('email.register', $builtMail->view);

        // ビューデータをテスト
        $this->assertEquals('テストユーザー', $builtMail->viewData['userName']);
        $this->assertEquals('test@example.com', $builtMail->viewData['userEmail']);
        $this->assertArrayHasKey('loginUrl', $builtMail->viewData);
    }

    /**
     * ユーザー名がnullの場合のデフォルト値テスト
     */
    public function test_register_mail_with_null_user_name(): void
    {
        $user = (object) ['name' => null, 'email' => 'test@example.com'];

        $mail = new RegisterMail($user);
        $builtMail = $mail->build();

        // デフォルト値がセットされているかテスト
        $this->assertEquals('ユーザー', $builtMail->viewData['userName']);
    }

    /**
     * キュー設定のテスト
     */
    public function test_register_mail_queue_configuration(): void
    {
        $user = User::factory()->create();
        $mail = new RegisterMail($user);

        // キューが設定されているかテスト
        $this->assertEquals('emails', $mail->queue);

        // リトライ設定のテスト
        $this->assertEquals(3, $mail->tries);
        $this->assertEquals([60, 300, 900], $mail->backoff);
    }
}