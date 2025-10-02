<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\RecordingSchedule;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RecordingScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // 予約一覧表示
    public function index()
    {
        $schedules = Auth::user()
            ->recordingSchedules()
            ->orderBy('scheduled_start_time', 'desc')
            ->get();

        return view('recording.schedules', compact('schedules'));
    }

    // 予約作成
    public function store(Request $request)
    {
        $request->validate([
            'station_id' => 'required|string',
            'program_title' => 'required|string',
            'scheduled_start_time' => 'required',
            'scheduled_end_time' => 'required'
        ]);

        try {
            // YmdHis形式の文字列をパース
            $startTime = Carbon::createFromFormat('YmdHis', $request->scheduled_start_time);
            $endTime = Carbon::createFromFormat('YmdHis', $request->scheduled_end_time);

            // 開始時刻が1週間以内かチェック
            if ($startTime->gt(Carbon::now()->addWeek())) {
                return response()->json([
                    'success' => false,
                    'message' => '録音予約は1週間先までです'
                ]);
            }

            RecordingSchedule::create([
                'user_id' => Auth::id(),
                'station_id' => $request->station_id,
                'program_title' => $request->program_title,
                'scheduled_start_time' => $startTime->format('Y-m-d H:i:s'),
                'scheduled_end_time' => $endTime->format('Y-m-d H:i:s')
            ]);

            return response()->json([
                'success' => true,
                'message' => '録音予約を登録しました'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '予約登録に失敗しました: ' . $e->getMessage()
            ]);
        }
    }

    // 予約キャンセル
    public function cancel(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|integer'
        ]);

        try {
            $schedule = RecordingSchedule::where('id', $request->schedule_id)
                ->where('user_id', Auth::id())
                ->first();

            if (!$schedule) {
                return response()->json([
                    'success' => false,
                    'message' => '予約が見つかりません'
                ]);
            }

            // pending状態の予約のみキャンセル可能
            if ($schedule->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => '予約済み以外の予約はキャンセルできません'
                ]);
            }

            $schedule->status = 'cancelled';
            $schedule->save();

            return response()->json([
                'success' => true,
                'message' => '予約をキャンセルしました'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'キャンセルに失敗しました: ' . $e->getMessage()
            ]);
        }
    }
}
