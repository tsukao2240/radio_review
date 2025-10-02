<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitoring
{
    /**
     * リクエストを処理する
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // パフォーマンス監視が無効の場合はスキップ
        if (!config('monitoring.enabled', false)) {
            return $next($request);
        }

        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        // リクエストを処理
        $response = $next($request);

        // 実行時間とメモリ使用量を計算
        $executionTime = (microtime(true) - $startTime) * 1000; // ミリ秒
        $memoryUsage = (memory_get_usage() - $startMemory) / 1024 / 1024; // MB
        $peakMemory = memory_get_peak_usage() / 1024 / 1024; // MB

        // 閾値をチェック
        $threshold = config('monitoring.threshold_ms', 1000);
        $memoryThreshold = config('monitoring.memory_threshold_mb', 50);

        $data = [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'execution_time_ms' => round($executionTime, 2),
            'memory_usage_mb' => round($memoryUsage, 2),
            'peak_memory_mb' => round($peakMemory, 2),
            'status_code' => $response->getStatusCode(),
            'ip' => $request->ip(),
            'user_id' => $request->user()?->id,
        ];

        // 閾値を超えた場合は警告ログ
        if ($executionTime > $threshold) {
            Log::warning('遅いリクエストを検出', array_merge($data, [
                'threshold_ms' => $threshold,
                'type' => 'slow_request'
            ]));
        }

        if ($memoryUsage > $memoryThreshold) {
            Log::warning('メモリ使用量が多いリクエストを検出', array_merge($data, [
                'memory_threshold_mb' => $memoryThreshold,
                'type' => 'high_memory'
            ]));
        }

        // 詳細ログが有効な場合はすべてのリクエストを記録
        if (config('monitoring.log_all_requests', false)) {
            Log::info('リクエストパフォーマンス', $data);
        }

        // レスポンスヘッダーに情報を追加（開発環境のみ）
        if (config('app.debug', false)) {
            $response->headers->set('X-Execution-Time', round($executionTime, 2) . 'ms');
            $response->headers->set('X-Memory-Usage', round($memoryUsage, 2) . 'MB');
            $response->headers->set('X-Peak-Memory', round($peakMemory, 2) . 'MB');
        }

        return $response;
    }
}
