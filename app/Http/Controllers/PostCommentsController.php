<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Post;
use App\User;
use Illuminate\Support\Facades\Auth;

class PostCommentsController extends Controller
{
    //
    public function index()
    {
        $results = DB::table('radio_programs')->paginate(10);
        return view('post.index', compact('results'));
    }

    public function review($id)
    {
        return view('post.create', compact('id'));
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $user_id = $input['user_id'];
        $user = User::findOrFail($user_id);
        $user->posts()->create($input);
        return redirect()->back();
    }
}
