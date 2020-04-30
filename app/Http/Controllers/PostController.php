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
    //
    public function index()
    {
        $results = DB::table('radio_programs')->paginate(10);
        return view('post.index', compact('results'));
    }

    public function review($program_id)
    {
        $user_id = Auth::id();
        $program = RadioProgram::findOrFail($program_id);
        $program_title = $program->title;
        return view('post.create', compact('program_id','user_id','program_title'));
    }

    public function store(ReviewCreateRequest $request)
    {
        $input = $request->all();
        $user_id = $input['user_id'];
        $user = User::findOrFail($user_id);
        $user->posts()->create($input);
        return redirect()->back()->with('message','投稿が完了しました');
    }

    public function view(){

        $posts = Post::all();
        return view('post.list',compact('posts'));

    }

}
