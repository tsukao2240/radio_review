<?php

namespace App\Services;

use App\RadioProgram;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 番組情報に関するビジネスロジックを担当するサービスクラス
 */
class ProgramService
{
    /**
     * @var RadikoApiService
     */
    protected $radikoService;

    /**
     * コンストラクタ
     *
     * @param RadikoApiService $radikoService
     */
    public function __construct(RadikoApiService $radikoService)
    {
        $this->radikoService = $radikoService;
    }

    /**
     * 番組の詳細情報を取得する（APIから取得できない場合はDBから取得）
     *
     * @param string $stationId
     * @param string $title
     * @return array
     */
    public function getProgramDetails($stationId, $title)
    {
        try {
            // まずAPIから取得を試みる
            $entries = $this->radikoService->getProgramDetails($stationId, $title);

            if (!empty($entries)) {
                // APIから取得できた場合、program_idをDBから取得
                $program = DB::table('radio_programs')
                    ->where('title', $title)
                    ->select('id')
                    ->first();

                $programId = $program ? $program->id : null;

                Log::info('Program details retrieved from API', [
                    'station_id' => $stationId,
                    'title' => $title,
                    'program_id' => $programId
                ]);

                return [
                    'entries' => $entries,
                    'program_id' => $programId,
                    'source' => 'api'
                ];
            }

            // APIから取得できない場合はDBから取得
            $results = DB::table('radio_programs')
                ->where('title', $title)
                ->select('title', 'cast', 'info', 'image', 'id', 'station_id')
                ->get();

            Log::info('Program details retrieved from DB', [
                'station_id' => $stationId,
                'title' => $title,
                'found' => $results->isNotEmpty()
            ]);

            return [
                'results' => $results,
                'source' => 'db'
            ];

        } catch (\Exception $e) {
            Log::error('Error getting program details: ' . $e->getMessage(), [
                'station_id' => $stationId,
                'title' => $title,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * 番組IDから番組情報を取得する
     *
     * @param int $programId
     * @return RadioProgram
     */
    public function getProgramById($programId)
    {
        try {
            $program = RadioProgram::findOrFail($programId);

            Log::info('Program retrieved by ID', [
                'program_id' => $programId
            ]);

            return $program;

        } catch (\Exception $e) {
            Log::error('Error getting program by ID: ' . $e->getMessage(), [
                'program_id' => $programId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * 番組タイトルから番組情報を取得する
     *
     * @param string $title
     * @return RadioProgram|null
     */
    public function getProgramByTitle($title)
    {
        try {
            $program = RadioProgram::where('title', $title)->first();

            Log::info('Program retrieved by title', [
                'title' => $title,
                'found' => $program !== null
            ]);

            return $program;

        } catch (\Exception $e) {
            Log::error('Error getting program by title: ' . $e->getMessage(), [
                'title' => $title,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
