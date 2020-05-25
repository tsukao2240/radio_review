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

                $program = DB::table('radio_programs')->where('title', $title)->select('id')->first();
                $program_id = $program->id;
                return view('radioprogram.detail', compact('entries', 'program_id'));
            }
        }
        if (empty($entries)) {
            $results = DB::table('radio_programs')->where('title', $title)->select('title', 'cast', 'info', 'image', 'id', 'station_id')->get();
        }
        return view('radioprogram.detail', compact('results'));
    }
}
