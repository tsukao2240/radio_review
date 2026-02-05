<?php

namespace App\Http\Controllers;

use App\RadioProgram;
use App\Services\RadikoApiService;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use DOMDocument;
use DOMXPath;

class ViewProgramDetailsController extends Controller
{
    protected $radikoApiService;

    public function __construct(RadikoApiService $radikoApiService)
    {
        $this->radikoApiService = $radikoApiService;
    }
    //番組の詳細情報を取得する。
    public function index($station_id, $title)
    {
        // 番組詳細を1時間キャッシュ
        $cacheKey = 'program_detail_' . $station_id . '_' . md5($title);

        $result = Cache::remember($cacheKey, 3600, function () use ($station_id, $title) {
            $entries = [];

            try {
                $client = new \GuzzleHttp\Client([
                    'timeout' => 10,
                    'connect_timeout' => 3
                ]);

                $url = 'http://radiko.jp/v3/program/station/weekly/' . $station_id . '.xml';
                $response = $client->get($url);
                $xmlContent = $response->getBody()->getContents();

                $dom = new DOMDocument();
                @$dom->loadXML($xmlContent);
                $xpath = new DOMXPath($dom);

                //番組情報をAPIから取得する。
                //APIから取得できない場合はDBからデータを取得する。
                foreach ($xpath->query('//radiko/stations/station/progs/prog') as $node) {

                    if ($title === $xpath->evaluate('string(title)', $node)) {

                        $entries[] = array(

                            'id' => $xpath->evaluate('string(../../@id)', $node),
                            'title' => $xpath->evaluate('string(title)', $node),
                            'cast' => $xpath->evaluate('string(pfm)', $node),
                            'image' => $xpath->evaluate('string(img)', $node),
                            'desc' => $xpath->evaluate('string(desc)', $node),
                            'info' => $xpath->evaluate('string(info)', $node)

                        );

                        // N+1問題を回避: ループの外でクエリを実行するために、
                        // 最初の一致で取得して返す
                        $program_id = RadioProgram::where('title', $title)->value('id');

                        return ['type' => 'entries', 'data' => $entries, 'program_id' => $program_id];
                    }
                }
            } catch (\Exception $e) {
                // API取得エラー時はログを記録してDBフォールバック
                error_log("Program detail fetch error for station {$station_id}: " . $e->getMessage());
            }

            if (empty($entries)) {
                $results = RadioProgram::where('title', $title)
                    ->select('title', 'cast', 'info', 'image', 'id', 'station_id')
                    ->limit(1)
                    ->get();
                return ['type' => 'results', 'data' => $results];
            }
            
            return ['type' => 'entries', 'data' => [], 'program_id' => null];
        });

        // 直近のタイムフリー放送情報を取得
        $latestBroadcast = null;
        try {
            $schedule = $this->radikoApiService->getTwoWeekSchedule($station_id);
            $now = Carbon::now();
            $timefreeLimitDate = $now->copy()->subDays(7);
            $latestEndTime = null;

            foreach ($schedule['entries'] as $entry) {
                if ($entry['title'] === $title) {
                    $programEndTime = Carbon::createFromFormat('Ymd H:i', $entry['date'] . ' ' . $entry['end']);

                    if ($programEndTime->isPast() && $programEndTime->isAfter($timefreeLimitDate)) {
                        if ($latestEndTime === null || $programEndTime->isAfter($latestEndTime)) {
                            $latestBroadcast = $entry;
                            $latestEndTime = $programEndTime;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('番組詳細の放送情報取得エラー', [
                'station_id' => $station_id,
                'title' => $title,
                'error' => $e->getMessage()
            ]);
        }

        if ($result['type'] === 'entries') {
            $entries = $result['data'];
            $program_id = $result['program_id'];
            return view('radioprogram.detail', compact('entries', 'program_id', 'latestBroadcast'));
        } else {
            $results = $result['data'];
            return view('radioprogram.detail', compact('results', 'latestBroadcast'));
        }
    }
}
