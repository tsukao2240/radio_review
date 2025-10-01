<?php

namespace App\Http\Controllers;

use App\RadioProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class CrudController extends Controller
{
    //番組検索処理
    public function index(Request $request)
    {
        $keyword = $request->input('title');

        if (!empty($keyword)) {
            // キャッシュキーを生成（検索キーワードごとに30分間キャッシュ）
            $cacheKey = 'search_programs_' . md5($keyword);
            
            $programs = Cache::remember($cacheKey, 1800, function () use ($keyword) {
                // REGEXP一発で処理して効率化
                $excludePattern = '\（新\）|\［新\］|\【新\】|\【新番組\】|\＜新番組\�|\（終\）|\［終\］|\≪終≫|\【終\】|\【最終回\】|\＜最終回\＞|\（再\）|\【再\】|\≪再≫|\[再\]|\（再放送\）|再放送';
                
                return RadioProgram::where('title', 'LIKE', '%' . $keyword . '%')
                    ->whereRaw('title NOT REGEXP ?', [$excludePattern])
                    ->distinct()
                    ->paginate(10);
            });
        } else {
            //キーワードが入力されていないときはページ遷移しない
            return back();
        }

        return view('post.index', compact('programs'));
    }
}
