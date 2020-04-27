<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Symfony\Component\Console\Input\Input;
use Illuminate\Support\Facades\DB;


class SearchController extends Controller
{
    //
    //番組を検索します
    public function index()
    {
        if (!empty(Input::get('title'))) {

            $keyword = Input::get('title');
            return DB::table('radio_programs')
                ->where('title', 'LIKE BINARY', '%' . $keyword . '%')->distinct()->select()->get();
        } else {
            //キーワードが入力されていないときはページ遷移しない
            return [];
        }
    }
}
