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
        // 一時的にデータベースアクセスを無効化
        // データベース問題解決後に元のコードに戻す予定
        try {
            $results = DB::table('radio_programs')
                ->Where('title', 'not like', '%（新）%')
                ->Where('title', 'not like', '%［新］%')
                ->Where('title', 'not like', '%【新】%')
                ->Where('title', 'not like', '%【新番組】%')
                ->Where('title', 'not like', '%＜新番組＞%')
                ->Where('title', 'not like', '%（終）%')
                ->Where('title', 'not like', '%［終］%')
                ->Where('title', 'not like', '%≪終≫%')
                ->Where('title', 'not like', '%【終】%')
                ->where('title', 'not like', '%【最終回】%')
                ->where('title', 'not like', '%＜最終回＞%')
                ->where('title', 'not like', '%(再)%')
                ->where('title', 'not like', '%【再】%')
                ->where('title', 'not like', '%≪再≫%')
                ->where('title', 'not like', '%[再]%')
                ->where('title', 'not like', '%（再放送）%')
                ->where('title', 'not like', '%再放送%')
                ->paginate(10);
        } catch (\Exception $e) {
            // データベース接続エラーの場合は空のペジネーションオブジェクトを返す
            $results = new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]), // 空のコレクション
                0, // 全体のアイテム数
                10, // 1ページあたりのアイテム数
                1, // 現在のページ
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
        $posts = DB::table('posts')->select('posts.*', 'radio_programs.station_id', 'users.name')
        ->leftJoin('users', 'users.id', '=', 'posts.user_id')
        ->leftJoin('radio_programs', 'posts.program_id', '=', 'radio_programs.id')
        ->paginate(10);
        return view('post.list_all', compact('posts'));
    }
    //番組詳細画面の"レビューを見る"ボタンの処理
    public function list($station_id, $program_title)
    {
        $id = RadioProgram::select('*')->where('title', '=', $program_title)->pluck('id');
        $posts = Post::select('posts.*','users.name')->leftjoin('users','users.id','=','posts.user_id')->where('program_id', '=', $id[0])->paginate(10);
        return view('post.list_each', compact('posts', 'program_title', 'station_id'));
    }
}
