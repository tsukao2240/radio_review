<?php

namespace App\Services;

use App\Post;
use App\PostTag;
use App\RadioProgram;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

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
            
            $tags = $data['tags'] ?? [];
            unset($data['tags']);
            
            $post = $user->posts()->create($data);
            
            // タグを添付
            if (!empty($tags)) {
                $post->tags()->attach($tags);
            }

            // レコメンデーションキャッシュをクリア
            Cache::forget("recommendations_user_{$userId}");

            Log::info('Post created', [
                'post_id' => $post->id,
                'user_id' => $userId,
                'program_id' => $data['program_id'],
                'rating' => $data['rating'] ?? null,
                'tags_count' => count($tags)
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

    /**
     * フィルタリング条件付きで投稿を取得
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPostsFiltered($filters, $perPage = 10)
    {
        try {
            $query = Post::query()
                ->select('posts.*', 'radio_programs.station_id', 'users.name')
                ->leftJoin('users', 'users.id', '=', 'posts.user_id')
                ->leftJoin('radio_programs', 'posts.program_id', '=', 'radio_programs.id');

            // 最小評価でフィルタリング
            if (isset($filters['min_rating']) && $filters['min_rating']) {
                $query->where('posts.rating', '>=', $filters['min_rating']);
            }

            // タグでフィルタリング
            if (isset($filters['tag_id']) && $filters['tag_id']) {
                $query->whereHas('tags', function ($q) use ($filters) {
                    $q->where('post_tags.id', $filters['tag_id']);
                });
            }

            // ソート順
            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortOrder = $filters['sort_order'] ?? 'desc';
            
            if ($sortBy === 'likes_count') {
                $query->orderBy('posts.likes_count', $sortOrder);
            } elseif ($sortBy === 'rating') {
                $query->orderBy('posts.rating', $sortOrder);
            } else {
                $query->orderBy('posts.created_at', $sortOrder);
            }

            $posts = $query->paginate($perPage);

            Log::info('Filtered posts retrieved', [
                'total' => $posts->total(),
                'filters' => $filters
            ]);

            return $posts;

        } catch (\Exception $e) {
            Log::error('Error retrieving filtered posts: ' . $e->getMessage(), [
                'filters' => $filters,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * 番組の平均評価を取得
     *
     * @param int $programId
     * @return array
     */
    public function getAverageRatingByProgram($programId)
    {
        try {
            $result = Post::where('program_id', $programId)
                ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as review_count')
                ->first();

            return [
                'average_rating' => $result->avg_rating ? round($result->avg_rating, 1) : null,
                'review_count' => $result->review_count
            ];

        } catch (\Exception $e) {
            Log::error('Error calculating average rating: ' . $e->getMessage(), [
                'program_id' => $programId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * 全タグを取得
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllTags()
    {
        try {
            return PostTag::ordered()->get();
        } catch (\Exception $e) {
            Log::error('Error retrieving tags: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
