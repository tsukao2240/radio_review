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
        $posts = Post::select('posts.*','radio_programs.station_id')->join('radio_programs','posts.program_id','=','radio_programs.id')->where('user_id', $user_id)->paginate(10);
        return view('mypage.index', compact('posts'));
    }
    //自分が投稿したレビューの編集画面に遷移する
    public function edit($program_id)
    {
        $program = RadioProgram::findOrFail($program_id);
        $post = Post::where('program_id', $program_id)->where('user_id', Auth::id())->firstOrFail();
        return view('mypage.edit', compact('post', 'program'));
    }
    //自分が投稿したレビューの編集画面に編集する
    public function update(Request $request)
    {
        $post = Post::findOrFail($request->id);
        $post->title = $request->title;
        $post->body = $request->body;
        $post->save();
        return redirect('/my')->with('message', '編集が完了しました');
    }
    //自分が投稿したレビューを削除する
    public function destroy(Request $request)
    {
        $post = Post::findOrFail($request->id);
        $post->delete();
        return redirect()->back()->with('message','削除しました');
    }
}
