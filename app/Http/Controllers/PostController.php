<?php

namespace App\Http\Controllers;

use App\Exceptions\DatabaseException;
use App\Http\Requests\ReviewCreateRequest;
use App\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Post;
use App\PostTag;
use App\RadioProgram;
use App\User;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    protected $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }
    //"レビューを投稿する"画面で初期表示ですべての番組を表示する
    public function index(Request $request)
    {
        try {
            // LIKE句を使わずにNOT REGEXP一発で処理して効率化
            $excludePattern = '\uff08\u65b0\uff09|\uff3b\u65b0\uff3d|\u3010\u65b0\u3011|\u3010\u65b0\u756a\u7d44\u3011|\uff1c\u65b0\u756a\u7d44\uff1e|\uff08\u7d42\uff09|\uff3b\u7d42\uff3d|\u226a\u7d42\u226b|\u3010\u7d42\u3011|\u3010\u6700\u7d42\u56de\u3011|\uff1c\u6700\u7d42\u56de\uff1e|\uff08\u518d\uff09|\u3010\u518d\u3011|\u226a\u518d\u226b|\[\u518d\]|\uff08\u518d\u653e\u9001\uff09|\u518d\u653e\u9001';

            // ページネーション件数（デフォルト50件、選択可能：10, 25, 50, 100件）
            $perPage = $request->input('per_page', 50);
            if (!in_array($perPage, [10, 25, 50, 100])) {
                $perPage = 50;
            }

            $query = RadioProgram::whereRaw('title NOT REGEXP ?', [$excludePattern]);

            // 検索機能
            if ($request->filled('search')) {
                $searchTerm = $request->input('search');
                $query->where(function($q) use ($searchTerm) {
                    $q->where('title', 'LIKE', '%' . $searchTerm . '%')
                      ->orWhere('cast', 'LIKE', '%' . $searchTerm . '%');
                });
            }

            $results = $query->paginate($perPage)->appends($request->except('page'));
        } catch (\Exception $e) {
            \Log::error('番組一覧取得エラー', ['error' => $e->getMessage()]);
            throw new DatabaseException('番組一覧の取得に失敗しました', 0, $e);
        }

        return view('post.index', compact('results'));
    }
    //レビュー入力画面
    public function review($program_id)
    {
        try {
            $user_id = Auth::id();
            $program = RadioProgram::findOrFail($program_id);
            $program_title = $program->title;
            $tags = $this->postService->getAllTags();
            return view('post.create', compact('program_id', 'user_id', 'program_title', 'tags'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw $e; // Handler.phpで処理
        } catch (\Exception $e) {
            \Log::error('レビュー画面表示エラー', ['error' => $e->getMessage()]);
            throw new DatabaseException('番組情報の取得に失敗しました', 0, $e);
        }
    }
    //レビューを投稿する
    public function store(ReviewCreateRequest $request)
    {
        try {
            $input = $request->all();
            $this->postService->createPost($input, Auth::id());
            return redirect()->back()->with('message', '投稿が完了しました');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw $e; // Handler.phpで処理
        } catch (\Exception $e) {
            \Log::error('レビュー投稿エラー', ['error' => $e->getMessage(), 'user_id' => Auth::id()]);
            throw new DatabaseException('投稿に失敗しました。もう一度お試しください', 0, $e);
        }
    }
    //"レビューを見る"画面の処理
    public function view(Request $request)
    {
        try {
            $filters = [
                'min_rating' => $request->input('min_rating'),
                'tag_id' => $request->input('tag_id'),
                'sort_by' => $request->input('sort_by', 'created_at'),
                'sort_order' => $request->input('sort_order', 'desc'),
            ];

            $posts = $this->postService->getPostsFiltered($filters, 10);
            $tags = $this->postService->getAllTags();

            return view('post.list_all', compact('posts', 'tags', 'filters'));
        } catch (\Exception $e) {
            \Log::error('レビュー一覧取得エラー', ['error' => $e->getMessage()]);
            throw new DatabaseException('レビューの取得に失敗しました', 0, $e);
        }
    }
    //番組詳細画面の"レビューを見る"ボタンの処理
    public function list($station_id, $program_title)
    {
        try {
            // N+1クエリを解消: Eloquentでwithを使用してリレーションをEager Load
            $program = RadioProgram::where('title', $program_title)->first();
            
            if (!$program) {
                abort(404, '番組が見つかりません');
            }
            
            $posts = Post::with(['user', 'tags'])
                ->where('program_id', $program->id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            
            // 番組の平均評価を取得
            $programRating = $this->postService->getAverageRatingByProgram($program->id);
                
            return view('post.list_each', compact('posts', 'program_title', 'station_id', 'programRating'));
        } catch (\Exception $e) {
            \Log::error('番組別レビュー取得エラー', [
                'error' => $e->getMessage(),
                'program_title' => $program_title,
                'station_id' => $station_id
            ]);
            throw new DatabaseException('レビューの取得に失敗しました', 0, $e);
        }
    }

    /**
     * 番組の平均評価を取得（API）
     */
    public function getProgramRating($programId)
    {
        try {
            $rating = $this->postService->getAverageRatingByProgram($programId);
            return response()->json([
                'success' => true,
                'data' => $rating,
            ]);
        } catch (\Exception $e) {
            \Log::error('番組評価取得エラー', [
                'error' => $e->getMessage(),
                'program_id' => $programId,
            ]);
            return response()->json([
                'success' => false,
                'message' => '評価の取得に失敗しました',
            ], 500);
        }
    }
}
