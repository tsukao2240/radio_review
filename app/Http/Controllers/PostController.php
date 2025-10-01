<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewCreateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Post;
use App\RadioProgram;
use App\User;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    //"レビューを投稿する"画面で初期表示ですべての番組を表示する
    public function index()
    {
        try {
            // LIKE句を使わずにNOT REGEXP一発で処理して効率化
            $excludePattern = '\uff08\u65b0\uff09|\uff3b\u65b0\uff3d|\u3010\u65b0\u3011|\u3010\u65b0\u756a\u7d44\u3011|\uff1c\u65b0\u756a\u7d44\uff1e|\uff08\u7d42\uff09|\uff3b\u7d42\uff3d|\u226a\u7d42\u226b|\u3010\u7d42\u3011|\u3010\u6700\u7d42\u56de\u3011|\uff1c\u6700\u7d42\u56de\uff1e|\uff08\u518d\uff09|\u3010\u518d\u3011|\u226a\u518d\u226b|\[\u518d\]|\uff08\u518d\u653e\u9001\uff09|\u518d\u653e\u9001';
            
            $results = RadioProgram::whereRaw('title NOT REGEXP ?', [$excludePattern])
                ->paginate(10);
        } catch (\Exception $e) {
            // データベース接続エラーの場合は空のペジネーションオブジェクトを返す
            $results = new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]),
                0,
                10,
                1,
                ['path' => request()->url(), 'pageName' => 'page']
            );
        }

        return view('post.index', compact('results'));
    }
    //レビュー入力画面
    public function review($program_id)
    {
        $user_id = Auth::id();
        $program = RadioProgram::findOrFail($program_id);
        $program_title = $program->title;
        return view('post.create', compact('program_id', 'user_id', 'program_title'));
    }
    //レビューを投稿する
    public function store(ReviewCreateRequest $request)
    {
        $input = $request->all();
        $user_id = $input['user_id'];
        $user = User::findOrFail($user_id);
        $user->posts()->create($input);
        return redirect()->back()->with('message', '投稿が完了しました');
    }
    //"レビューを見る"画面の処理
    public function view()
    {
        // N+1クエリを解消: Eloquentでwithを使用してリレーションをEager Load
        $posts = Post::with(['user', 'radioProgram'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('post.list_all', compact('posts'));
    }
    //番組詳細画面の"レビューを見る"ボタンの処理
    public function list($station_id, $program_title)
    {
        // N+1クエリを解消: Eloquentでwithを使用してリレーションをEager Load
        $program = RadioProgram::where('title', $program_title)->first();
        
        if (!$program) {
            abort(404, '番組が見つかりません');
        }
        
        $posts = Post::with('user')
            ->where('program_id', $program->id)
            ->paginate(10);
            
        return view('post.list_each', compact('posts', 'program_title', 'station_id'));
    }
}
