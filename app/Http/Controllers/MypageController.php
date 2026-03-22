<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Post;
use App\PostTag;
use App\RadioProgram;
use App\Services\PostService;
use Illuminate\Support\Facades\Auth;

class MypageController extends Controller
{
    protected $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }
    //自分が投稿したレビューを表示する
    public function index()
    {
        $user_id = Auth::id();
        // JOINを使用してstation_idとprogram_titleを取得（N+1問題を回避）
        $posts = Post::with('tags')
            ->select('posts.*', 'radio_programs.station_id', 'radio_programs.title as program_title')
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
        $post = Post::with(['radioProgram', 'tags'])
            ->where('program_id', $program_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        $program = $post->radioProgram;
        $tags = $this->postService->getAllTags();
        return view('mypage.edit', compact('post', 'program', 'tags'));
    }
    //自分が投稿したレビューの編集画面に編集する
    public function update(Request $request, $program_id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'rating' => 'required|numeric|min:1|max:5',
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:post_tags,id',
        ]);

        $post = Post::where('program_id', $program_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        $post->title = $request->title;
        $post->body = $request->body;
        $post->rating = $request->rating;
        $post->save();
        
        // タグを同期
        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        } else {
            $post->tags()->sync([]);
        }
        
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
