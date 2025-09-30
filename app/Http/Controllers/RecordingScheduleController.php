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
            'scheduled_start_time' => 'required|date|after:now',
            'scheduled_end_time' => 'required|date|after:scheduled_start_time'
        ]);

        try {
            // 開始時刻が1週間以内かチェック
            $startTime = Carbon::parse($request->scheduled_start_time);
            if ($startTime->diffInDays(now()) > 7) {
                return response()->json([
                    'success' => false,
                    'message' => '録音予約は1週間先までです'
                ]);
            }

            // 重複チェック
            $existing = RecordingSchedule::where('user_id', Auth::id())
                ->where('station_id', $request->station_id)
                ->where('scheduled_start_time', $startTime)
                ->whereIn('status', ['pending', 'recording'])
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'すでに同じ番組が予約されています'
                ]);
            }

            RecordingSchedule::create([
                'user_id' => Auth::id(),
                'station_id' => $request->station_id,
                'program_title' => $request->program_title,
                'scheduled_start_time' => $request->scheduled_start_time,
                'scheduled_end_time' => $request->scheduled_end_time
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
            'id' => 'required|integer'
        ]);

        try {
            $schedule = RecordingSchedule::where('id', $request->id)
                ->where('user_id', Auth::id())
                ->whereIn('status', ['pending'])
                ->first();

            if (!$schedule) {
                return response()->json([
                    'success' => false,
                    'message' => '予約が見つかりません'
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
