<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;

class CustomVerifyEmail extends VerifyEmail implements ShouldQueue
{
    use Queueable, InteractsWithQueue;

    /**
     * キュー処理のリトライ回数
     */
    public $tries = 3;

    /**
     * リトライ間隔（秒）
     */
    public $backoff = [60, 300, 900]; // 1分、5分、15分後にリトライ

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->queue = 'notifications';
        $this->delay(now()->addSeconds(5)); // 5秒後に送信
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * 送信レート制限のチェック
     *
     * @param mixed $notifiable
     * @param string $channel
     * @return bool
     */
    public function shouldSend($notifiable, $channel)
    {
        // 過去1時間に3回以上送信していない場合のみ送信
        $key = "email_verify_rate_limit:{$notifiable->id}";
        $attempts = Cache::get($key, 0);

        if ($attempts >= 3) {
            \Log::warning('Email verification rate limit exceeded', [
                'user_id' => $notifiable->id,
                'attempts' => $attempts
            ]);
            return false;
        }

        // 送信回数を記録（1時間の有効期限）
        Cache::put($key, $attempts + 1, 3600);

        return true;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('メールアドレスの確認 - ラジオレビューアプリ')
            ->greeting('こんにちは！')
            ->line('ラジオレビューアプリへの登録ありがとうございます。')
            ->line('以下のボタンをクリックしてメールアドレスを確認してください。')
            ->action('メールアドレスを確認', $this->verificationUrl($notifiable))
            ->line('このボタンをクリックできない場合は、以下のURLをブラウザにコピー&ペーストしてください：')
            ->line($this->verificationUrl($notifiable))
            ->line('このメールに心当たりがない場合は、このメールを無視してください。')
            ->salutation('ラジオレビューアプリチーム');
    }

    /**
     * メール送信失敗時の処理
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception)
    {
        \Log::error('Email verification notification failed', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
