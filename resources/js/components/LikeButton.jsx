import React, { useState, useEffect } from 'react';
import axios from 'axios';

/**
 * いいねボタンコンポーネント
 * @param {number} postId - 投稿ID
 * @param {number} initialLikesCount - 初期いいね数
 * @param {boolean} initialLiked - 初期いいね状態
 * @param {boolean} isAuthenticated - ユーザーがログイン中か
 */
export default function LikeButton({ postId, initialLikesCount = 0, initialLiked = false, isAuthenticated = false }) {
    const [liked, setLiked] = useState(initialLiked);
    const [likesCount, setLikesCount] = useState(initialLikesCount);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        // ログイン中の場合、いいね状態を確認
        if (isAuthenticated && !initialLiked) {
            checkLikeStatus();
        }
    }, [postId, isAuthenticated]);

    const checkLikeStatus = async () => {
        try {
            const response = await axios.get('/api/posts/check-like', {
                params: { post_id: postId }
            });
            if (response.data.success) {
                setLiked(response.data.data.has_liked);
            }
        } catch (error) {
            console.error('いいね状態の確認に失敗:', error);
        }
    };

    const handleLike = async () => {
        if (!isAuthenticated) {
            window.location.href = '/login';
            return;
        }

        if (loading) return;

        setLoading(true);
        try {
            const endpoint = liked ? '/api/posts/unlike' : '/api/posts/like';
            const response = await axios.post(endpoint, { post_id: postId });

            if (response.data.success) {
                setLiked(!liked);
                setLikesCount(response.data.data.likes_count);
            }
        } catch (error) {
            console.error('いいね処理に失敗:', error);
            alert(error.response?.data?.message || 'エラーが発生しました');
        } finally {
            setLoading(false);
        }
    };

    return (
        <button
            className={`btn btn-sm ${liked ? 'btn-danger' : 'btn-outline-danger'} d-inline-flex align-items-center`}
            onClick={handleLike}
            disabled={loading}
            title={liked ? 'いいねを取り消す' : 'いいね'}
        >
            <span className="me-1">{liked ? '❤️' : '🤍'}</span>
            <span>{likesCount}</span>
        </button>
    );
}
