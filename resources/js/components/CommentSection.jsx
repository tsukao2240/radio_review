import React, { useState, useEffect } from 'react';
import axios from 'axios';

/**
 * コメントセクションコンポーネント
 * @param {number} postId - 投稿ID
 * @param {number} initialCommentsCount - 初期コメント数
 * @param {boolean} isAuthenticated - ユーザーがログイン中か
 * @param {number} currentUserId - 現在のユーザーID
 */
export default function CommentSection({ postId, initialCommentsCount = 0, isAuthenticated = false, currentUserId = null }) {
    const [comments, setComments] = useState([]);
    const [commentsCount, setCommentsCount] = useState(initialCommentsCount);
    const [expanded, setExpanded] = useState(false);
    const [loading, setLoading] = useState(false);
    const [newComment, setNewComment] = useState('');
    const [submitting, setSubmitting] = useState(false);

    useEffect(() => {
        if (expanded && comments.length === 0) {
            loadComments();
        }
    }, [expanded]);

    const loadComments = async () => {
        setLoading(true);
        try {
            const response = await axios.get('/api/posts/comments', {
                params: { post_id: postId }
            });
            if (response.data.success) {
                setComments(response.data.data.data || []);
                setCommentsCount(response.data.data.total || 0);
            }
        } catch (error) {
            console.error('コメントの読み込みに失敗:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!isAuthenticated) {
            window.location.href = '/login';
            return;
        }

        if (!newComment.trim()) {
            alert('コメントを入力してください');
            return;
        }

        if (newComment.length > 1000) {
            alert('コメントは1000文字以内で入力してください');
            return;
        }

        setSubmitting(true);
        try {
            const response = await axios.post('/api/posts/comment', {
                post_id: postId,
                body: newComment
            });

            if (response.data.success) {
                setComments([response.data.data.comment, ...comments]);
                setCommentsCount(response.data.data.comments_count);
                setNewComment('');
            }
        } catch (error) {
            console.error('コメントの投稿に失敗:', error);
            alert(error.response?.data?.message || 'エラーが発生しました');
        } finally {
            setSubmitting(false);
        }
    };

    const handleDelete = async (commentId) => {
        if (!confirm('コメントを削除しますか？')) return;

        try {
            const response = await axios.post('/api/posts/comment/delete', {
                comment_id: commentId
            });

            if (response.data.success) {
                setComments(comments.filter(c => c.id !== commentId));
                setCommentsCount(commentsCount - 1);
            }
        } catch (error) {
            console.error('コメントの削除に失敗:', error);
            alert(error.response?.data?.message || 'エラーが発生しました');
        }
    };

    return (
        <div className="comment-section mt-2">
            <button
                className="btn btn-sm btn-outline-secondary d-inline-flex align-items-center"
                onClick={() => setExpanded(!expanded)}
            >
                <span className="me-1">💬</span>
                <span>{commentsCount}</span>
                <span className="ms-1">{expanded ? '▲' : '▼'}</span>
            </button>

            {expanded && (
                <div className="mt-3">
                    {isAuthenticated && (
                        <form onSubmit={handleSubmit} className="mb-3">
                            <div className="mb-2">
                                <textarea
                                    className="form-control form-control-sm"
                                    rows="3"
                                    placeholder="コメントを入力（最大1000文字）"
                                    value={newComment}
                                    onChange={(e) => setNewComment(e.target.value)}
                                    maxLength={1000}
                                ></textarea>
                                <small className="text-muted">{newComment.length} / 1000</small>
                            </div>
                            <button
                                type="submit"
                                className="btn btn-primary btn-sm"
                                disabled={submitting || !newComment.trim()}
                            >
                                {submitting ? '送信中...' : 'コメント'}
                            </button>
                        </form>
                    )}

                    {loading ? (
                        <div className="text-center py-3">
                            <div className="spinner-border spinner-border-sm" role="status">
                                <span className="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    ) : comments.length > 0 ? (
                        <div className="comments-list" style={{ maxHeight: '400px', overflowY: 'auto' }}>
                            {comments.map((comment) => (
                                <div key={comment.id} className="border-bottom pb-2 mb-2">
                                    <div className="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong className="text-primary">{comment.user?.name || '匿名'}</strong>
                                            <small className="text-muted ms-2">
                                                {new Date(comment.created_at).toLocaleString('ja-JP')}
                                            </small>
                                        </div>
                                        {currentUserId === comment.user_id && (
                                            <button
                                                className="btn btn-sm btn-outline-danger"
                                                onClick={() => handleDelete(comment.id)}
                                                title="削除"
                                            >
                                                ×
                                            </button>
                                        )}
                                    </div>
                                    <p className="mb-0 mt-1" style={{ whiteSpace: 'pre-wrap' }}>
                                        {comment.body}
                                    </p>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <p className="text-muted text-center py-3">コメントはまだありません</p>
                    )}
                </div>
            )}
        </div>
    );
}
