<?php

namespace App\Http\Controllers;

use App\RadioProgram;
use App\Services\RadikoApiService;
use Illuminate\Http\Request;
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
    public function index(Request $request, $station_id, $title)
    {
        // 番組詳細を1時間キャッシュ
        $cacheKey = 'program_detail_' . $station_id . '_' . md5($title);

        $result = Cache::remember($cacheKey, 3600, function () use ($station_id, $title) {
            $allEntries = [];

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

                $stationIdFromApi = null;
                foreach ($xpath->query('//radiko/stations/station/progs/prog') as $node) {
                    if ($title === $xpath->evaluate('string(title)', $node)) {
                        $ft = $xpath->evaluate('string(@ft)', $node); // e.g. 20260312230000
                        $entryDate = strlen($ft) >= 8 ? substr($ft, 0, 8) : null;

                        $allEntries[] = [
                            'id'    => $xpath->evaluate('string(../../@id)', $node),
                            'title' => $xpath->evaluate('string(title)', $node),
                            'cast'  => $xpath->evaluate('string(pfm)', $node),
                            'image' => $xpath->evaluate('string(img)', $node),
                            'desc'  => $xpath->evaluate('string(desc)', $node),
                            'info'  => $xpath->evaluate('string(info)', $node),
                            'date'  => $entryDate,
                        ];

                        if (!$stationIdFromApi) {
                            $stationIdFromApi = $xpath->evaluate('string(../../@id)', $node);
                        }
                    }
                }

                if (!empty($allEntries)) {
                    $program_id = RadioProgram::where('station_id', $stationIdFromApi)
                        ->where('title', $title)
                        ->value('id');
                    return ['type' => 'entries', 'data' => $allEntries, 'program_id' => $program_id];
                }
            } catch (\Exception $e) {
                error_log("Program detail fetch error for station {$station_id}: " . $e->getMessage());
            }

            $results = RadioProgram::where('title', $title)
                ->select('title', 'cast', 'info', 'image', 'id', 'station_id')
                ->limit(1)
                ->get();
            return ['type' => 'results', 'data' => $results];
        });

        // URLパラメータから曜日を決定
        // 優先順位: date (Ymd) > broadcast_day (0-6直接指定)
        $targetDayOfWeek = null;
        $dateParam = $request->get('date');
        if ($dateParam && preg_match('/^\d{8}$/', $dateParam)) {
            $targetDayOfWeek = Carbon::createFromFormat('Ymd', $dateParam)->isoWeekday() - 1;
        } elseif ($request->get('broadcast_day') !== null && $request->get('broadcast_day') !== '') {
            $targetDayOfWeek = (int) $request->get('broadcast_day');
        }


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
                    $isInTimefreeWindow = $programEndTime->isPast() && $programEndTime->isAfter($timefreeLimitDate);

                    // dateパラメータで特定日付が指定されている場合はその日付を優先
                    if ($dateParam && $entry['date'] === $dateParam) {
                        if ($isInTimefreeWindow) {
                            // 指定日付がタイムフリー期間内 → そのまま確定
                            $latestBroadcast = $entry;
                            break;
                        }
                        // 指定日付がまだ終了していない（未来・放送中）→ 同じ曜日の直近放送へフォールバック
                        continue;
                    }

                    // dateパラメータなし or 指定日がウィンドウ外の場合: 曜日フィルタで直近を探す
                    if ($targetDayOfWeek !== null) {
                        $entryDayOfWeek = Carbon::createFromFormat('Ymd', $entry['date'])->isoWeekday() - 1;
                        if ($entryDayOfWeek !== $targetDayOfWeek) {
                            continue;
                        }
                    }

                    if ($isInTimefreeWindow) {
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

        // broadcast_day: dateパラメータ優先、なければlatestBroadcastの日付から算出
        $broadcast_day = $targetDayOfWeek;
        if ($broadcast_day === null && $latestBroadcast) {
            $broadcast_day = Carbon::createFromFormat('Ymd', $latestBroadcast['date'])->isoWeekday() - 1;
        }

        if ($result['type'] === 'entries') {
            $allEntries = $result['data'];
            $program_id = $result['program_id'];

            // dateパラメータに合うエントリを選択。なければ最初のエントリを使う
            $entry = $allEntries[0] ?? null;
            if ($dateParam && count($allEntries) > 1) {
                foreach ($allEntries as $e) {
                    if (($e['date'] ?? null) === $dateParam) {
                        $entry = $e;
                        break;
                    }
                }
                // 完全一致なければ曜日で探す
                if (($entry['date'] ?? null) !== $dateParam && $targetDayOfWeek !== null) {
                    foreach ($allEntries as $e) {
                        if (isset($e['date']) && Carbon::createFromFormat('Ymd', $e['date'])->isoWeekday() - 1 === $targetDayOfWeek) {
                            $entry = $e;
                            break;
                        }
                    }
                }
            }

            $entries = $entry ? [$entry] : $allEntries;
            return view('radioprogram.detail', compact('entries', 'program_id', 'latestBroadcast', 'broadcast_day'));
        } else {
            $results = $result['data'];
            // DBから取得した場合もprogram_idを渡す
            $program_id = $results->isNotEmpty() ? $results->first()->id : null;
            return view('radioprogram.detail', compact('results', 'program_id', 'latestBroadcast', 'broadcast_day'));
        }
    }
}
