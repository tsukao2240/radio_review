<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Post;
use App\RadioProgram;
use Illuminate\Support\Facades\Auth;

class MypageController extends Controller
{
    //自分が投稿したレビューを表示する
    public function index()
    {
        $user_id = Auth::id();
        // JOINを使用してstation_idを取得（N+1問題を回避）
        $posts = Post::select('posts.*', 'radio_programs.station_id')
            ->join('radio_programs', 'posts.program_id', '=', 'radio_programs.id')
            ->where('posts.user_id', $user_id)
            ->orderBy('posts.created_at', 'desc')
            ->paginate(10);
        return view('mypage.index', compact('posts'));
    }
    //自分が投稿したレビューの編集画面に遷移する
    public function edit($program_id)
    {
        // RadioProgramとPostを一度のクエリで取得
        $post = Post::with('radioProgram')
            ->where('program_id', $program_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        $program = $post->radioProgram;
        return view('mypage.edit', compact('post', 'program'));
    }
    //自分が投稿したレビューの編集画面に編集する
    public function update(Request $request, $program_id)
    {
        $post = Post::where('program_id', $program_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        $post->title = $request->title;
        $post->body = $request->body;
        $post->save();
        return redirect('/my')->with('message', '編集が完了しました');
    }
    //自分が投稿したレビューを削除する
    public function destroy(Request $request)
    {
        $post = Post::where('program_id', $request->program_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        $post->delete();
        return redirect()->back()->with('message','削除しました');
    }
}
