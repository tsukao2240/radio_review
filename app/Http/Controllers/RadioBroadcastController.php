<?php

namespace App\Http\Controllers;

use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;
use App\RadioProgram;
use Carbon\Carbon;
use DateInterval;
use DateTime as GlobalDateTime;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;

class RadioBroadcastController extends Controller
{
    //放送局ごとの番組表の情報を取得する（パフォーマンス改善版）
    public function getWeeklySchedule($id)
    {
        // キャッシュキーを生成（30分間隔でキャッシュ）
        $cacheKey = 'weekly_schedule_' . $id . '_' . floor(time() / 1800);

        $data = Cache::remember($cacheKey, 1800, function () use ($id) {
            return $this->fetchWeeklyScheduleFromAPI($id);
        });

        if (!$data) {
            // キャッシュに失敗した場合のフォールバック
            $data = [
                'entries' => [],
                'thisWeek' => [],
                'broadcast_name' => '番組表取得エラー'
            ];
        }

        return view('radioprogram.weekly_schedule', $data);
    }

    private function fetchWeeklyScheduleFromAPI($id)
    {
        try {
            $date = new GlobalDateTime();
            $today = $this->getToday($date);

            $client = new Client([
                'timeout' => 10,
                'connect_timeout' => 3
            ]);

            $url = 'http://radiko.jp/v3/program/station/weekly/' . $id . '.xml';
            $response = $client->get($url);

            $dom = new DOMDocument();
            @$dom->loadXML($response->getBody()->getContents());
            $xpath = new DOMXPath($dom);

            $entries = [];
            $thisWeek = [];
            $broadcast_name = '';

            foreach ($xpath->query('//radiko/stations/station/progs/prog') as $node) {
                $progDate = substr($xpath->evaluate('string(./@ft)', $node), 0, 8);

                if (intval($today) <= intval($progDate)) {
                    $startTime = substr_replace($xpath->evaluate('string(./@ftl)', $node), ':', 2, 0);

                    // 24時以降の番組を事前にフィルタリング
                    if ($progDate === $today && intval(substr($startTime, 0, 2)) >= 24) {
                        continue;
                    }

                    $entries[] = [
                        'id' => $xpath->evaluate('string(../../@id)', $node),
                        'date' => $progDate,
                        'title' => $xpath->evaluate('string(title)', $node),
                        'cast' => $xpath->evaluate('string(pfm)', $node),
                        'start' => $startTime,
                        'end' => substr_replace($xpath->evaluate('string(./@tol)', $node), ':', 2, 0),
                    ];

                    $thisWeek[] = $progDate;

                    if (empty($broadcast_name)) {
                        $broadcast_name = $xpath->evaluate('string(../.././name)', $node);
                    }
                }
            }

            // array_unique を使用してより効率的に重複削除
            $thisWeek = array_values(array_unique($thisWeek));

            return [
                'entries' => $entries,
                'thisWeek' => $thisWeek,
                'broadcast_name' => $broadcast_name
            ];

        } catch (\Exception $e) {
            // エラーログを記録
            error_log("Weekly schedule fetch error for station {$id}: " . $e->getMessage());
            return null;
        }
    }

    private function getToday(GlobalDateTime $date)
    {
        //日付の切り替えを午前5時に行うために一旦時間までを取得し、判別を行ったあとにYmdの形式に戻している
        $today = $date->format('YmdH');
        if (substr($today, 8, 10) < 5) {
            return $date->modify('-1 days')->format('Ymd');
        } else {
            return substr($today, 0, 8);
        }
    }
}
