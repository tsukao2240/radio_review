<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\RadioProgram;
use App\Services\RadioProgramSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SearchController extends Controller
{
    /**
     * @var RadioProgramSearchService
     */
    protected $searchService;

    /**
     * コンストラクタ
     *
     * @param RadioProgramSearchService $searchService
     */
    public function __construct(RadioProgramSearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * 番組タイトルまたは出演者で検索する（API用）
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // バリデーション
            $validator = Validator::make($request->all(), [
                'title' => 'nullable|string|max:255',
                'cast' => 'nullable|string|max:255',
                'station_id' => 'nullable|string|max:50',
                'limit' => 'nullable|integer|min:1|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'バリデーションエラーが発生しました',
                    'errors' => $validator->errors()
                ], 422);
            }

            $keyword = $request->input('title');
            $cast = $request->input('cast');
            $stationId = $request->input('station_id');
            $limit = $request->input('limit', 20);

            // キーワードが入力されていない場合
            if (empty($keyword) && empty($cast) && empty($stationId)) {
                return response()->json([
                    'status' => 'error',
                    'message' => '検索キーワードを入力してください',
                    'data' => []
                ], 400);
            }

            // サービスクラスを使用して検索
            $programs = $this->searchService->searchProgramsForApi($keyword, $cast, $stationId, $limit);

            return response()->json([
                'status' => 'success',
                'message' => '検索が完了しました',
                'data' => $programs,
                'count' => $programs->count()
            ], 200);

        } catch (\Exception $e) {
            Log::error('API Search error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => '検索中にエラーが発生しました',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }

    /**
     * 番組IDで詳細を取得する
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $program = RadioProgram::find($id);

            if (!$program) {
                return response()->json([
                    'status' => 'error',
                    'message' => '番組が見つかりませんでした',
                ], 404);
            }

            Log::info('API Program detail retrieved', ['program_id' => $id]);

            return response()->json([
                'status' => 'success',
                'message' => '番組情報を取得しました',
                'data' => $program
            ], 200);

        } catch (\Exception $e) {
            Log::error('API Program detail error: ' . $e->getMessage(), [
                'program_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => '番組情報の取得中にエラーが発生しました',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }
}
