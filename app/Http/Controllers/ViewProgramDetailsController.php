<?php

namespace App\Http\Controllers;

use App\RadioProgram;
use Illuminate\Support\Facades\Cache;
use DOMDocument;
use DOMXPath;

class ViewProgramDetailsController extends Controller
{
    //番組の詳細情報を取得する。
    public function index($station_id, $title)
    {
        // 番組詳細を1時間キャッシュ
        $cacheKey = 'program_detail_' . $station_id . '_' . md5($title);

        $result = Cache::remember($cacheKey, 3600, function () use ($station_id, $title) {
            $entries = [];

            $url = 'http://radiko.jp/v3/program/station/weekly/' . $station_id . '.xml';
            $dom = new DOMDocument();
            @$dom->load($url);
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
                    $program = RadioProgram::where('title', $title)->select('id')->first();
                    $program_id = $program ? $program->id : null;

                    return ['type' => 'entries', 'data' => $entries, 'program_id' => $program_id];
                }
            }

            if (empty($entries)) {
                $results = RadioProgram::where('title', $title)
                    ->select('title', 'cast', 'info', 'image', 'id', 'station_id')
                    ->get();
                return ['type' => 'results', 'data' => $results];
            }
            
            return ['type' => 'entries', 'data' => [], 'program_id' => null];
        });

        if ($result['type'] === 'entries') {
            $entries = $result['data'];
            $program_id = $result['program_id'];
            return view('radioprogram.detail', compact('entries', 'program_id'));
        } else {
            $results = $result['data'];
            return view('radioprogram.detail', compact('results'));
        }
    }
}
