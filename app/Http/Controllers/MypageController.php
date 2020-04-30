<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Post;
use Illuminate\Support\Facades\Auth;

class MypageController extends Controller
{
    //
    public function index()
    {
        $user_id = Auth::id();
        $posts = Post::where('user_id',$user_id)->paginate(10);

        return view('mypage.index',compact('posts'));
    }
}
