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
        $programs = [];
        $replace = [

            "　" => ' ',
            "\n" => '',
            '(新)' => '',
            '(終)' => ''

        ];
        if (!empty($keyword)) {

            $keyword = DB::table('radio_programs')
                ->where('title', 'LIKE BINARY', '%' . $keyword . '%')->distinct()->select()->Paginate(10);
            foreach ($keyword as $item) {

                // $zenToHan = mb_convert_kana($item->title, 'a');
                // $zenToHan = strtr($zenToHan,$replace);
                // $programs[] = $zenToHan;

            }
            $programs = array_unique($programs, SORT_REGULAR);
        } else {
            //キーワードが入力されていないときはページ遷移しない
            return back();
        }
        //検索結果があるかどうかでViewで表示する内容を変更するために使用している
        //$existResult = $keyword->items();
        return view('post.index', compact('keyword', 'programs'));
    }
}
