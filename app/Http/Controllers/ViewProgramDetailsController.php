<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\RadioProgram;
use Illuminate\Support\Facades\DB;
use DOMDocument;
use DOMXPath;

class ViewProgramDetailsController extends Controller
{
    //番組の詳細情報を取得します
    public function index($id, $title)
    {

        $entries = [];

        $url = 'http://radiko.jp/v3/program/station/weekly/' . $id . '.xml';
        $dom = new DOMDocument();
        @$dom->load($url);
        $xpath = new DOMXPath($dom);

        foreach ($xpath->query('//radiko/stations/station/progs/prog') as $node) {

            if ($title === $xpath->evaluate('string(title)', $node)) {

                $entries[] = array(


                    'title' => $xpath->evaluate('string(title)', $node),
                    'cast' => $xpath->evaluate('string(pfm)', $node),
                    'image' => $xpath->evaluate('string(img)', $node),
                    'info' => $xpath->evaluate('string(info)', $node)

                );

                //$results = collect(DB::table('radio_programs'))->firstWhere('title',$title);

                return view('detail.radioPrgramDetail', compact('entries'));
            }
        }
        if (empty($entries)) {

            $results = DB::table('radio_programs')->where('title', $title)->select('title','cast','info','image')->get();

        }
        return view('detail.radioPrgramDetail', compact('results'));
    }
}
