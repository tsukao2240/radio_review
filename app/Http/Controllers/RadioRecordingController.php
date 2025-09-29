<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use GuzzleHttp\Client;

class RadioRecordingController extends Controller
{
    private $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'connect_timeout' => 10
        ]);
    }

    // タイムフリー録音開始
    public function startTimefreeRecording(Request $request): JsonResponse
    {
        $stationId = $request->input('station_id');
        $title = $request->input('title');
        $startTime = $request->input('start_time'); // YYYYMMDDHHMM形式
        $endTime = $request->input('end_time'); // YYYYMMDDHHMM形式

        if (!$stationId || !$startTime || !$endTime) {
            return response()->json(['success' => false, 'message' => '放送局ID、開始時間、終了時間が必要です']);
        }

        // 放送終了から1週間以内かチェック
        $broadcastEnd = Carbon::createFromFormat('YmdHi', $endTime);
        if ($broadcastEnd->addWeek()->isPast()) {
            return response()->json(['success' => false, 'message' => 'タイムフリー期間（1週間）を過ぎています']);
        }

        // 録音ファイル名を生成
        $timestamp = Carbon::now()->format('YmdHis');
        $filename = "{$stationId}_{$startTime}_{$endTime}_{$timestamp}.m4a";
        $filepath = storage_path("app/recordings/{$filename}");

        // recordingsディレクトリを作成
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        try {
            // 認証トークンを取得
            $authToken = $this->getRadikoAuthToken();
            if (!$authToken) {
                return response()->json(['success' => false, 'message' => '認証に失敗しました']);
            }

            // タイムフリーのプレイリストURLを構築
            $playlistUrl = sprintf(
                'https://radiko.jp/v2/api/ts/playlist.m3u8?station_id=%s&l=15&lsid=%s&ft=%s&to=%s',
                $stationId,
                bin2hex(random_bytes(16)),
                $startTime . '00',
                $endTime . '00'
            );

            // 録音処理をバックグラウンドで実行
            $this->executeTimefreeRecording($playlistUrl, $filepath, $authToken);

            // 録音情報をキャッシュに保存
            $recordingInfo = [
                'station_id' => $stationId,
                'title' => $title,
                'filename' => $filename,
                'filepath' => $filepath,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'created_at' => Carbon::now()->toISOString(),
                'status' => 'recording'
            ];

            $recordingId = "{$stationId}_{$startTime}_{$timestamp}";
            Cache::put("recording_{$recordingId}", $recordingInfo, 7200);

            return response()->json([
                'success' => true,
                'message' => 'タイムフリー録音を開始しました',
                'recording_id' => $recordingId,
                'filename' => $filename
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'タイムフリー録音開始に失敗しました: ' . $e->getMessage()
            ]);
        }
    }

    // radiko認証トークンを取得
    private function getRadikoAuthToken(): ?string
    {
        try {
            // 第1步: 認証トークンを取得
            $response1 = $this->client->post('https://radiko.jp/v2/api/auth1', [
                'headers' => [
                    'User-Agent' => 'curl/7.68.0',
                    'Accept' => '*/*'
                ],
                'timeout' => 10
            ]);

            $authToken = $response1->getHeaderLine('X-Radiko-AuthToken');
            $keyLength = (int)$response1->getHeaderLine('X-Radiko-KeyLength');
            $keyOffset = (int)$response1->getHeaderLine('X-Radiko-KeyOffset');

            if (!$authToken || !$keyLength || !$keyOffset) {
                \Log::error('radiko auth1レスポンスヘッダーが不正', [
                    'auth_token' => $authToken ? 'あり' : 'なし',
                    'key_length' => $keyLength,
                    'key_offset' => $keyOffset
                ]);
                return null;
            }

            // 第2步: playerCommon.jsから部分キーを取得
            $jsContent = file_get_contents('https://radiko.jp/apps/js/playerCommon.js');
            if (!$jsContent) {
                \Log::error('playerCommon.jsの取得に失敗');
                return null;
            }

            // 部分キーを抽出
            if (strlen($jsContent) < $keyOffset + $keyLength) {
                \Log::error('部分キーのオフセットが範囲外', [
                    'content_length' => strlen($jsContent),
                    'required' => $keyOffset + $keyLength
                ]);
                return null;
            }

            $partialKey = base64_encode(substr($jsContent, $keyOffset, $keyLength));

            // 第3步: 認証完了
            $response2 = $this->client->post('https://radiko.jp/v2/api/auth2', [
                'headers' => [
                    'X-Radiko-AuthToken' => $authToken,
                    'X-Radiko-PartialKey' => $partialKey,
                    'User-Agent' => 'curl/7.68.0'
                ],
                'timeout' => 10
            ]);

            // auth2のレスポンスステータスを確認
            if ($response2->getStatusCode() !== 200) {
                \Log::error('radiko auth2が失敗', ['status' => $response2->getStatusCode()]);
                return null;
            }

            return $authToken;

        } catch (\Exception $e) {
            \Log::error('radiko認証エラー', [
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            return null;
        }
    }

    // タイムフリー録音実行
    private function executeTimefreeRecording(string $playlistUrl, string $filepath, string $authToken): void
    {
        $command = sprintf(
            'ffmpeg -headers "X-Radiko-AuthToken: %s" -i "%s" -c:a aac -b:a 128k "%s" > /dev/null 2>&1 &',
            escapeshellarg($authToken),
            escapeshellarg($playlistUrl),
            escapeshellarg($filepath)
        );

        exec($command);
    }

    // 録音停止
    public function stopRecording(Request $request): JsonResponse
    {
        $recordingId = $request->input('recording_id');

        if (!$recordingId) {
            return response()->json(['success' => false, 'message' => '録音IDが必要です']);
        }

        try {
            $recordingInfo = Cache::get("recording_{$recordingId}");

            if (!$recordingInfo) {
                return response()->json(['success' => false, 'message' => '録音情報が見つかりません']);
            }

            // ffmpegプロセスを停止
            exec("pkill -f 'ffmpeg.*{$recordingInfo['station_id']}'");

            // ステータスを更新
            $recordingInfo['status'] = 'stopped';
            $recordingInfo['stopped_at'] = Carbon::now()->toISOString();
            Cache::put("recording_{$recordingId}", $recordingInfo, 7200);

            return response()->json([
                'success' => true,
                'message' => 'タイムフリー録音を停止しました'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '録音停止に失敗しました: ' . $e->getMessage()
            ]);
        }
    }

    // 録音状態確認
    public function getRecordingStatus(Request $request): JsonResponse
    {
        $recordingId = $request->input('recording_id');

        if (!$recordingId) {
            return response()->json(['success' => false, 'message' => '録音IDが必要です']);
        }

        $recordingInfo = Cache::get("recording_{$recordingId}");

        if (!$recordingInfo) {
            return response()->json(['success' => false, 'message' => '録音情報が見つかりません']);
        }

        // ファイルの存在確認
        $fileExists = file_exists($recordingInfo['filepath']);
        $fileSize = $fileExists ? filesize($recordingInfo['filepath']) : 0;

        return response()->json([
            'success' => true,
            'status' => $recordingInfo['status'],
            'file_exists' => $fileExists,
            'file_size' => $fileSize,
            'recording_info' => $recordingInfo
        ]);
    }

    // 録音ファイルダウンロード
    public function downloadRecording(Request $request)
    {
        $recordingId = $request->input('recording_id');

        if (!$recordingId) {
            return response()->json(['success' => false, 'message' => '録音IDが必要です']);
        }

        $recordingInfo = Cache::get("recording_{$recordingId}");

        if (!$recordingInfo || !file_exists($recordingInfo['filepath'])) {
            return response()->json(['success' => false, 'message' => 'ファイルが見つかりません']);
        }

        return response()->download($recordingInfo['filepath'], $recordingInfo['filename']);
    }

    // 録音一覧
    public function listRecordings(): JsonResponse
    {
        $recordings = [];
        $cacheKeys = Cache::getRedis()->keys('laravel_database_recording_*');

        foreach ($cacheKeys as $key) {
            $recordingInfo = Cache::get(str_replace('laravel_database_', '', $key));
            if ($recordingInfo) {
                $recordings[] = $recordingInfo;
            }
        }

        return response()->json(['success' => true, 'recordings' => $recordings]);
    }
}