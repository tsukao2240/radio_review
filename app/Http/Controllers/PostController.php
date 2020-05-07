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

        return view('post.index', compact('results'));
    }

    public function review($program_id)
    {
        $user_id = Auth::id();
        $program = RadioProgram::findOrFail($program_id);
        $program_title = $program->title;
        return view('post.create', compact('program_id', 'user_id', 'program_title'));
    }

    public function store(ReviewCreateRequest $request)
    {
        $input = $request->all();
        $user_id = $input['user_id'];
        $user = User::findOrFail($user_id);
        $user->posts()->create($input);
        return redirect()->back()->with('message', '投稿が完了しました');
    }

    public function view()
    {
        $posts = DB::table('posts')->select('posts.*','radio_programs.station_id')->leftJoin('radio_programs','posts.program_id','=','radio_programs.id')->get();
        return view('post.list', compact('posts'));
    }
}
