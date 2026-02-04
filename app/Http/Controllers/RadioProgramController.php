<?php

namespace App\Http\Controllers;

use App\RadioProgram;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request as GuzzleRequest;

class RadioProgramController extends Controller
{
    //radikoのAPIから今放送されている番組を取得する（パフォーマンス改善版）
    public function fetchRecentProgram()
    {
        // キャッシュキーを生成（5分間隔でキャッシュ）
        $cacheKey = 'recent_programs_' . floor(time() / 300);

        $results = Cache::remember($cacheKey, 300, function () {
            return $this->fetchProgramsFromAPI();
        });

        return view('radioprogram.recent_schedule', compact('results'));
    }

    private function fetchProgramsFromAPI()
    {
        $client = new Client([
            'timeout' => 5,  // タイムアウトを5秒に設定
            'connect_timeout' => 2
        ]);

        $requests = [];
        $stationData = [];

        // 非同期リクエストを準備
        for ($i = 1; $i < 48; ++$i) {
            $url = 'http://radiko.jp/v3/program/now/JP' . $i . '.xml';
            $requests[] = new GuzzleRequest('GET', $url);
        }

        // 並列処理でAPIを呼び出し
        $pool = new Pool($client, $requests, [
            'concurrency' => 10, // 同時接続数を10に制限
            'fulfilled' => function ($response, $index) use (&$stationData) {
                $this->processResponse($response->getBody()->getContents(), $stationData);
            },
            'rejected' => function ($reason, $index) {
                // エラーログを記録（本来はLaravelのLogファサードを使用）
                error_log("Failed to fetch data for JP" . ($index + 1) . ": " . $reason->getMessage());
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();

        // 重複除去を効率化
        $uniqueStations = [];
        $results = [];

        foreach ($stationData as $data) {
            if (!isset($uniqueStations[$data['station']])) {
                $uniqueStations[$data['station']] = true;
                $results[] = $data;
            }
        }

        return $results;
    }

    private function processResponse($xmlContent, &$stationData)
    {
        try {
            $dom = new DOMDocument();
            @$dom->loadXML($xmlContent);
            $xpath = new DOMXPath($dom);

            foreach ($xpath->query('//radiko/stations/station') as $node) {
                $stationData[] = [
                    'station_id' => $xpath->evaluate('string(./@id)', $node),
                    'station' => $xpath->evaluate('string(name)', $node),
                    'title' => $xpath->evaluate('string(progs/prog/title)', $node),
                    'cast' => $xpath->evaluate('string(progs/prog/pfm)', $node),
                    'start' => substr_replace($xpath->evaluate('string(.//prog/@ftl)', $node), ':', 2, 0),
                    'end' => substr_replace($xpath->evaluate('string(.//prog/@tol)', $node), ':', 2, 0),
                    'url' => $xpath->evaluate('string(progs/prog/url)', $node),
                ];
            }
        } catch (\Exception $e) {
            // XMLパースエラーをログに記録
            error_log("XML parsing error: " . $e->getMessage());
        }
    }
}
