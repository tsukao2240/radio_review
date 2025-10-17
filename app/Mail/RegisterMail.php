<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

class RegisterMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, InteractsWithQueue;

    /**
     * キュー処理のリトライ回数
     */
    public $tries = 3;

    /**
     * リトライ間隔（秒）
     */
    public $backoff = [60, 300, 900]; // 1分、5分、15分後にリトライ

    /**
     * ユーザー情報
     */
    protected $user;

    /**
     * Create a new message instance.
     *
     * @param $user ユーザー情報
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
        $this->queue = 'emails'; // 専用キュー
        $this->delay(now()->addSeconds(10)); // 10秒後に送信
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('会員登録完了のお知らせ - ラジオレビューアプリ')
            ->view('email.register')
            ->with([
                'userName' => $this->user->name ?? 'ユーザー',
                'userEmail' => $this->user->email ?? '',
                'loginUrl' => route('login')
            ]);
    }

    /**
     * メール送信失敗時の処理
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        // 送信失敗時のログ記録
        \Log::error('Registration email failed', [
            'user_id' => $this->user->id ?? null,
            'user_email' => $this->user->email ?? null,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
