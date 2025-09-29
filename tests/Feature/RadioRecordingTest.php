<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class RadioRecordingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // テスト用のrecordingsディレクトリを作成
        Storage::fake('recordings');
    }

    /**
     * タイムフリー録音開始のテスト - 正常系
     */
    public function test_start_timefree_recording_success()
    {
        $requestData = [
            'station_id' => 'TBS',
            'title' => 'テスト番組',
            'start_time' => '202509292200',
            'end_time' => '202509292230'
        ];

        // 認証をモック
        $this->mockRadikoAuth();

        $response = $this->postJson('/recording/timefree/start', $requestData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'タイムフリー録音を開始しました'
                ])
                ->assertJsonStructure([
                    'success',
                    'message',
                    'recording_id',
                    'filename'
                ]);

        // キャッシュに録音情報が保存されているかチェック
        $recordingId = $response->json('recording_id');
        $this->assertTrue(Cache::has("recording_{$recordingId}"));
    }

    /**
     * タイムフリー録音開始のテスト - 必須パラメータ不足
     */
    public function test_start_timefree_recording_missing_parameters()
    {
        $response = $this->postJson('/recording/timefree/start', []);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => false,
                    'message' => '放送局ID、開始時間、終了時間が必要です'
                ]);
    }

    /**
     * タイムフリー録音開始のテスト - タイムフリー期間外
     */
    public function test_start_timefree_recording_out_of_timefree_period()
    {
        // 2週間前の番組を指定
        $twoWeeksAgo = Carbon::now()->subWeeks(2);

        $requestData = [
            'station_id' => 'TBS',
            'title' => 'テスト番組',
            'start_time' => $twoWeeksAgo->format('YmdHi'),
            'end_time' => $twoWeeksAgo->addMinutes(30)->format('YmdHi')
        ];

        $response = $this->postJson('/recording/timefree/start', $requestData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => false,
                    'message' => 'タイムフリー期間（1週間）を過ぎています'
                ]);
    }

    /**
     * 録音状態確認のテスト - 正常系
     */
    public function test_get_recording_status_success()
    {
        // テスト用録音情報をキャッシュに保存
        $recordingId = 'TBS_202509292200_20250929220000';
        $recordingInfo = [
            'station_id' => 'TBS',
            'title' => 'テスト番組',
            'filename' => 'test.m4a',
            'filepath' => '/path/to/test.m4a',
            'start_time' => '202509292200',
            'end_time' => '202509292230',
            'created_at' => Carbon::now()->toISOString(),
            'status' => 'recording'
        ];

        Cache::put("recording_{$recordingId}", $recordingInfo, 7200);

        $response = $this->getJson("/recording/status?recording_id={$recordingId}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ])
                ->assertJsonStructure([
                    'success',
                    'recording_info',
                    'file_exists',
                    'file_size'
                ]);
    }

    /**
     * 録音状態確認のテスト - 録音IDなし
     */
    public function test_get_recording_status_missing_id()
    {
        $response = $this->getJson('/recording/status');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => false,
                    'message' => '録音IDが必要です'
                ]);
    }

    /**
     * 録音状態確認のテスト - 録音情報が見つからない
     */
    public function test_get_recording_status_not_found()
    {
        $response = $this->getJson('/recording/status?recording_id=invalid_id');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => false,
                    'message' => '録音情報が見つかりません'
                ]);
    }

    /**
     * 録音停止のテスト - 正常系
     */
    public function test_stop_recording_success()
    {
        // テスト用録音情報をキャッシュに保存
        $recordingId = 'TBS_202509292200_20250929220000';
        $recordingInfo = [
            'station_id' => 'TBS',
            'title' => 'テスト番組',
            'filename' => 'test.m4a',
            'filepath' => '/path/to/test.m4a',
            'start_time' => '202509292200',
            'end_time' => '202509292230',
            'created_at' => Carbon::now()->toISOString(),
            'status' => 'recording'
        ];

        Cache::put("recording_{$recordingId}", $recordingInfo, 7200);

        $response = $this->postJson('/recording/stop', [
            'recording_id' => $recordingId
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'タイムフリー録音を停止しました'
                ]);

        // ステータスが更新されているかチェック
        $updatedInfo = Cache::get("recording_{$recordingId}");
        $this->assertEquals('stopped', $updatedInfo['status']);
    }

    /**
     * 録音一覧取得のテスト
     */
    public function test_list_recordings()
    {
        // テスト用録音情報を複数キャッシュに保存
        $recordings = [
            'TBS_202509292200_20250929220000' => [
                'station_id' => 'TBS',
                'title' => 'テスト番組1',
                'filename' => 'test1.m4a',
                'created_at' => Carbon::now()->subHours(2)->toISOString(),
                'status' => 'completed'
            ],
            'QRR_202509292100_20250929210000' => [
                'station_id' => 'QRR',
                'title' => 'テスト番組2',
                'filename' => 'test2.m4a',
                'created_at' => Carbon::now()->subHour()->toISOString(),
                'status' => 'recording'
            ]
        ];

        foreach ($recordings as $id => $info) {
            Cache::put("recording_{$id}", $info, 7200);
        }

        $response = $this->getJson('/recording/list');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ])
                ->assertJsonStructure([
                    'success',
                    'recordings' => [
                        '*' => [
                            'recording_id',
                            'station_id',
                            'title',
                            'filename',
                            'created_at',
                            'status'
                        ]
                    ]
                ]);
    }

    /**
     * ダウンロード機能のテスト - 正常系
     */
    public function test_download_recording_success()
    {
        // テスト用録音ファイルを作成
        $filename = 'test_recording.m4a';
        $filepath = storage_path("app/recordings/{$filename}");

        // ディレクトリを作成
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        // ダミーファイルを作成
        file_put_contents($filepath, 'テスト用音声データ');

        // 録音情報をキャッシュに保存
        $recordingId = 'TBS_202509292200_20250929220000';
        $recordingInfo = [
            'station_id' => 'TBS',
            'title' => 'テスト番組',
            'filename' => $filename,
            'filepath' => $filepath,
            'status' => 'completed'
        ];

        Cache::put("recording_{$recordingId}", $recordingInfo, 7200);

        $response = $this->get("/recording/download?recording_id={$recordingId}");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/octet-stream');

        // テストファイルを削除
        if (file_exists($filepath)) {
            unlink($filepath);
        }
    }

    /**
     * ダウンロード機能のテスト - ファイルが存在しない
     */
    public function test_download_recording_file_not_found()
    {
        // 存在しないファイルの録音情報をキャッシュに保存
        $recordingId = 'TBS_202509292200_20250929220000';
        $recordingInfo = [
            'station_id' => 'TBS',
            'title' => 'テスト番組',
            'filename' => 'nonexistent.m4a',
            'filepath' => '/nonexistent/path/nonexistent.m4a',
            'status' => 'completed'
        ];

        Cache::put("recording_{$recordingId}", $recordingInfo, 7200);

        $response = $this->get("/recording/download?recording_id={$recordingId}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => false,
                    'message' => 'ファイルが見つかりません'
                ]);
    }

    /**
     * radiko認証をモックする
     */
    private function mockRadikoAuth()
    {
        // HTTP clientをモック
        $this->app->bind('GuzzleHttp\Client', function () {
            $mock = \Mockery::mock('GuzzleHttp\Client');

            // auth1のレスポンス
            $auth1Response = \Mockery::mock('Psr\Http\Message\ResponseInterface');
            $auth1Response->shouldReceive('getHeaderLine')
                ->with('X-Radiko-AuthToken')->andReturn('mock_auth_token');
            $auth1Response->shouldReceive('getHeaderLine')
                ->with('X-Radiko-KeyLength')->andReturn('16');
            $auth1Response->shouldReceive('getHeaderLine')
                ->with('X-Radiko-KeyOffset')->andReturn('0');

            // auth2のレスポンス
            $auth2Response = \Mockery::mock('Psr\Http\Message\ResponseInterface');
            $auth2Response->shouldReceive('getStatusCode')->andReturn(200);

            $mock->shouldReceive('post')
                ->with('https://radiko.jp/v2/api/auth1', \Mockery::any())
                ->andReturn($auth1Response);

            $mock->shouldReceive('post')
                ->with('https://radiko.jp/v2/api/auth2', \Mockery::any())
                ->andReturn($auth2Response);

            return $mock;
        });

        // file_get_contentsをモック
        if (!function_exists('file_get_contents_original')) {
            function file_get_contents_original($filename, $use_include_path = false, $context = null, $offset = 0, $length = null) {
                return \file_get_contents($filename, $use_include_path, $context, $offset, $length);
            }
        }
    }
}