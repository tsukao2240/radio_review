<?php

namespace App\Services;

use App\Notification;
use App\User;

class NotificationService
{
    /**
     * 通知を作成
     * 
     * @param User $user
     * @param string $type
     * @param string $title
     * @param string $message
     * @param array|null $data
     * @return Notification
     */
    public function create(User $user, string $type, string $title, string $message, ?array $data = null): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data
        ]);
    }

    /**
     * 録音開始通知
     * 
     * @param User $user
     * @param array $recordingInfo
     * @return Notification
     */
    public function notifyRecordingStart(User $user, array $recordingInfo): Notification
    {
        return $this->create(
            $user,
            'recording_start',
            '録音開始',
            "「{$recordingInfo['title']}」の録音を開始しました",
            $recordingInfo
        );
    }

    /**
     * 録音完了通知
     * 
     * @param User $user
     * @param array $recordingInfo
     * @return Notification
     */
    public function notifyRecordingComplete(User $user, array $recordingInfo): Notification
    {
        return $this->create(
            $user,
            'recording_complete',
            '録音完了',
            "「{$recordingInfo['title']}」の録音が完了しました",
            $recordingInfo
        );
    }

    /**
     * 録音失敗通知
     * 
     * @param User $user
     * @param array $recordingInfo
     * @param string $error
     * @return Notification
     */
    public function notifyRecordingFailed(User $user, array $recordingInfo, string $error): Notification
    {
        return $this->create(
            $user,
            'recording_failed',
            '録音失敗',
            "「{$recordingInfo['title']}」の録音に失敗しました: {$error}",
            array_merge($recordingInfo, ['error' => $error])
        );
    }

    /**
     * お気に入り番組放送開始通知
     * 
     * @param User $user
     * @param string $programTitle
     * @param string $stationId
     * @return Notification
     */
    public function notifyFavoriteProgramBroadcast(User $user, string $programTitle, string $stationId): Notification
    {
        return $this->create(
            $user,
            'favorite_broadcast',
            'お気に入り番組放送中',
            "「{$programTitle}」が放送中です",
            [
                'program_title' => $programTitle,
                'station_id' => $stationId
            ]
        );
    }

    /**
     * ユーザーの未読通知を取得
     * 
     * @param User $user
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUnreadNotifications(User $user, int $limit = 10)
    {
        return $user->notifications()
            ->unread()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * ユーザーの全通知を取得
     * 
     * @param User $user
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllNotifications(User $user, int $limit = 50)
    {
        return $user->notifications()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * 通知を既読にする
     * 
     * @param int $notificationId
     * @param User $user
     * @return bool
     */
    public function markAsRead(int $notificationId, User $user): bool
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $user->id)
            ->first();

        if ($notification) {
            $notification->markAsRead();
            return true;
        }

        return false;
    }

    /**
     * 全通知を既読にする
     * 
     * @param User $user
     * @return int
     */
    public function markAllAsRead(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);
    }

    /**
     * 古い通知を削除
     * 
     * @param int $days
     * @return int
     */
    public function deleteOldNotifications(int $days = 30): int
    {
        return Notification::where('created_at', '<', now()->subDays($days))
            ->delete();
    }
}
