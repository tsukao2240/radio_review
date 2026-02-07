<?php

namespace App\Services;

use App\Post;
use App\PostLike;
use App\PostComment;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 投稿へのインタラクション（いいね・コメント）を管理するサービスクラス
 */
class PostInteractionService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * 投稿にいいねする
     *
     * @param int $postId
     * @param int $userId
     * @return array
     */
    public function likePost($postId, $userId)
    {
        try {
            $post = Post::findOrFail($postId);
            
            // すでにいいねしているか確認
            if ($this->hasLiked($postId, $userId)) {
                return [
                    'success' => false,
                    'message' => 'すでにいいねしています',
                ];
            }

            DB::beginTransaction();

            // いいねを作成
            PostLike::create([
                'post_id' => $postId,
                'user_id' => $userId,
            ]);

            // カウンターをインクリメント
            $post->increment('likes_count');

            DB::commit();

            // 自分の投稿でない場合は通知
            if ($post->user_id !== $userId) {
                $this->notificationService->create(
                    User::find($post->user_id),
                    'post_liked',
                    'いいねされました',
                    "あなたの投稿「{$post->title}」にいいねがつきました",
                    ['post_id' => $postId]
                );
            }

            Log::info('Post liked', [
                'post_id' => $postId,
                'user_id' => $userId,
            ]);

            return [
                'success' => true,
                'message' => 'いいねしました',
                'data' => ['likes_count' => $post->fresh()->likes_count],
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error liking post: ' . $e->getMessage(), [
                'post_id' => $postId,
                'user_id' => $userId,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * 投稿のいいねを取り消す
     *
     * @param int $postId
     * @param int $userId
     * @return array
     */
    public function unlikePost($postId, $userId)
    {
        try {
            $post = Post::findOrFail($postId);

            DB::beginTransaction();

            // いいねを削除
            $deleted = PostLike::where('post_id', $postId)
                ->where('user_id', $userId)
                ->delete();

            if (!$deleted) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'いいねしていません',
                ];
            }

            // カウンターをデクリメント
            $post->decrement('likes_count');

            DB::commit();

            Log::info('Post unliked', [
                'post_id' => $postId,
                'user_id' => $userId,
            ]);

            return [
                'success' => true,
                'message' => 'いいねを取り消しました',
                'data' => ['likes_count' => $post->fresh()->likes_count],
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error unliking post: ' . $e->getMessage(), [
                'post_id' => $postId,
                'user_id' => $userId,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * 投稿にコメントを追加
     *
     * @param int $postId
     * @param int $userId
     * @param string $body
     * @return array
     */
    public function addComment($postId, $userId, $body)
    {
        try {
            $post = Post::findOrFail($postId);

            DB::beginTransaction();

            // コメントを作成
            $comment = PostComment::create([
                'post_id' => $postId,
                'user_id' => $userId,
                'body' => $body,
            ]);

            // カウンターをインクリメント
            $post->increment('comments_count');

            DB::commit();

            // 自分の投稿でない場合は通知
            if ($post->user_id !== $userId) {
                $this->notificationService->create(
                    User::find($post->user_id),
                    'post_commented',
                    'コメントがつきました',
                    "あなたの投稿「{$post->title}」にコメントがつきました",
                    ['post_id' => $postId, 'comment_id' => $comment->id]
                );
            }

            Log::info('Comment added', [
                'post_id' => $postId,
                'user_id' => $userId,
                'comment_id' => $comment->id,
            ]);

            return [
                'success' => true,
                'message' => 'コメントを投稿しました',
                'data' => [
                    'comment' => $comment->load('user'),
                    'comments_count' => $post->fresh()->comments_count,
                ],
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding comment: ' . $e->getMessage(), [
                'post_id' => $postId,
                'user_id' => $userId,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * コメントを削除（本人のみ）
     *
     * @param int $commentId
     * @param int $userId
     * @return array
     */
    public function deleteComment($commentId, $userId)
    {
        try {
            $comment = PostComment::findOrFail($commentId);

            // 本人確認
            if ($comment->user_id !== $userId) {
                return [
                    'success' => false,
                    'message' => '削除権限がありません',
                ];
            }

            DB::beginTransaction();

            $postId = $comment->post_id;
            $comment->delete();

            // カウンターをデクリメント
            Post::find($postId)->decrement('comments_count');

            DB::commit();

            Log::info('Comment deleted', [
                'comment_id' => $commentId,
                'user_id' => $userId,
                'post_id' => $postId,
            ]);

            return [
                'success' => true,
                'message' => 'コメントを削除しました',
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting comment: ' . $e->getMessage(), [
                'comment_id' => $commentId,
                'user_id' => $userId,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * 投稿のコメント一覧を取得
     *
     * @param int $postId
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getComments($postId, $perPage = 20)
    {
        try {
            return PostComment::where('post_id', $postId)
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

        } catch (\Exception $e) {
            Log::error('Error retrieving comments: ' . $e->getMessage(), [
                'post_id' => $postId,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * ユーザーが投稿にいいねしているか確認
     *
     * @param int $postId
     * @param int $userId
     * @return bool
     */
    public function hasLiked($postId, $userId)
    {
        return PostLike::where('post_id', $postId)
            ->where('user_id', $userId)
            ->exists();
    }
}
