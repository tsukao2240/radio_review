<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use GuzzleHttp\Client;
use App\Exceptions\RecordingException;
use App\Exceptions\ExternalApiException;

class RadioRecordingController extends Controller
{
    private $client;

    // 放送局ID → 主エリアID（本社所在地）のマッピング
    private const STATION_AREA_MAP = [
        // 北海道・東北
        'HBC' => 'JP1', 'STV' => 'JP1', 'AIR-G' => 'JP1', 'NORTHWAVE' => 'JP1', // 北海道
        'RAB' => 'JP2', // 青森
        'IBC' => 'JP3', 'FMI' => 'JP3', // 岩手
        'TBC' => 'JP4', 'DATEFM' => 'JP4', // 宮城
        'ABS' => 'JP5', 'AFM' => 'JP5', // 秋田
        'YBC' => 'JP6', 'YFM' => 'JP6', // 山形
        'RFC' => 'JP7', 'FMF' => 'JP7', // 福島
        // 関東
        'JOQR' => 'JP13', 'TBS' => 'JP13', 'JORF' => 'JP13', 'INT' => 'JP13',
        'J-WAVE' => 'JP13', 'FMT' => 'JP13', 'HOUSOU-DAIGAKU' => 'JP13',
        'QRR' => 'JP13', 'LFR' => 'JP13', 'RN1' => 'JP13', 'RN2' => 'JP13',
        'CRT' => 'JP12', 'BAYFM78' => 'JP12', // 千葉
        'YFM' => 'JP14', 'FMY' => 'JP14', // 神奈川
        'NACK5' => 'JP11', 'FMN' => 'JP11', // 埼玉
        'CRK' => 'JP8', 'RCC' => 'JP9', 'FM-GUNMA' => 'JP10', // 茨城・栃木・群馬
        // 中部
        'BSN' => 'JP15', 'FM-NIIGATA' => 'JP15', // 新潟
        'KNB' => 'JP16', 'FMT' => 'JP16', // 富山
        'MRO' => 'JP17', 'HELLO FIVE' => 'JP17', // 石川
        'FBC' => 'JP18', 'FM-FUKUI' => 'JP18', // 福井
        'YBS' => 'JP19', 'FM-FUJI' => 'JP19', // 山梨
        'SBC' => 'JP20', 'FMN' => 'JP20', // 長野
        'CBC' => 'JP23', 'SF' => 'JP23', 'ZIP-FM' => 'JP23', '@FM' => 'JP23', // 愛知
        'GBS' => 'JP21', 'FMG' => 'JP21', // 岐阜
        'SBS' => 'JP22', 'K-MIX' => 'JP22', // 静岡
        // 関西
        'MBS' => 'JP27', 'ABC' => 'JP27', 'OBC' => 'JP27',
        'FM802' => 'JP27', 'FMO' => 'JP27', 'FM-COCOLO' => 'JP27',
        'CCL' => 'JP28', 'CRK' => 'JP28', 'FMOH' => 'JP28', 'KISS FM' => 'JP28', // 兵庫
        'KBS' => 'JP26', 'α-STATION' => 'JP26', // 京都
        'WBS' => 'JP30', 'FMW' => 'JP30', // 和歌山
        'FMNARA' => 'JP29', 'MIE-FM' => 'JP24', 'BBC' => 'JP25', 'e-radio' => 'JP25',
        // 中国
        'BSS' => 'JP31', // 鳥取・島根
        'RSK' => 'JP33', 'FM-OKAYAMA' => 'JP33', // 岡山
        'RCC' => 'JP34', 'HFM' => 'JP34', 'FM-FUKUYAMA' => 'JP34', // 広島
        'KRY' => 'JP35', 'FMY' => 'JP35', // 山口
        // 四国
        'JRT' => 'JP36', 'FMT' => 'JP36', // 徳島
        'RNC' => 'JP37', 'FM-KAGAWA' => 'JP37', // 香川
        'RNB' => 'JP38', 'JOEU-FM' => 'JP38', // 愛媛
        'RKC' => 'JP39', 'HI-SIX' => 'JP39', // 高知
        // 九州・沖縄
        'KBC' => 'JP40', 'RKB' => 'JP40', 'LOVE-FM' => 'JP40', 'FM-FUKUOKA' => 'JP40', 'CROSS FM' => 'JP40',
        'STS' => 'JP41', // 佐賀
        'NBC' => 'JP42', 'FM-NAGASAKI' => 'JP42', // 長崎
        'RKK' => 'JP43', 'FMK' => 'JP43', // 熊本
        'OBS' => 'JP44', 'FM-OITA' => 'JP44', // 大分
        'MRT' => 'JP45', 'JOY-FM' => 'JP45', // 宮崎
        'MBC' => 'JP46', 'μFM' => 'JP46', // 鹿児島
        'RBC' => 'JP47', 'ROK' => 'JP47', 'FM-OKINAWA' => 'JP47', 'FM21' => 'JP47', // 沖縄
    ];

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'connect_timeout' => 2,
            'verify' => false,
            'http_errors' => false,
            'allow_redirects' => true,
            'decode_content' => false,
            'curl' => [
                CURLOPT_MAXCONNECTS => 50, // 高速化のため接続数を増加
                CURLOPT_TCP_NODELAY => 1, // Nagleアルゴリズム無効化
                CURLOPT_FORBID_REUSE => 0, // 接続再利用
                CURLOPT_TCP_KEEPALIVE => 1, // Keep-Alive有効
                CURLOPT_TCP_KEEPIDLE => 10,
                CURLOPT_TCP_KEEPINTVL => 5,
            ]
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

