<?php

namespace App\Http\Controllers;

use App\RadioProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class CrudController extends Controller
{
    //番組を検索します
    public function index(Request $request)
    {
        $keyword = $request->input('title');

        if (!empty($keyword)) {

            $programs = DB::table('radio_programs')
                ->where('title', 'LIKE', '%' . $keyword . '%')
                ->Where('title','not like','%（新）%')
                ->Where('title','not like','%［新］%')
                ->Where('title','not like','%【新】%')
                ->Where('title','not like','%【新番組】%')
                ->Where('title','not like','%＜新番組＞%')
                ->Where('title','not like','%（終）%')
                ->Where('title','not like','%［終］%')
                ->Where('title','not like','%≪終≫%')
                ->Where('title','not like','%【終】%')
                ->where('title','not like','%【最終回】%')
                ->where('title','not like','%＜最終回＞%')
                ->where('title','not like','%(再)%')
                ->where('title','not like','%【再】%')
                ->where('title','not like','%≪再≫%')
                ->where('title','not like','%[再]%')
                ->where('title','not like','%（再放送）%')
                ->where('title','not like','%再放送%')
                ->distinct()->select()->Paginate(10);

        } else {
            //キーワードが入力されていないときはページ遷移しない
            return back();
        }

        return view('post.index', compact('programs'));
    }
}
