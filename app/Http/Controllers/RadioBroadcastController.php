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
        $cacheKey = 'weekly_schedule_' . $id . '_' . floor(time() / 3600);

        $data = Cache::remember($cacheKey, 3600, function () use ($id) {
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

            // エントリを日付と時刻順にソート
            usort($entries, function($a, $b) {
                // まず日付で比較
                $dateCompare = strcmp($a['date'], $b['date']);
                if ($dateCompare !== 0) {
                    return $dateCompare;
                }
                
                // 日付が同じ場合は時刻で比較
                // "HH:MM"形式の文字列として比較
                return strcmp($a['start'], $b['start']);
            });

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

    // 2週間番組表（放送局選択画面）
    public function getTwoWeekSchedule(Request $request)
    {
        $areaId = $request->input('area', 'JP13');

        // 放送局リストを取得
        $stations = $this->fetchStationList($areaId);

        // エリアリスト
        $areas = $this->getAreaList();

        return view('radioprogram.twoweek_select', [
            'stations' => $stations,
            'areas' => $areas,
            'selectedArea' => $areaId
        ]);
    }

    // 2週間番組表（放送局指定）
    public function getTwoWeekScheduleByStation(Request $request, $stationId)
    {
        $areaId = $request->input('area', 'JP13');

        // キャッシュキー
        $cacheKey = 'twoweek_schedule_' . $stationId . '_' . date('YmdH');

        $data = Cache::remember($cacheKey, 1800, function () use ($stationId) {
            return $this->fetchTwoWeekSchedule($stationId);
        });

        if (!$data) {
            $data = [
                'entries' => [],
                'dates' => [],
                'broadcast_name' => '番組表取得エラー',
                'station_id' => $stationId
            ];
        }

        $data['station_id'] = $stationId;
        $data['selectedArea'] = $areaId;

        return view('radioprogram.twoweek_schedule', $data);
    }

    // 2週間分の番組データを取得
    private function fetchTwoWeekSchedule($stationId)
    {
        try {
            $client = new Client([
                'timeout' => 15,
                'connect_timeout' => 5
            ]);

            $entries = [];
            $dates = [];
            $broadcastName = '';

            // 過去7日 + 今日 + 未来7日 = 15日分
            $today = Carbon::now();
            if ($today->hour < 5) {
                $today = $today->subDay();
            }

            for ($i = -7; $i <= 7; $i++) {
                $targetDate = $today->copy()->addDays($i);
                $dateStr = $targetDate->format('Ymd');
                $dates[] = $dateStr;

                // 日付ごとの番組データを取得
                $url = "http://radiko.jp/v3/program/station/date/{$dateStr}/{$stationId}.xml";

                try {
                    $response = $client->get($url);
                    $xml = $response->getBody()->getContents();

                    $dom = new DOMDocument();
                    @$dom->loadXML($xml);
                    $xpath = new DOMXPath($dom);

                    // 放送局名を取得
                    if (empty($broadcastName)) {
                        $broadcastName = $xpath->evaluate('string(//station/name)');
                    }

                    // 番組データを取得
                    foreach ($xpath->query('//prog') as $node) {
                        $ft = $xpath->evaluate('string(./@ft)', $node);
                        $to = $xpath->evaluate('string(./@to)', $node);
                        $ftl = $xpath->evaluate('string(./@ftl)', $node);
                        $tol = $xpath->evaluate('string(./@tol)', $node);

                        $entries[] = [
                            'id' => $stationId,
                            'date' => substr($ft, 0, 8),
                            'title' => $xpath->evaluate('string(title)', $node),
                            'cast' => $xpath->evaluate('string(pfm)', $node),
                            'start' => substr_replace($ftl, ':', 2, 0),
                            'end' => substr_replace($tol, ':', 2, 0),
                            'ft' => $ft,
                            'to' => $to,
                            'img' => $xpath->evaluate('string(img)', $node),
                            'desc' => mb_substr($xpath->evaluate('string(desc)', $node), 0, 100),
                        ];
                    }
                } catch (\Exception $e) {
                    // 特定の日付でエラーが発生しても続行
                    \Log::warning("Failed to fetch schedule for {$stationId} on {$dateStr}: " . $e->getMessage());
                    continue;
                }
            }

            // 時刻順にソート
            usort($entries, function($a, $b) {
                return strcmp($a['ft'], $b['ft']);
            });

            return [
                'entries' => $entries,
                'dates' => $dates,
                'broadcast_name' => $broadcastName
            ];

        } catch (\Exception $e) {
            \Log::error("Two week schedule fetch error: " . $e->getMessage());
            return null;
        }
    }

    // 放送局リストを取得
    private function fetchStationList($areaId)
    {
        $cacheKey = 'station_list_' . $areaId;

        return Cache::remember($cacheKey, 86400, function () use ($areaId) {
            try {
                $client = new Client(['timeout' => 10]);
                $url = "http://radiko.jp/v3/station/list/{$areaId}.xml";
                $response = $client->get($url);

                $dom = new DOMDocument();
                @$dom->loadXML($response->getBody()->getContents());
                $xpath = new DOMXPath($dom);

                $stations = [];
                foreach ($xpath->query('//station') as $node) {
                    $stations[] = [
                        'id' => $xpath->evaluate('string(id)', $node),
                        'name' => $xpath->evaluate('string(name)', $node),
                        'logo' => $xpath->evaluate('string(logo[@width="224"][@align="center"])', $node),
                    ];
                }

                return $stations;
            } catch (\Exception $e) {
                \Log::error("Station list fetch error: " . $e->getMessage());
                return [];
            }
        });
    }

    // エリアリストを取得
    private function getAreaList()
    {
        return [
            'JP1' => '北海道',
            'JP2' => '青森県',
            'JP3' => '岩手県',
            'JP4' => '宮城県',
            'JP5' => '秋田県',
            'JP6' => '山形県',
            'JP7' => '福島県',
            'JP8' => '茨城県',
            'JP9' => '栃木県',
            'JP10' => '群馬県',
            'JP11' => '埼玉県',
            'JP12' => '千葉県',
            'JP13' => '東京都',
            'JP14' => '神奈川県',
            'JP15' => '新潟県',
            'JP16' => '富山県',
            'JP17' => '石川県',
            'JP18' => '福井県',
            'JP19' => '山梨県',
            'JP20' => '長野県',
            'JP21' => '岐阜県',
            'JP22' => '静岡県',
            'JP23' => '愛知県',
            'JP24' => '三重県',
            'JP25' => '滋賀県',
            'JP26' => '京都府',
            'JP27' => '大阪府',
            'JP28' => '兵庫県',
            'JP29' => '奈良県',
            'JP30' => '和歌山県',
            'JP31' => '鳥取県',
            'JP32' => '島根県',
            'JP33' => '岡山県',
            'JP34' => '広島県',
            'JP35' => '山口県',
            'JP36' => '徳島県',
            'JP37' => '香川県',
            'JP38' => '愛媛県',
            'JP39' => '高知県',
            'JP40' => '福岡県',
            'JP41' => '佐賀県',
            'JP42' => '長崎県',
            'JP43' => '熊本県',
            'JP44' => '大分県',
            'JP45' => '宮崎県',
            'JP46' => '鹿児島県',
            'JP47' => '沖縄県',
        ];
    }
}
