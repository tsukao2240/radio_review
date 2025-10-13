<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class RecordingCleanupService
{
    /**
     * 古い録音ファイルを自動削除
     * 
     * @param int $daysToKeep 保持日数（デフォルト30日）
     * @return array 削除結果
     */
    public function cleanupOldRecordings(int $daysToKeep = 30): array
    {
        $deletedCount = 0;
        $freedSpace = 0;
        $errors = [];

        try {
            $recordings = $this->getAllRecordings();
            $cutoffDate = Carbon::now()->subDays($daysToKeep);

            foreach ($recordings as $recording) {
                if (!isset($recording['created_at'])) {
                    continue;
                }

                $createdAt = Carbon::parse($recording['created_at']);

                if ($createdAt->lt($cutoffDate)) {
                    $filepath = $recording['filepath'] ?? null;

                    if ($filepath && file_exists($filepath)) {
                        $filesize = filesize($filepath);

                        if (@unlink($filepath)) {
                            $deletedCount++;
                            $freedSpace += $filesize;

                            // キャッシュからも削除
                            $recordingId = $this->extractRecordingId($recording);
                            if ($recordingId) {
                                Cache::forget("recording_{$recordingId}");
                            }
                        } else {
                            $errors[] = "Failed to delete: {$filepath}";
                        }
                    }
                }
            }

            return [
                'success' => true,
                'deleted_count' => $deletedCount,
                'freed_space_mb' => round($freedSpace / 1024 / 1024, 2),
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * ストレージ使用量を取得
     * 
     * @return array ストレージ情報
     */
    public function getStorageInfo(): array
    {
        $recordingPath = storage_path(env('RECORDING_STORAGE_PATH', 'app/recordings'));
        $totalSize = 0;
        $fileCount = 0;

        if (is_dir($recordingPath)) {
            $files = glob($recordingPath . '/*.m4a');
            $fileCount = count($files);

            foreach ($files as $file) {
                if (file_exists($file)) {
                    $totalSize += filesize($file);
                }
            }
        }

        // ディスク全体の容量
        $diskTotal = disk_total_space($recordingPath);
        $diskFree = disk_free_space($recordingPath);

        return [
            'recordings_count' => $fileCount,
            'recordings_size_mb' => round($totalSize / 1024 / 1024, 2),
            'disk_total_gb' => round($diskTotal / 1024 / 1024 / 1024, 2),
            'disk_free_gb' => round($diskFree / 1024 / 1024 / 1024, 2),
            'disk_used_percent' => round((($diskTotal - $diskFree) / $diskTotal) * 100, 2)
        ];
    }

    /**
     * 録音ファイルを圧縮（将来的な拡張用）
     * 
     * @param string $recordingId
     * @return bool
     */
    public function compressRecording(string $recordingId): bool
    {
        // 将来的にFFmpegで再エンコードして圧縮する機能を追加可能
        return false;
    }

    /**
     * すべての録音情報を取得（プライベートメソッド）
     */
    private function getAllRecordings(): array
    {
        $recordings = [];

        $store = Cache::getStore();
        if ($store instanceof \Illuminate\Cache\RedisStore) {
            $redis = $store->connection();
            $keys = $redis->keys('laravel_database_recording_*');

            foreach ($keys as $key) {
                $cleanKey = str_replace('laravel_database_', '', $key);
                $recordingInfo = Cache::get($cleanKey);

                if ($recordingInfo) {
                    $recordings[] = $recordingInfo;
                }
            }
        }

        return $recordings;
    }

    /**
     * 録音情報から録音IDを抽出
     */
    private function extractRecordingId(array $recording): ?string
    {
        if (isset($recording['filename'])) {
            return str_replace('.m4a', '', $recording['filename']);
        }

        return null;
    }
}
