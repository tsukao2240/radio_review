<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostCommentsController extends Controller
{
    //
    public function index()
    {

        $results = DB::table('radio_programs')->paginate(20);
        return view('post.index', compact('results'));

    }

    public function post()
    {

    }
}
