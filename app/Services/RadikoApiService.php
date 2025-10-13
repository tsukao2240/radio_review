<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Radiko APIとの連携を担当するサービスクラス
 */
class RadikoApiService
{
    /**
     * 指定された放送局の週間番組表を取得する
     * キャッシュ有効期限: 30分
     *
     * @param string $stationId
     * @return array
     * @throws \Exception
     */
    public function getWeeklySchedule($stationId)
    {
        $cacheKey = 'radiko_weekly_schedule_' . $stationId;

        return Cache::remember($cacheKey, 1800, function () use ($stationId) {
            try {
                $entries = [];
                $thisWeek = [];
                $broadcastName = '';

                $today = $this->getCurrentDate();
                $url = 'http://radiko.jp/v3/program/station/weekly/' . $stationId . '.xml';

            $dom = new DOMDocument();
            @$dom->load($url);

            if (!$dom->documentElement) {
                throw new \Exception('Failed to load XML from Radiko API');
            }

            $xpath = new DOMXPath($dom);

            foreach ($xpath->query('//radiko/stations/station/progs/prog') as $node) {
                $programDate = substr($xpath->evaluate('string(./@ft)', $node), 0, 8);

                if (intval($today) <= intval($programDate)) {
                    $entries[] = [
                        'id' => $xpath->evaluate('string(../../@id)', $node),
                        'date' => $programDate,
                        'title' => $xpath->evaluate('string(title)', $node),
                        'cast' => $xpath->evaluate('string(pfm)', $node),
                        'start' => substr_replace($xpath->evaluate('string(./@ftl)', $node), ':', 2, 0),
                        'end' => substr_replace($xpath->evaluate('string(./@tol)', $node), ':', 2, 0),
                    ];

                    $thisWeek[] = $programDate;
                    $broadcastName = $xpath->evaluate('string(../.././name)', $node);
                }
            }

            $thisWeek = array_unique($thisWeek, SORT_REGULAR);
            $thisWeek = array_values($thisWeek);

            // 24時以降の番組を除外
            $entries = $this->filterLateNightPrograms($entries, $today);

                Log::info('Weekly schedule retrieved from Radiko API', [
                    'station_id' => $stationId,
                    'programs_count' => count($entries)
                ]);

                return [
                    'entries' => $entries,
                    'thisWeek' => $thisWeek,
                    'broadcast_name' => $broadcastName
                ];

            } catch (\Exception $e) {
                Log::error('Error fetching weekly schedule from Radiko API: ' . $e->getMessage(), [
                    'station_id' => $stationId,
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        });
    }

    /**
     * 番組の詳細情報を取得する
     * キャッシュ有効期限: 30分
     *
     * @param string $stationId
     * @param string $title
     * @return array
     * @throws \Exception
     */
    public function getProgramDetails($stationId, $title)
    {
        $cacheKey = 'radiko_program_details_' . $stationId . '_' . md5($title);

        return Cache::remember($cacheKey, 1800, function () use ($stationId, $title) {
            try {
                $entries = [];
                $url = 'http://radiko.jp/v3/program/station/weekly/' . $stationId . '.xml';

            $dom = new DOMDocument();
            @$dom->load($url);

            if (!$dom->documentElement) {
                throw new \Exception('Failed to load XML from Radiko API');
            }

            $xpath = new DOMXPath($dom);

            foreach ($xpath->query('//radiko/stations/station/progs/prog') as $node) {
                $programTitle = $xpath->evaluate('string(title)', $node);

                if ($title === $programTitle) {
                    $entries[] = [
                        'id' => $xpath->evaluate('string(../../@id)', $node),
                        'title' => $programTitle,
                        'cast' => $xpath->evaluate('string(pfm)', $node),
                        'image' => $xpath->evaluate('string(img)', $node),
                        'desc' => $xpath->evaluate('string(desc)', $node),
                        'info' => $xpath->evaluate('string(info)', $node)
                    ];

                    break; // 最初にマッチしたものを返す
                }
            }

                Log::info('Program details retrieved from Radiko API', [
                    'station_id' => $stationId,
                    'title' => $title,
                    'found' => !empty($entries)
                ]);

                return $entries;

            } catch (\Exception $e) {
                Log::error('Error fetching program details from Radiko API: ' . $e->getMessage(), [
                    'station_id' => $stationId,
                    'title' => $title,
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        });
    }

    /**
     * 現在放送中の番組を取得する
     * キャッシュ有効期限: 5分（番組情報は頻繁に変わるため短く設定）
     *
     * @return array
     * @throws \Exception
     */
    public function getCurrentPrograms()
    {
        $cacheKey = 'radiko_current_programs';

        return Cache::remember($cacheKey, 300, function () {
            try {
                $entries = [];

                // 48は都道府県の数
                for ($i = 1; $i < 48; ++$i) {
                $url = 'http://radiko.jp/v3/program/now/JP' . $i . '.xml';
                $dom = new DOMDocument();
                @$dom->load($url);

                if (!$dom->documentElement) {
                    continue; // エラーが発生しても次の都道府県を処理
                }

                $xpath = new DOMXPath($dom);

                foreach ($xpath->query('//radiko/stations/station') as $node) {
                    $entries[] = [
                        'station_id' => $xpath->evaluate('string(./@id)', $node),
                        'station' => $xpath->evaluate('string(name)', $node),
                        'title' => $xpath->evaluate('string(progs/prog/title)', $node),
                        'cast' => $xpath->evaluate('string(progs/prog/pfm)', $node),
                        'start' => substr_replace($xpath->evaluate('string(//prog/@ftl)', $node), ':', 2, 0),
                        'end' => substr_replace($xpath->evaluate('string(//prog/@tol)', $node), ':', 2, 0),
                        'url' => $xpath->evaluate('string(progs/prog/url)', $node),
                    ];
                }
            }

                // 放送局の重複を削除
                $results = $this->removeDuplicateStations($entries);

                Log::info('Current programs retrieved from Radiko API', [
                    'programs_count' => count($results)
                ]);

                return $results;

            } catch (\Exception $e) {
                Log::error('Error fetching current programs from Radiko API: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        });
    }

    /**
     * 現在の日付を取得（午前5時を基準に日付を判定）
     *
     * @return string
     */
    private function getCurrentDate()
    {
        $date = new \DateTime();
        $today = $date->format('YmdH');

        // 日付の切り替えを午前5時に行う
        if (intval(substr($today, 8, 2)) < 5) {
            $today = $date->modify('-1 days')->format('Ymd');
        } else {
            $today = substr($today, 0, 8);
        }

        return $today;
    }

    /**
     * 24時以降の今日の番組を除外する
     *
     * @param array $entries
     * @param string $today
     * @return array
     */
    private function filterLateNightPrograms($entries, $today)
    {
        return array_values(array_filter($entries, function($entry) use ($today) {
            // 24時以降の番組を除外
            if ($entry['date'] === $today && intval(substr($entry['start'], 0, 2)) >= 24) {
                return false;
            }
            return true;
        }));
    }

    /**
     * 放送局の重複を削除
     *
     * @param array $entries
     * @return array
     */
    private function removeDuplicateStations($entries)
    {
        $arrTmp = [];
        $results = [];

        foreach ($entries as $entry) {
            if (!in_array($entry['station'], $arrTmp)) {
                $arrTmp[] = $entry['station'];
                $results[] = $entry;
            }
        }

        return $results;
    }
}
