<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\RecordingSchedule;
use App\Http\Controllers\RadioRecordingController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessRecordingSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recording:process-schedules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '録音予約を処理し、開始時刻になった予約の録音を開始する';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('録音予約処理を開始します...');

        // 開始時刻になった予約を取得（現在時刻から1分以内に開始予定のもの）
        $schedules = RecordingSchedule::where('status', 'pending')
            ->where('scheduled_start_time', '<=', Carbon::now()->addMinute())
            ->where('scheduled_start_time', '>=', Carbon::now()->subMinute())
            ->get();

        if ($schedules->isEmpty()) {
            $this->info('処理すべき録音予約はありません');
            return 0;
        }

        $this->info(sprintf('%d件の録音予約を処理します', $schedules->count()));

        foreach ($schedules as $schedule) {
            try {
                $this->info(sprintf('録音開始: %s - %s', $schedule->station_id, $schedule->program_title));

                // 録音を開始
                $this->startRecording($schedule);

                $this->info(sprintf('録音開始成功: %s', $schedule->program_title));
            } catch (\Exception $e) {
                $this->error(sprintf('録音開始失敗: %s - %s', $schedule->program_title, $e->getMessage()));
                Log::error('録音予約処理エラー', [
                    'schedule_id' => $schedule->id,
                    'error' => $e->getMessage()
                ]);

                // ステータスを失敗に更新
                $schedule->status = 'failed';
                $schedule->error_message = $e->getMessage();
                $schedule->save();
            }
        }

        $this->info('録音予約処理を完了しました');
        return 0;
    }

    /**
     * 録音を開始する
     */
    private function startRecording(RecordingSchedule $schedule)
    {
        // ステータスを録音中に更新
        $schedule->status = 'recording';
        $schedule->save();

        // RadioRecordingControllerのstartTimefreeRecordingメソッドを使用
        $controller = new RadioRecordingController();

        // Requestオブジェクトを作成
        $request = new \Illuminate\Http\Request();
        $request->merge([
            'station_id' => $schedule->station_id,
            'title' => $schedule->program_title,
            'start_time' => Carbon::parse($schedule->scheduled_start_time)->format('YmdHi'),
            'end_time' => Carbon::parse($schedule->scheduled_end_time)->format('YmdHi'),
        ]);

        // 録音開始
        $response = $controller->startTimefreeRecording($request);
        $data = $response->getData(true);

        if (!$data['success']) {
            throw new \Exception($data['message']);
        }

        // 録音IDを保存
        $schedule->recording_id = $data['recording_id'];
        $schedule->save();

        Log::info('録音予約から録音開始', [
            'schedule_id' => $schedule->id,
            'recording_id' => $data['recording_id'],
            'program_title' => $schedule->program_title
        ]);
    }
}