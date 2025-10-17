<?php

namespace App\Services;

use App\Post;
use App\RadioProgram;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 投稿（レビュー）に関するビジネスロジックを担当するサービスクラス
 */
class PostService
{
    /**
     * すべての投稿を取得する
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllPosts($perPage = 10)
    {
        try {
            $posts = DB::table('posts')
                ->select('posts.*', 'radio_programs.station_id', 'users.name')
                ->leftJoin('users', 'users.id', '=', 'posts.user_id')
                ->leftJoin('radio_programs', 'posts.program_id', '=', 'radio_programs.id')
                ->paginate($perPage);

            Log::info('All posts retrieved', [
                'total' => $posts->total()
            ]);

            return $posts;

        } catch (\Exception $e) {
            Log::error('Error retrieving all posts: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * 特定の番組に対する投稿を取得する
     *
     * @param string $stationId
     * @param string $programTitle
     * @param int $perPage
     * @return array
     */
    public function getPostsByProgram($stationId, $programTitle, $perPage = 10)
    {
        try {
            $program = RadioProgram::where('title', '=', $programTitle)->first();

            if (!$program) {
                Log::warning('Program not found for posts', [
                    'station_id' => $stationId,
                    'title' => $programTitle
                ]);
                return [
                    'posts' => collect(),
                    'program_id' => null
                ];
            }

            $posts = Post::select('posts.*', 'users.name')
                ->leftJoin('users', 'users.id', '=', 'posts.user_id')
                ->where('program_id', '=', $program->id)
                ->paginate($perPage);

            Log::info('Posts by program retrieved', [
                'program_id' => $program->id,
                'total' => $posts->total()
            ]);

            return [
                'posts' => $posts,
                'program_id' => $program->id
            ];

        } catch (\Exception $e) {
            Log::error('Error retrieving posts by program: ' . $e->getMessage(), [
                'station_id' => $stationId,
                'title' => $programTitle,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * 特定のユーザーの投稿を取得する
     *
     * @param int $userId
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPostsByUser($userId, $perPage = 10)
    {
        try {
            $posts = Post::select('posts.*', 'radio_programs.station_id')
                ->join('radio_programs', 'posts.program_id', '=', 'radio_programs.id')
                ->where('user_id', $userId)
                ->paginate($perPage);

            Log::info('Posts by user retrieved', [
                'user_id' => $userId,
                'total' => $posts->total()
            ]);

            return $posts;

        } catch (\Exception $e) {
            Log::error('Error retrieving posts by user: ' . $e->getMessage(), [
                'user_id' => $userId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * 投稿を作成する
     *
     * @param array $data
     * @param int $userId
     * @return Post
     */
    public function createPost(array $data, $userId)
    {
        try {
            $user = User::findOrFail($userId);
            $post = $user->posts()->create($data);

            Log::info('Post created', [
                'post_id' => $post->id,
                'user_id' => $userId,
                'program_id' => $data['program_id']
            ]);

            return $post;

        } catch (\Exception $e) {
            Log::error('Error creating post: ' . $e->getMessage(), [
                'user_id' => $userId,
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * 投稿を更新する
     *
     * @param int $postId
     * @param array $data
     * @return Post
     */
    public function updatePost($postId, array $data)
    {
        try {
            $post = Post::findOrFail($postId);
            $post->title = $data['title'];
            $post->body = $data['body'];
            $post->save();

            Log::info('Post updated', [
                'post_id' => $postId,
                'user_id' => $post->user_id
            ]);

            return $post;

        } catch (\Exception $e) {
            Log::error('Error updating post: ' . $e->getMessage(), [
                'post_id' => $postId,
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * 投稿を削除する
     *
     * @param int $postId
     * @return bool
     */
    public function deletePost($postId)
    {
        try {
            $post = Post::findOrFail($postId);
            $userId = $post->user_id;
            $post->delete();

            Log::info('Post deleted', [
                'post_id' => $postId,
                'user_id' => $userId
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Error deleting post: ' . $e->getMessage(), [
                'post_id' => $postId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * 投稿の詳細を取得する
     *
     * @param int $postId
     * @return Post
     */
    public function getPostById($postId)
    {
        try {
            $post = Post::findOrFail($postId);

            Log::info('Post detail retrieved', [
                'post_id' => $postId
            ]);

            return $post;

        } catch (\Exception $e) {
            Log::error('Error retrieving post detail: ' . $e->getMessage(), [
                'post_id' => $postId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
