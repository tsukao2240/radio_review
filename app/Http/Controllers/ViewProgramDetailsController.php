<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\RadioProgram;
use Illuminate\Support\Facades\DB;
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

                    $program = RadioProgram::where('title', $title)->select('id')->first();
                    $program_id = $program->id;
                    
                    return ['type' => 'entries', 'data' => $entries, 'program_id' => $program_id];
                }
            }
            
            if (empty($entries)) {
                $results = RadioProgram::where('title', $title)
                    ->select('title', 'cast', 'info', 'image', 'id', 'station_id')
                    ->get();
                return ['type' => 'results', 'data' => $results];
            }
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
