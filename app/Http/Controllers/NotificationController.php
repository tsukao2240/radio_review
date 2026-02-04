<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->middleware('auth');
        $this->notificationService = $notificationService;
    }

    /**
     * 未読通知を取得
     */
    public function getUnread()
    {
        $notifications = $this->notificationService->getUnreadNotifications(Auth::user());

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $notifications->count()
        ]);
    }

    /**
     * 全通知を取得
     */
    public function getAll(Request $request)
    {
        $limit = $request->input('limit', 50);
        $notifications = $this->notificationService->getAllNotifications(Auth::user(), $limit);

        return response()->json([
            'success' => true,
            'notifications' => $notifications
        ]);
    }

    /**
     * 通知を既読にする
     */
    public function markAsRead(Request $request)
    {
        $request->validate([
            'notification_id' => 'required|integer'
        ]);

        $success = $this->notificationService->markAsRead(
            $request->notification_id,
            Auth::user()
        );

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => '通知を既読にしました'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => '通知が見つかりません'
        ], 404);
    }

    /**
     * 全通知を既読にする
     */
    public function markAllAsRead()
    {
        $count = $this->notificationService->markAllAsRead(Auth::user());

        return response()->json([
            'success' => true,
            'message' => "{$count}件の通知を既読にしました",
            'count' => $count
        ]);
    }

    /**
     * 通知一覧画面を表示
     */
    public function index()
    {
        $notifications = $this->notificationService->getAllNotifications(Auth::user());
        return view('notifications.index', compact('notifications'));
    }
}
