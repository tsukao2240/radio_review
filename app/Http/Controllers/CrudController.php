<?php

namespace App\Http\Controllers;

use App\RadioProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CrudController extends Controller
{
    public function index(Request $request){

        $keyword = $request->input('title');

        if(!empty($keyword)){

            $keyword = DB::table('radio_programs')
                ->where('title','LIKE BINARY','%' . $keyword . '%')->distinct()->select('title')->simplePaginate(10);

        }else{
            //キーワードが入力されていないときはページ遷移しない
            return redirect('/');

        }
        //検索結果があるかどうかでViewで表示する内容を変更するために使用している
        $existResult = $keyword->items();

        return view('index',compact('keyword','existResult'));

    }
}