        // 録音ファイル名を生成（ファイル名として使えない文字をサニタイズ）
        $timestamp = Carbon::now()->format('YmdHis');
        $sanitizedTitle = preg_replace('/[\/\\\:\*\?\"\<\>\|]/', '_', $title);
        $filename = "{$stationId}_{$sanitizedTitle}_{$startTime}.m4a";
        $filepath = storage_path("app/recordings/{$filename}");

        // recordingsディレクトリを作成
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        try {
            // エリアIDを取得（未指定の場合は放送局IDから自動判定）
            $areaId = $request->input('area_id');
            if (empty($areaId)) {
                $areaId = $this->getAreaIdFromStationId($stationId);
            }

            \Log::info('タイムフリー録音リクエスト受信', [
                'station_id' => $stationId,
                'area_id' => $areaId,
                'auto_detected' => empty($request->input('area_id')),
                'title' => $title
            ]);

            // 認証トークンを取得（エリアフリー対応）
            $authToken = $this->getRadikoAuthToken($areaId);
            if (!$authToken) {
                return response()->json(['success' => false, 'message' => '認証に失敗しました']);
            }

            // 録音情報をキャッシュに保存（録音開始前に保存）
            $recordingId = "{$stationId}_{$startTime}_{$timestamp}";
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
            Cache::put("recording_{$recordingId}", $recordingInfo, 7200);

            // テスト環境では非同期処理をスキップ
            if (app()->environment('testing')) {
                // テスト時は同期実行せずに即座にレスポンスを返す
                return response()->json([
                    'success' => true,
                    'message' => 'タイムフリー録音を開始しました',
                    'recording_id' => $recordingId,
                    'filename' => $filename
                ]);
            }

            // 録音レスポンスを先に返す（非同期風）
            // Laravelのresponse()->send()を使って先にレスポンスを返す
            $response = response()->json([
                'success' => true,
                'message' => 'タイムフリー録音を開始しました',
                'recording_id' => $recordingId,
                'filename' => $filename
            ]);

            // FastCGIの場合はfastcgi_finish_requestを使用
            if (function_exists('fastcgi_finish_request')) {
                $response->send();
                fastcgi_finish_request();
            } else {
                // 通常のPHP-FPMの場合
                $response->send();
                if (ob_get_level()) {
                    ob_end_flush();
                }
                flush();
            }

            // この後の処理はクライアントに返した後に実行される
            $this->executeTimefreeRecording($stationId, $startTime, $endTime, $filepath, $authToken, $areaId, $recordingId);

            // 録音完了後、キャッシュを更新
            $recordingInfo['status'] = 'completed';
            Cache::put("recording_{$recordingId}", $recordingInfo, 7200);

            return $response;

        } catch (\Exception $e) {
            \Log::error('タイムフリー録音開始エラー', ['error' => $e->getMessage(), 'station_id' => $stationId]);
            throw new RecordingException('タイムフリー録音の開始に失敗しました', 0, $e);
        }
    }

    // radiko認証トークンを取得（Web API + Android認証キー版 - rajiko方式）
    private function getRadikoAuthToken(?string $areaId = null): ?string
    {
        try {
            // デバイス情報を生成（rajiko方式）
            $appVersion = '8.2.4';
            $userId = bin2hex(random_bytes(16)); // 32文字の16進数
            $sdkVersion = '34'; // Android 14
            $model = 'GooglePixel6';
            $device = "{$sdkVersion}.{$model}";

            // 第1步: auth1でトークンとキー情報を取得
            $response1 = $this->client->get('https://radiko.jp/v2/api/auth1', [
                'headers' => [
                    'X-Radiko-App' => 'aSmartPhone8',
                    'X-Radiko-App-Version' => $appVersion,
                    'X-Radiko-Device' => $device,
                    'X-Radiko-User' => $userId,
                ],
                'timeout' => 10
            ]);

            $authToken = $response1->getHeaderLine('X-Radiko-AuthToken');
            $keyLength = (int)$response1->getHeaderLine('X-Radiko-KeyLength');
            $keyOffset = (int)$response1->getHeaderLine('X-Radiko-KeyOffset');

            if (!$authToken || !$keyLength) {
                \Log::error('radiko auth1レスポンスヘッダーが不正', [
                    'auth_token' => $authToken ? 'あり' : 'なし',
                    'key_length' => $keyLength,
                    'key_offset' => $keyOffset
                ]);
                return null;
            }

            \Log::info('radiko auth1成功', [
                'key_length' => $keyLength,
                'key_offset' => $keyOffset
            ]);

            // 第2步: JPEG認証キーから部分キーを取得
            $keyPath = storage_path('app/keys/radiko_auth_key.txt');
            if (!file_exists($keyPath)) {
                \Log::error('Radiko認証キーファイルが見つかりません', ['path' => $keyPath]);
                return null;
            }

            $authKeyB64 = trim(file_get_contents($keyPath));
            $authKey = base64_decode($authKeyB64);

            \Log::info('JPEG認証キーを使用', [
                'key_length' => strlen($authKey),
                'offset' => $keyOffset,
                'extract_length' => $keyLength
            ]);

            // 部分キーを抽出
            if (strlen($authKey) < $keyOffset + $keyLength) {
                \Log::error('部分キーのオフセットが範囲外', [
                    'auth_key_length' => strlen($authKey),
                    'required' => $keyOffset + $keyLength
                ]);
                return null;
            }

            $extractedKey = substr($authKey, $keyOffset, $keyLength);
            $partialKey = base64_encode($extractedKey);

            \Log::info('部分キー計算完了', [
                'partial_key_length' => strlen($partialKey)
            ]);

            // GPS座標を生成（エリアフリー用）
            $gpsLocation = $this->generateGpsLocation($areaId ?? 'JP13');

            // 第3步: auth2で認証完了
            $response2 = $this->client->get('https://radiko.jp/v2/api/auth2', [
                'headers' => [
                    'X-Radiko-App' => 'aSmartPhone8',
                    'X-Radiko-App-Version' => $appVersion,
                    'X-Radiko-Device' => $device,
                    'X-Radiko-User' => $userId,
                    'X-Radiko-AuthToken' => $authToken,
                    'X-Radiko-PartialKey' => $partialKey,
                    'X-Radiko-Location' => $gpsLocation,
                ],
                'timeout' => 10
            ]);

            if ($response2->getStatusCode() !== 200) {
                \Log::error('radiko auth2が失敗', [
                    'status' => $response2->getStatusCode(),
                    'body' => (string)$response2->getBody()
                ]);
                return null;
            }

            $auth2Body = (string)$response2->getBody();
            \Log::info('radiko auth2成功', ['response' => $auth2Body]);

            return $authToken;

        } catch (\Exception $e) {
            \Log::error('radiko認証エラー', [
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            return null;
        }
    }

    // タイムフリープレイリストベースURL取得（v3 API）
    private function getTimefreeBaseUrl(string $stationId, bool $isAreaFree = false): string
    {
        $streamInfoUrl = "https://radiko.jp/v3/station/stream/pc_html5/{$stationId}.xml";
        $response = $this->client->get($streamInfoUrl);
        $xml = (string)$response->getBody();

        $dom = new \DOMDocument();
        @$dom->loadXML($xml);
        $xpath = new \DOMXPath($dom);

        $timefreeAttr = '1';
        $areafreeAttr = $isAreaFree ? '1' : '0';

        // 適切なURLを選択
        foreach ($xpath->query("//url[@timefree='{$timefreeAttr}'][@areafree='{$areafreeAttr}']/playlist_create_url") as $node) {
            return $node->textContent;
        }

        // フォールバック
        foreach ($xpath->query("//url[@timefree='1']/playlist_create_url") as $node) {
            return $node->textContent;
        }

        throw new \Exception("タイムフリープレイリストURLが取得できませんでした: {$stationId}");
    }

    // 全セグメントURLを取得（rajiko方式：300秒チャンク）
    private function getAllSegmentUrls(string $stationId, string $startTime, string $endTime, string $authToken, ?string $areaId = null): array
    {
        $isAreaFree = $areaId && $areaId !== 'JP13';
        $baseUrl = $this->getTimefreeBaseUrl($stationId, $isAreaFree);
        $lsid = bin2hex(random_bytes(16));

        // 時刻をDateTimeに変換
        $startDt = \Carbon\Carbon::createFromFormat('YmdHi', $startTime, 'Asia/Tokyo');
        $endDt = \Carbon\Carbon::createFromFormat('YmdHi', $endTime, 'Asia/Tokyo');
        $seekDt = clone $startDt;

        $allSegments = [];
        $chunkSeconds = 300; // 5分チャンク

        \Log::info('セグメントURL収集開始', [
            'station' => $stationId,
            'start' => $startTime,
            'end' => $endTime,
            'duration_minutes' => $startDt->diffInMinutes($endDt),
            'area_id' => $areaId,
            'is_area_free' => $isAreaFree,
            'base_url' => $baseUrl
        ]);

        while ($seekDt < $endDt) {
            $params = [
                'station_id' => $stationId,
                'start_at' => $startDt->format('YmdHis'),
                'ft' => $startDt->format('YmdHis'),
                'end_at' => $endDt->format('YmdHis'),
                'to' => $endDt->format('YmdHis'),
                'seek' => $seekDt->format('YmdHis'),
                'l' => (string)$chunkSeconds,
                'lsid' => $lsid,
                'type' => $isAreaFree ? 'c' : 'b',
            ];

            try {
                // マスタープレイリスト取得
                $playlistUrl = $baseUrl . '?' . http_build_query($params);
                $playlistResp = $this->client->get($playlistUrl, [
                    'headers' => ['X-Radiko-AuthToken' => $authToken],
                    'timeout' => 10
                ]);
                $masterContent = (string)$playlistResp->getBody();

                \Log::info('プレイリスト取得', [
                    'seek' => $seekDt->format('YmdHis'),
                    'status' => $playlistResp->getStatusCode(),
                    'content_length' => strlen($masterContent),
                    'content_preview' => substr($masterContent, 0, 200)
                ]);

                // medialist URL抽出
                if (preg_match('/(https:\/\/[^\s]+medialist[^\s]+)/', $masterContent, $m)) {
                    $medialistResp = $this->client->get($m[1], [
                        'headers' => ['X-Radiko-AuthToken' => $authToken],
                        'timeout' => 10
                    ]);
                    $medialistContent = (string)$medialistResp->getBody();

                    // AACセグメントURL抽出
                    if (preg_match_all('/https:\/\/[^\s]+\.aac/', $medialistContent, $segs)) {
                        $allSegments = array_merge($allSegments, $segs[0]);
                    }
                } else {
                    \Log::warning('medialistURL未検出', ['seek' => $seekDt->format('YmdHis')]);
                }
            } catch (\Exception $e) {
                \Log::warning('セグメント取得エラー', ['seek' => $seekDt->format('YmdHis'), 'error' => $e->getMessage()]);
            }

            $seekDt->addSeconds($chunkSeconds);
        }

        \Log::info('セグメントURL収集完了', ['total' => count($allSegments)]);
        return array_unique($allSegments); // 重複除去
    }

    // GPS座標を生成（エリアIDから）
    private function generateGpsLocation(string $areaId): string
    {
        // 都道府県庁所在地の座標
        $coordinates = [
            'JP1' => [43.064615, 141.346807],  // 北海道
            'JP2' => [40.824308, 140.739998],  // 青森
            'JP3' => [39.703619, 141.152684],  // 岩手
            'JP4' => [38.268837, 140.8721],    // 宮城
            'JP5' => [39.718614, 140.102364],  // 秋田
            'JP6' => [38.240436, 140.363633],  // 山形
            'JP7' => [37.750299, 140.467551],  // 福島
            'JP8' => [36.341811, 140.446793],  // 茨城
            'JP9' => [36.565725, 139.883565],  // 栃木
            'JP10' => [36.390668, 139.060406], // 群馬
            'JP11' => [35.856999, 139.648849], // 埼玉
            'JP12' => [35.605057, 140.123306], // 千葉
            'JP13' => [35.689487, 139.691711], // 東京
            'JP14' => [35.447507, 139.642342], // 神奈川
            'JP15' => [37.902552, 139.023095], // 新潟
            'JP16' => [36.695291, 137.211338], // 富山
            'JP17' => [36.594682, 136.625573], // 石川
            'JP18' => [36.065178, 136.221527], // 福井
            'JP19' => [35.664158, 138.568449], // 山梨
            'JP20' => [36.651299, 138.180956], // 長野
            'JP21' => [35.391227, 136.722291], // 岐阜
            'JP22' => [34.97712, 138.383084],  // 静岡
            'JP23' => [35.180188, 136.906565], // 愛知
            'JP24' => [34.730283, 136.508588], // 三重
            'JP25' => [35.004531, 135.86859],  // 滋賀
            'JP26' => [35.021247, 135.755597], // 京都
            'JP27' => [34.686297, 135.519661], // 大阪
            'JP28' => [34.691269, 135.183071], // 兵庫
            'JP29' => [34.685334, 135.832742], // 奈良
            'JP30' => [34.225987, 135.167506], // 和歌山
            'JP31' => [35.503891, 134.237736], // 鳥取
            'JP32' => [35.472295, 133.0505],   // 島根
            'JP33' => [34.661751, 133.934406], // 岡山
            'JP34' => [34.39656, 132.459622],  // 広島
            'JP35' => [34.185956, 131.470649], // 山口
            'JP36' => [34.065718, 134.55936],  // 徳島
            'JP37' => [34.340149, 134.043444], // 香川
            'JP38' => [33.841624, 132.765681], // 愛媛
            'JP39' => [33.559706, 133.531079], // 高知
            'JP40' => [33.606576, 130.418297], // 福岡
            'JP41' => [33.249442, 130.299794], // 佐賀
            'JP42' => [32.744839, 129.873756], // 長崎
            'JP43' => [32.789827, 130.741667], // 熊本
            'JP44' => [33.238172, 131.612619], // 大分
            'JP45' => [31.911096, 131.423893], // 宮崎
            'JP46' => [31.560146, 130.557978], // 鹿児島
            'JP47' => [26.2124, 127.680932],   // 沖縄
        ];

        // デフォルトは東京
        $coords = $coordinates[$areaId] ?? $coordinates['JP13'];

        // 少しランダムにずらす（±0.025度 = 約2.5km）
        $lat = $coords[0] + (mt_rand(-250, 250) / 10000);
        $long = $coords[1] + (mt_rand(-250, 250) / 10000);

        return sprintf('%.6f,%.6f,gps', $lat, $long);
    }

    // タイムフリー録音実行（高速版：rajiko方式）
    private function executeTimefreeRecording(string $stationId, string $startTime, string $endTime, string $filepath, string $authToken, ?string $areaId = null, string $recordingId = null): void
    {
        // 全セグメントURLを取得
        $segments = $this->getAllSegmentUrls($stationId, $startTime, $endTime, $authToken, $areaId);

        if (empty($segments)) {
            throw new \Exception('セグメントが見つかりませんでした');
        }

        // 高速並列ダウンロード
        $this->fastParallelDownload($segments, $filepath, $authToken, $recordingId);
        return;

        // 以下はフォールバック用（エラー時のみ）
        // ffmpegの存在確認
        $ffmpegPath = $this->findFFmpeg();
        if (!$ffmpegPath) {
            \Log::error('ffmpegが見つかりません。録音を実行できません。');
            throw new \Exception('ffmpegがインストールされていません。録音機能を使用するにはffmpegをインストールしてください。');
        }

        // パスとファイルパスの正規化
        $normalizedFFmpegPath = $this->normalizePath($ffmpegPath);
        $normalizedFilePath = $this->normalizePath($filepath);

        // クロスプラットフォーム対応のコマンド構築（最大限の高速化設定）
        // -http_persistent 0: HTTP接続を使い捨てにしてオーバーヘッド削減
        // -multiple_requests 1: 複数のHTTPリクエストを許可
        // -seekable 0: シーク不要（ダウンロードのみ）
        // -reconnect 1: 接続エラー時に再接続
        // -reconnect_streamed 1: ストリーミング中の再接続を有効化
        // -reconnect_delay_max 2: 再接続の最大遅延
        // -c:a copy: 再エンコードなし（ストリームコピー）
        if (PHP_OS_FAMILY === 'Windows') {
            // Windowsの場合
            $command = sprintf(
                'start /B "" "%s" -loglevel error -protocol_whitelist "file,http,https,tcp,tls" -http_persistent 0 -multiple_requests 1 -seekable 0 -reconnect 1 -reconnect_streamed 1 -reconnect_delay_max 2 -headers "X-Radiko-AuthToken: %s" -i "%s" -c:a copy -bsf:a aac_adtstoasc -movflags +faststart "%s" 2>NUL',
                $normalizedFFmpegPath,
                $authToken,
                $playlistUrl,
                $normalizedFilePath
            );
        } else {
            // Linux/macOSの場合
            $command = sprintf(
                '"%s" -loglevel error -protocol_whitelist "file,http,https,tcp,tls" -http_persistent 0 -multiple_requests 1 -seekable 0 -reconnect 1 -reconnect_streamed 1 -reconnect_delay_max 2 -headers "X-Radiko-AuthToken: %s" -i "%s" -c:a copy -bsf:a aac_adtstoasc -movflags +faststart "%s" > /dev/null 2>&1 &',
                $normalizedFFmpegPath,
                $authToken,
                $playlistUrl,
                $normalizedFilePath
            );
        }

        \Log::info('ffmpeg録音コマンド実行', [
            'command' => $command,
            'ffmpeg_path' => $normalizedFFmpegPath,
            'output_path' => $normalizedFilePath,
            'os_family' => PHP_OS_FAMILY
        ]);

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            \Log::error('ffmpegコマンド実行失敗', [
                'return_code' => $returnCode,
                'output' => $output
            ]);
        }
    }

    // ffmpegの実行ファイルを探す
    private function findFFmpeg(): ?string
    {
        $actualOS = $this->detectActualOS();

        // 両方のパターンを試す（環境検出が間違っている可能性があるため）
        $projectPaths = [
            base_path('ffmpeg/ffmpeg-7.0.2-amd64-static/ffmpeg'), // Linux用（静的ビルド）
            base_path('ffmpeg/ffmpeg-master-latest-win64-gpl/bin/ffmpeg.exe'), // Windows用
            base_path('ffmpeg/ffmpeg'),  // Linux用（直接配置）
            base_path('ffmpeg/ffmpeg.exe') // Windows用（直接配置）
        ];

        foreach ($projectPaths as $projectFFmpeg) {
            \Log::info('プロジェクト内ffmpegパス確認', [
                'path' => $projectFFmpeg,
                'exists' => file_exists($projectFFmpeg)
            ]);

            if (file_exists($projectFFmpeg)) {
                \Log::info('プロジェクト内ffmpegを検出', ['path' => $projectFFmpeg]);
                return $projectFFmpeg;
            }
        }

        // システムパスでの検索（両OS対応）
        $possiblePaths = [
            'ffmpeg',
            'ffmpeg.exe',
            '/usr/bin/ffmpeg',
            '/usr/local/bin/ffmpeg',
            '/opt/homebrew/bin/ffmpeg',
            '/snap/bin/ffmpeg',
            'C:\ffmpeg\bin\ffmpeg.exe',
            'C:\Program Files\ffmpeg\bin\ffmpeg.exe',
        ];

        foreach ($possiblePaths as $path) {
            if ($this->isExecutableAvailable($path)) {
                return $path;
            }
        }

        \Log::error('ffmpegが見つかりませんでした');
        return null;
    }

    // 実際のOS環境を検出
    private function detectActualOS(): string
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return 'Windows';
        }
        if (strpos(strtolower(PHP_OS), 'linux') !== false) {
            return 'Linux';
        }
        if (strpos(strtolower(PHP_OS), 'darwin') !== false) {
            return 'macOS';
        }
        return PHP_OS;
    }

    // Windows環境かどうかを判定（複数の方法で確認）
    private function isWindowsEnvironment(): bool
    {
        return PHP_OS_FAMILY === 'Windows' ||
               strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ||
               DIRECTORY_SEPARATOR === '\\' ||
               strpos(strtolower(php_uname('s')), 'windows') !== false;
    }

    // 実行可能ファイルが利用可能かチェック
    private function isExecutableAvailable(string $executable): bool
    {
        $testCommand = PHP_OS_FAMILY === 'Windows'
            ? "where \"$executable\" 2>NUL"
            : "which \"$executable\" 2>/dev/null";

        exec($testCommand, $output, $returnCode);
        return $returnCode === 0;
    }

    // パスを環境に応じて正規化
    private function normalizePath(string $path): string
    {
        // Windowsパス形式に変換（必要に応じて）
        if (PHP_OS_FAMILY === 'Windows') {
            // スラッシュをバックスラッシュに変換
            $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
        } else {
            // バックスラッシュをスラッシュに変換
            $path = str_replace('\\', '/', $path);
        }

        return $path;
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
            \Log::error('録音停止エラー', ['error' => $e->getMessage(), 'recording_id' => $recordingId]);
            throw new RecordingException('録音の停止に失敗しました', 0, $e);
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

        // 録音経過時間を計算
        $startTime = \Carbon\Carbon::parse($recordingInfo['created_at']);
        $elapsedSeconds = $startTime->diffInSeconds(\Carbon\Carbon::now());

        // 予定録音時間を計算
        $startTimeStr = $recordingInfo['start_time'];
        $endTimeStr = $recordingInfo['end_time'];
        $plannedDurationMinutes = $this->calculateDurationMinutes($startTimeStr, $endTimeStr);

        // 予想ファイルサイズを計算（1分あたり約350KB）
        $expectedFileSize = $plannedDurationMinutes * 350 * 1024;

        // 進捗率を計算（ファイルサイズベース、最大100%）
        $progressPercentage = $expectedFileSize > 0
            ? min(100, ($fileSize / $expectedFileSize) * 100)
            : 0;

        // ファイルサイズを人間が読みやすい形式に変換
        $fileSizeFormatted = $this->formatFileSize($fileSize);

        // 録音状態を判定
        // キャッシュのstatusを確認、まだcompletedになっていない場合は録音中
        $cacheStatus = $recordingInfo['status'] ?? 'recording';

        // ファイルが存在して95%以上なら完了
        if ($fileExists && $fileSize > 0 && $progressPercentage >= 95) {
            $isRecording = false;
            $finalStatus = 'completed';
        } elseif ($cacheStatus === 'completed') {
            // キャッシュで完了になっている
            $isRecording = false;
            $finalStatus = 'completed';
        } else {
            // 録音中
            $isRecording = true;
            $finalStatus = 'recording';
        }

        $responseData = [
            'success' => true,
            'status' => $finalStatus,
            'file_exists' => $fileExists,
            'file_size' => $fileSize,
            'file_size_formatted' => $fileSizeFormatted,
            'elapsed_seconds' => $elapsedSeconds,
            'elapsed_time_formatted' => $this->formatDuration($elapsedSeconds),
            'planned_duration_minutes' => $plannedDurationMinutes,
            'progress_percentage' => round($progressPercentage, 0),
            'is_recording' => $isRecording,
            'recording_info' => $recordingInfo
        ];

        return response()->json($responseData);
    }

    // 録音時間（分）を計算
    private function calculateDurationMinutes(string $startTime, string $endTime): int
    {
        try {
            // YYYYMMDDHHMM 形式から時刻を抽出
            $startHour = (int)substr($startTime, 8, 2);
            $startMinute = (int)substr($startTime, 10, 2);
            $endHour = (int)substr($endTime, 8, 2);
            $endMinute = (int)substr($endTime, 10, 2);

            $startTotalMinutes = $startHour * 60 + $startMinute;
            $endTotalMinutes = $endHour * 60 + $endMinute;

            // 日をまたぐ場合を考慮
            if ($endTotalMinutes < $startTotalMinutes) {
                $endTotalMinutes += 24 * 60;
            }

            return $endTotalMinutes - $startTotalMinutes;
        } catch (\Exception $e) {
            return 0;
        }
    }

    // ファイルサイズを人間が読みやすい形式に変換
    private function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 1) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 0) . ' KB';
        } else {
            return $bytes . ' B';
        }
    }

    // 秒数を時間表記に変換
    private function formatDuration(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $remainingSeconds);
        } else {
            return sprintf('%d:%02d', $minutes, $remainingSeconds);
        }
    }

    // 高速並列ダウンロード（rajiko方式）
    private function fastParallelDownload(array $segments, string $filepath, string $authToken, string $recordingId = null): void
    {
        // メモリ制限を緩和（高速ダウンロード用）
        ini_set('memory_limit', '512M');
        set_time_limit(600); // 10分タイムアウト

        \Log::info('高速ダウンロード開始', ['segments' => count($segments)]);

        // 一時ディレクトリ作成
        $tempDir = storage_path('app/temp_segments/' . basename($filepath, '.m4a'));
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        // 高速並列ダウンロード（rajiko方式：待機なし）
        $downloadStartTime = microtime(true);
        $maxParallel = 20; // 高速化のため並列数を増加
        $segmentFiles = [];
        $chunks = array_chunk($segments, $maxParallel);

        \Log::info('セグメントダウンロード開始', [
            'total_segments' => count($segments),
            'parallel' => $maxParallel,
            'chunks' => count($chunks)
        ]);

        foreach ($chunks as $chunkIndex => $chunk) {
            $promises = [];
            foreach ($chunk as $index => $segmentUrl) {
                $actualIndex = $chunkIndex * $maxParallel + $index;
                $segmentFile = $tempDir . '/segment_' . str_pad($actualIndex, 5, '0', STR_PAD_LEFT) . '.aac';
                $segmentFiles[] = $segmentFile;

                $promises[] = $this->client->getAsync($segmentUrl, [
                    'headers' => [
                        'X-Radiko-AuthToken' => $authToken,
                        'Connection' => 'keep-alive',
                        'Accept-Encoding' => 'identity', // 圧縮なしで高速化
                        'User-Agent' => 'Mozilla/5.0'
                    ],
                    'sink' => $segmentFile,
                    'timeout' => 15,
                    'connect_timeout' => 2
                ]);
            }

            // 並列実行（待機なし）
            \GuzzleHttp\Promise\Utils::settle($promises)->wait();
        }

        $downloadTime = microtime(true) - $downloadStartTime;
        // セグメントを結合（stream_copy_to_streamで高速化）
        $outputHandle = fopen($filepath, 'wb');
        foreach ($segmentFiles as $segmentFile) {
            if (file_exists($segmentFile)) {
                $inputHandle = fopen($segmentFile, 'rb');
                stream_copy_to_stream($inputHandle, $outputHandle);
                fclose($inputHandle);
                unlink($segmentFile); // 削除
            }
        }
        fclose($outputHandle);

        // 一時ディレクトリ削除
        @rmdir($tempDir);

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

        // Redisドライバーの場合のみkeys()を使用
        if (method_exists(Cache::getStore(), 'getRedis')) {
            $cacheKeys = Cache::getRedis()->keys('laravel_database_recording_*');

            foreach ($cacheKeys as $key) {
                $recordingInfo = Cache::get(str_replace('laravel_database_', '', $key));
                if ($recordingInfo) {
                    $recordings[] = $recordingInfo;
                }
            }
        }

        return response()->json(['success' => true, 'recordings' => $recordings]);
    }

    // 録音履歴画面表示
    public function showHistory()
    {
        $recordings = [];

        // Redisドライバーの場合のみkeys()を使用
        $store = Cache::getStore();
        if ($store instanceof \Illuminate\Cache\RedisStore) {
            $redis = $store->connection();

            // SCAN使用でメモリ効率的に取得（keys()よりパフォーマンスが良い）
            $cursor = null;
            $pattern = '*recording_*';

            do {
                $result = $redis->scan($cursor, ['MATCH' => $pattern, 'COUNT' => 100]);
                $cursor = $result[0];
                $keys = $result[1];

                foreach ($keys as $key) {
                    // すべてのプレフィックスを除去してrecording_で始まるキーを抽出
                    if (preg_match('/recording_[^:]+$/', $key, $matches)) {
                        $cleanKey = $matches[0];
                    } else {
                        continue;
                    }

                    $recordingInfo = Cache::get($cleanKey);

                    if ($recordingInfo) {
                        // recording_id を抽出（キーから）
                        preg_match('/recording_(.+)$/', $cleanKey, $matches);
                        $recordingId = $matches[1] ?? $cleanKey;

                        // ファイル情報を追加
                        $filepath = $recordingInfo['filepath'];
                        $fileExists = file_exists($filepath);
                        $fileSize = $fileExists ? filesize($filepath) : 0;

                        $recordingInfo['recording_id'] = $recordingId;
                        $recordingInfo['file_exists'] = $fileExists;
                        $recordingInfo['file_size'] = $this->formatFileSize($fileSize);

                        // created_atをCarbonオブジェクトに変換（日本時間に設定）
                        if (isset($recordingInfo['created_at']) && is_string($recordingInfo['created_at'])) {
                            $recordingInfo['created_at'] = \Carbon\Carbon::parse($recordingInfo['created_at'])->setTimezone('Asia/Tokyo');
                        }

                        $recordings[] = $recordingInfo;
                    }
                }
            } while ($cursor !== 0 && $cursor !== '0');
        }

        // 作成日時で降順ソート
        usort($recordings, function($a, $b) {
            $timeA = isset($a['created_at']) ? strtotime($a['created_at']) : 0;
            $timeB = isset($b['created_at']) ? strtotime($b['created_at']) : 0;
            return $timeB - $timeA;
        });

        // ディスク使用状況を取得（ファイル一覧取得を最適化）
        $recordingsPath = storage_path('app/recordings');
        $diskUsage = null;

        if (is_dir($recordingsPath)) {
            $totalSize = 0;
            $iterator = new \FilesystemIterator($recordingsPath, \FilesystemIterator::SKIP_DOTS);

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $totalSize += $file->getSize();
                }
            }

            $diskFree = disk_free_space($recordingsPath);
            $diskTotal = disk_total_space($recordingsPath);

            $diskUsage = [
                'used' => $this->formatFileSize($totalSize),
                'total' => $this->formatFileSize($diskTotal),
                'percentage' => $diskTotal > 0 ? round(($totalSize / $diskTotal) * 100, 2) : 0
            ];
        }

        return view('recording.history', compact('recordings', 'diskUsage'));
    }

    // 録音ファイル削除
    public function deleteRecording(Request $request): JsonResponse
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

            // ファイルを削除
            if (file_exists($recordingInfo['filepath'])) {
                unlink($recordingInfo['filepath']);
            }

            // キャッシュから削除
            Cache::forget("recording_{$recordingId}");

            return response()->json([
                'success' => true,
                'message' => '録音ファイルを削除しました'
            ]);

        } catch (\Exception $e) {
            \Log::error('録音ファイル削除エラー', ['error' => $e->getMessage(), 'filename' => $filename]);
            throw new RecordingException('録音ファイルの削除に失敗しました', 0, $e);
        }
    }

    // 放送局IDから主エリアID（本社所在地）を取得
    private function getAreaIdFromStationId(string $stationId): string
    {
        if (isset(self::STATION_AREA_MAP[$stationId])) {
            return self::STATION_AREA_MAP[$stationId];
        }

        \Log::warning('放送局IDからエリアIDを判定できませんでした', ['station_id' => $stationId]);
        return 'JP13'; // デフォルト: 東京
    }
}
