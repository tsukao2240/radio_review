<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\FavoriteProgram;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\DatabaseException;
use App\Http\Requests\FavoriteProgramRequest;
use App\Services\RadikoApiService;
use Carbon\Carbon;

class FavoriteProgramController extends Controller
{
    protected $radikoApiService;

    public function __construct(RadikoApiService $radikoApiService)
    {
        $this->middleware('auth');
        $this->radikoApiService = $radikoApiService;
    }

    // お気に入り一覧表示
    public function index()
    {
        $favorites = Auth::user()->favoritePrograms;

        // 各お気に入り番組の直近放送情報を取得
        $favoritesWithSchedule = $favorites->map(function($favorite) {
            try {
                // 週間番組表を取得
                $schedule = $this->radikoApiService->getWeeklySchedule($favorite->station_id);

                // 番組タイトルにマッチする直近の放送を検索
                $latestBroadcast = null;
                $now = Carbon::now();

                foreach ($schedule['entries'] as $entry) {
                    if ($entry['title'] === $favorite->program_title) {
                        $programEndTime = Carbon::createFromFormat('Ymd H:i', $entry['date'] . ' ' . $entry['end']);

                        // タイムフリー期間内（放送終了から7日以内）かつ、放送が終了済みの番組
                        if ($programEndTime->isPast() && $programEndTime->diffInDays($now) <= 7) {
                            $latestBroadcast = $entry;
                            break; // 最初に見つかった直近の放送を使用
                        }
                    }
                }

                $favorite->latest_broadcast = $latestBroadcast;
            } catch (\Exception $e) {
                \Log::error('お気に入り番組の放送情報取得エラー', [
                    'favorite_id' => $favorite->id,
                    'station_id' => $favorite->station_id,
                    'program_title' => $favorite->program_title,
                    'error' => $e->getMessage()
                ]);
                $favorite->latest_broadcast = null;
            }

            return $favorite;
        });

        return view('favorite.index', ['favorites' => $favoritesWithSchedule]);
    }

    // お気に入り登録
    public function store(FavoriteProgramRequest $request)
    {
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
            \Log::error('お気に入り登録エラー', ['error' => $e->getMessage(), 'user_id' => Auth::id()]);
            throw new DatabaseException('お気に入りの登録に失敗しました', 0, $e);
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
            \Log::error('お気に入り削除エラー', ['error' => $e->getMessage(), 'user_id' => Auth::id()]);
            throw new DatabaseException('お気に入りの削除に失敗しました', 0, $e);
        }
    }

    // お気に入り確認API
    public function check(FavoriteProgramRequest $request)
    {
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
