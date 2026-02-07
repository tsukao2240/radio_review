<?php

namespace App\Services;

use App\User;
use App\Post;
use App\RadioProgram;
use App\FavoriteProgram;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * 番組レコメンデーションを管理するサービスクラス
 */
class RecommendationService
{
    /**
     * ユーザーへのパーソナライズされたレコメンデーション
     *
     * @param User $user
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecommendations(User $user, $limit = 10)
    {
        $cacheKey = "recommendations_user_{$user->id}";

        return Cache::remember($cacheKey, 3600, function () use ($user, $limit) {
            try {
                // ユーザーのお気に入りと高評価レビューからキーワードを抽出
                $keywords = $this->extractKeywords($user);

                if (empty($keywords)) {
                    // 新規ユーザーの場合は人気番組を返す
                    Log::info('No user history, returning popular programs', [
                        'user_id' => $user->id,
                    ]);
                    return $this->getPopularPrograms($limit);
                }

                // 類似番組を検索
                $recommendations = $this->findSimilarPrograms($keywords, $user, $limit);

                Log::info('Recommendations generated', [
                    'user_id' => $user->id,
                    'keywords_count' => count($keywords),
                    'results_count' => $recommendations->count(),
                ]);

                return $recommendations;

            } catch (\Exception $e) {
                Log::error('Error generating recommendations: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'trace' => $e->getTraceAsString(),
                ]);
                // エラー時は人気番組を返す
                return $this->getPopularPrograms($limit);
            }
        });
    }

    /**
     * トレンド番組を取得（最近高評価が多い番組）
     *
     * @param int $days
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTrendingPrograms($days = 7, $limit = 10)
    {
        try {
            $programs = RadioProgram::select('radio_programs.*')
                ->join('posts', 'radio_programs.id', '=', 'posts.program_id')
                ->where('posts.created_at', '>=', now()->subDays($days))
                ->where('posts.rating', '>=', 4.0)
                ->groupBy('radio_programs.id')
                ->selectRaw('COUNT(posts.id) as recent_reviews_count')
                ->selectRaw('AVG(posts.rating) as avg_rating')
                ->orderByDesc('recent_reviews_count')
                ->orderByDesc('avg_rating')
                ->limit($limit)
                ->get();

            Log::info('Trending programs retrieved', [
                'days' => $days,
                'count' => $programs->count(),
            ]);

            return $programs;

        } catch (\Exception $e) {
            Log::error('Error retrieving trending programs: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return collect();
        }
    }

    /**
     * 人気番組を取得（全期間で評価が高い番組）
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPopularPrograms($limit = 10)
    {
        try {
            $programs = RadioProgram::select('radio_programs.*')
                ->join('posts', 'radio_programs.id', '=', 'posts.program_id')
                ->groupBy('radio_programs.id')
                ->selectRaw('COUNT(posts.id) as reviews_count')
                ->selectRaw('AVG(posts.rating) as avg_rating')
                ->having('reviews_count', '>=', 3) // 最低3件のレビューが必要
                ->orderByDesc('avg_rating')
                ->orderByDesc('reviews_count')
                ->limit($limit)
                ->get();

            Log::info('Popular programs retrieved', [
                'count' => $programs->count(),
            ]);

            return $programs;

        } catch (\Exception $e) {
            Log::error('Error retrieving popular programs: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return collect();
        }
    }

    /**
     * ユーザーのレコメンデーションキャッシュをクリア
     *
     * @param int $userId
     * @return void
     */
    public function clearUserCache($userId)
    {
        Cache::forget("recommendations_user_{$userId}");
        Log::info('Recommendation cache cleared', ['user_id' => $userId]);
    }

    /**
     * ユーザーの興味キーワードを抽出
     *
     * @param User $user
     * @return array
     */
    protected function extractKeywords(User $user)
    {
        $keywords = [];

        // お気に入り番組のタイトルからキーワードを抽出
        $favorites = $user->favoritePrograms()->limit(10)->get();
        foreach ($favorites as $favorite) {
            $keywords = array_merge($keywords, $this->cleanTitle($favorite->program_title));
        }

        // 高評価（4-5星）レビューの番組タイトルからキーワードを抽出
        $highRatedPosts = $user->posts()
            ->where('rating', '>=', 4.0)
            ->limit(10)
            ->get();
        
        foreach ($highRatedPosts as $post) {
            $keywords = array_merge($keywords, $this->cleanTitle($post->program_title));
        }

        // 重複を削除し、頻度でソート
        $keywords = array_count_values($keywords);
        arsort($keywords);

        return array_keys(array_slice($keywords, 0, 10)); // 上位10キーワード
    }

    /**
     * タイトルをクリーンアップしてキーワードに分解
     *
     * @param string $title
     * @return array
     */
    protected function cleanTitle($title)
    {
        // 記号を削除
        $title = preg_replace('/[「」『』【】（）()〜～・、。！？\s]+/', ' ', $title);
        
        // 単語に分解（日本語の場合は2文字以上の連続した文字列）
        preg_match_all('/[ぁ-んァ-ヶー一-龠々]{2,}/u', $title, $matches);
        
        return $matches[0] ?? [];
    }

    /**
     * キーワードに類似する番組を検索
     *
     * @param array $keywords
     * @param User $user
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function findSimilarPrograms(array $keywords, User $user, $limit)
    {
        // すでにお気に入りの番組IDを取得
        $favoriteProgramIds = $user->favoritePrograms()->pluck('program_id')->toArray();

        $query = RadioProgram::query();

        // キーワードマッチング
        foreach ($keywords as $index => $keyword) {
            if ($index === 0) {
                $query->where('title', 'LIKE', "%{$keyword}%");
            } else {
                $query->orWhere('title', 'LIKE', "%{$keyword}%");
            }
        }

        // すでにお気に入りの番組を除外
        if (!empty($favoriteProgramIds)) {
            $query->whereNotIn('id', $favoriteProgramIds);
        }

        // 評価の高い順でソート
        $programs = $query
            ->leftJoin('posts', 'radio_programs.id', '=', 'posts.program_id')
            ->select('radio_programs.*')
            ->groupBy('radio_programs.id')
            ->selectRaw('AVG(posts.rating) as avg_rating')
            ->selectRaw('COUNT(posts.id) as reviews_count')
            ->orderByDesc('avg_rating')
            ->orderByDesc('reviews_count')
            ->limit($limit)
            ->get();

        return $programs;
    }
}
