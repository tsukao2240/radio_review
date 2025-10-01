<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\FavoriteProgram;
use Illuminate\Support\Facades\Auth;

class FavoriteProgramController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // お気に入り一覧表示
    public function index()
    {
        $favorites = Auth::user()->favoritePrograms()->orderBy('created_at', 'desc')->get();
        return view('favorite.index', compact('favorites'));
    }

    // お気に入り登録
    public function store(Request $request)
    {
        $request->validate([
            'station_id' => 'required|string',
            'program_title' => 'required|string'
        ]);

        try {
            // exists()を使って重複チェックを最適化
            $exists = FavoriteProgram::where('user_id', Auth::id())
                ->where('station_id', $request->station_id)
                ->where('program_title', $request->program_title)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'すでにお気に入りに登録されています'
                ]);
            }

            // お気に入り登録
            FavoriteProgram::create([
                'user_id' => Auth::id(),
                'station_id' => $request->station_id,
                'program_title' => $request->program_title
            ]);

            return response()->json([
                'success' => true,
                'message' => 'お気に入りに登録しました'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '登録に失敗しました'
            ], 500);
        }
    }

    // お気に入り削除
    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|integer'
        ]);

        try {
            // delete()を直接使って効率化（戻り値で削除件数を取得）
            $deleted = FavoriteProgram::where('id', $request->id)
                ->where('user_id', Auth::id())
                ->delete();

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'お気に入りが見つかりません'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'お気に入りを削除しました'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '削除に失敗しました'
            ], 500);
        }
    }

    // お気に入り確認API
    public function check(Request $request)
    {
        $request->validate([
            'station_id' => 'required|string',
            'program_title' => 'required|string'
        ]);

        // exists()を使ってメモリ効率化
        $isFavorite = FavoriteProgram::where('user_id', Auth::id())
            ->where('station_id', $request->station_id)
            ->where('program_title', $request->program_title)
            ->exists();

        return response()->json([
            'success' => true,
            'is_favorite' => $isFavorite
        ]);
    }
}
