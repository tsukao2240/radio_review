import React, { useState, useEffect } from 'react';
import axios from 'axios';

const NotificationCenter = () => {
    const [notifications, setNotifications] = useState([]);
    const [unreadCount, setUnreadCount] = useState(0);
    const [isOpen, setIsOpen] = useState(false);
    const [loading, setLoading] = useState(false);

    // 未読通知を取得
    const fetchUnreadNotifications = async () => {
        try {
            setLoading(true);
            const response = await axios.get('/api/notifications/unread');
            if (response.data.success) {
                setNotifications(response.data.notifications);
                setUnreadCount(response.data.unread_count);
            }
        } catch (error) {
            console.error('通知取得エラー:', error);
        } finally {
            setLoading(false);
        }
    };

    // 通知を既読にする
    const markAsRead = async (notificationId) => {
        try {
            const response = await axios.post('/api/notifications/mark-read', {
                notification_id: notificationId
            });

            if (response.data.success) {
                // 通知リストから削除
                setNotifications(prev => prev.filter(n => n.id !== notificationId));
                setUnreadCount(prev => Math.max(0, prev - 1));
            }
        } catch (error) {
            console.error('既読エラー:', error);
        }
    };

    // 全て既読にする
    const markAllAsRead = async () => {
        try {
            const response = await axios.post('/api/notifications/mark-all-read');

            if (response.data.success) {
                setNotifications([]);
                setUnreadCount(0);
            }
        } catch (error) {
            console.error('全既読エラー:', error);
        }
    };

    // 定期的に通知をチェック (30秒ごと)
    useEffect(() => {
        fetchUnreadNotifications();
        const interval = setInterval(fetchUnreadNotifications, 30000);
        return () => clearInterval(interval);
    }, []);

    // 通知タイプごとのアイコン
    const getNotificationIcon = (type) => {
        switch (type) {
            case 'recording_start':
                return '🔴';
            case 'recording_complete':
                return '✅';
            case 'recording_failed':
                return '❌';
            case 'favorite_broadcast':
                return '⭐';
            case 'post_liked':
                return '❤️';
            case 'post_commented':
                return '💬';
            default:
                return '🔔';
        }
    };

    // 通知タイプごとの色
    const getNotificationColor = (type) => {
        switch (type) {
            case 'recording_start':
                return 'info';
            case 'recording_complete':
                return 'success';
            case 'recording_failed':
                return 'danger';
            case 'favorite_broadcast':
                return 'warning';
            case 'post_liked':
                return 'danger';
            case 'post_commented':
                return 'primary';
            default:
                return 'secondary';
        }
    };

    // 時間のフォーマット
    const formatTime = (timestamp) => {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = Math.floor((now - date) / 1000); // 秒単位

        if (diff < 60) return 'たった今';
        if (diff < 3600) return `${Math.floor(diff / 60)}分前`;
        if (diff < 86400) return `${Math.floor(diff / 3600)}時間前`;
        return `${Math.floor(diff / 86400)}日前`;
    };

    return (
        <div className="notification-center">
            {/* 通知ベルアイコン */}
            <div className="position-relative">
                <button
                    className="btn btn-link text-dark position-relative"
                    onClick={() => setIsOpen(!isOpen)}
                    aria-label="通知"
                >
                    <i className="fas fa-bell fa-lg"></i>
                    {unreadCount > 0 && (
                        <span className="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {unreadCount > 99 ? '99+' : unreadCount}
                        </span>
                    )}
                </button>

                {/* 通知ドロップダウン */}
                {isOpen && (
                    <div className="notification-dropdown card shadow-lg position-absolute end-0 mt-2" style={{ width: '400px', maxHeight: '600px', zIndex: 1050 }}>
                        <div className="card-header d-flex justify-content-between align-items-center">
                            <h6 className="mb-0">通知</h6>
                            {unreadCount > 0 && (
                                <button
                                    className="btn btn-sm btn-link text-primary"
                                    onClick={markAllAsRead}
                                >
                                    全て既読
                                </button>
                            )}
                        </div>

                        <div className="card-body p-0" style={{ maxHeight: '500px', overflowY: 'auto' }}>
                            {loading ? (
                                <div className="text-center p-4">
                                    <div className="spinner-border text-primary" role="status">
                                        <span className="visually-hidden">読み込み中...</span>
                                    </div>
                                </div>
                            ) : notifications.length === 0 ? (
                                <div className="text-center text-muted p-4">
                                    <i className="fas fa-inbox fa-3x mb-3"></i>
                                    <p>新しい通知はありません</p>
                                </div>
                            ) : (
                                <div className="list-group list-group-flush">
                                    {notifications.map((notification) => (
                                        <div
                                            key={notification.id}
                                            className={`list-group-item list-group-item-action border-start border-4 border-${getNotificationColor(notification.type)}`}
                                            onClick={() => markAsRead(notification.id)}
                                            style={{ cursor: 'pointer' }}
                                        >
                                            <div className="d-flex w-100 justify-content-between align-items-start">
                                                <div className="flex-grow-1">
                                                    <div className="d-flex align-items-center mb-1">
                                                        <span className="me-2" style={{ fontSize: '1.2em' }}>
                                                            {getNotificationIcon(notification.type)}
                                                        </span>
                                                        <h6 className="mb-0">{notification.title}</h6>
                                                    </div>
                                                    <p className="mb-1 small">{notification.message}</p>
                                                    <small className="text-muted">
                                                        {formatTime(notification.created_at)}
                                                    </small>
                                                </div>
                                                <button
                                                    className="btn btn-sm btn-link text-muted ms-2"
                                                    onClick={(e) => {
                                                        e.stopPropagation();
                                                        markAsRead(notification.id);
                                                    }}
                                                >
                                                    <i className="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>

                        {notifications.length > 0 && (
                            <div className="card-footer text-center">
                                <a href="/notifications" className="btn btn-link text-primary">
                                    全ての通知を見る
                                </a>
                            </div>
                        )}
                    </div>
                )}
            </div>

            {/* 背景クリックでクローズ */}
            {isOpen && (
                <div
                    className="position-fixed top-0 start-0 w-100 h-100"
                    style={{ zIndex: 1040 }}
                    onClick={() => setIsOpen(false)}
                />
            )}
        </div>
    );
};

export default NotificationCenter;
