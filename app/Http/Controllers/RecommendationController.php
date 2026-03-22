<?php

namespace App\Http\Controllers;

use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecommendationController extends Controller
{
    protected $recommendationService;

    public function __construct(RecommendationService $recommendationService)
    {
        $this->middleware('auth');
        $this->recommendationService = $recommendationService;
    }

    /**
     * レコメンデーションページを表示
     */
    public function index()
    {
        $user = Auth::user();
        
        $recommendations = $this->recommendationService->getRecommendations($user, 10);
        $trending = $this->recommendationService->getTrendingPrograms(7, 10);

        return view('recommendations.index', compact('recommendations', 'trending'));
    }

    /**
     * レコメンデーションを取得（JSON API）
     */
    public function getRecommendations(Request $request)
    {
        $user = Auth::user();
        $limit = $request->input('limit', 10);

        $recommendations = $this->recommendationService->getRecommendations($user, $limit);

        return response()->json([
            'success' => true,
            'data' => $recommendations,
        ]);
    }

    /**
     * レコメンデーションキャッシュをリフレッシュ
     */
    public function refresh()
    {
        $user = Auth::user();
        
        $this->recommendationService->clearUserCache($user->id);
        $recommendations = $this->recommendationService->getRecommendations($user, 10);

        return response()->json([
            'success' => true,
            'message' => 'レコメンデーションを更新しました',
            'data' => $recommendations,
        ]);
    }
}
