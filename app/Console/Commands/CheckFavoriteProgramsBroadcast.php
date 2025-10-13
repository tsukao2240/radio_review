<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\FavoriteProgram;
use App\Services\NotificationService;
use App\RadioProgram;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckFavoriteProgramsBroadcast extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'favorites:check-broadcast';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'お気に入り番組の放送開始をチェックして通知する';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $notificationService = app(\App\Services\NotificationService::class);

        $this->info('お気に入り番組の放送チェックを開始します...');

        // 全ユーザーのお気に入り番組を取得
        $favorites = FavoriteProgram::with('user')->get();

        if ($favorites->isEmpty()) {
            $this->info('お気に入り番組が登録されていません');
            return 0;
        }

        $now = Carbon::now()->format('Hi'); // 現在時刻（HHmm形式）
        $notificationCount = 0;

        foreach ($favorites as $favorite) {
            try {
                // 番組情報を取得
                $program = RadioProgram::where('station_id', $favorite->station_id)
                    ->where('title', $favorite->program_title)
                    ->first();

                if (!$program) {
                    continue;
                }

                // 開始時刻をチェック（5分以内に開始する番組）
                $programStart = Carbon::parse($program->start)->format('Hi');
                $fiveMinutesLater = Carbon::now()->addMinutes(5)->format('Hi');

                if ($programStart >= $now && $programStart <= $fiveMinutesLater) {
                    // 通知を送信
                    $notificationService->notifyFavoriteProgramBroadcast(
                        $favorite->user,
                        $favorite->program_title,
                        $favorite->station_id
                    );

                    $notificationCount++;
                    $this->info(sprintf(
                        '通知送信: %s - %s (ユーザーID: %d)',
                        $favorite->station_id,
                        $favorite->program_title,
                        $favorite->user_id
                    ));
                }
            } catch (\Exception $e) {
                $this->error(sprintf(
                    'エラー: %s - %s',
                    $favorite->program_title,
                    $e->getMessage()
                ));
                Log::error('お気に入り番組放送チェックエラー', [
                    'favorite_id' => $favorite->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info(sprintf('%d件の通知を送信しました', $notificationCount));
        return 0;
    }
}
