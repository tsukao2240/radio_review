<?php

namespace App\Http\Controllers;

use App\RadioProgram;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;

class RadioProgramController extends Controller
{
    //radikoの番組表から今放送されている番組を取得します
    public function fetchProgramGuide(){

        $entries = [];
        for($i = 1;$i < 48;$i++){
            $url = 'http://radiko.jp/v3/program/now/JP' . $i . '.xml';
            $dom = new DOMDocument();
            @$dom->load($url);
            $xpath = new DOMXPath($dom);

            foreach($xpath->query('//radiko/stations/station') as $node){
                    $entries[] = array(
                        'station' => $xpath->evaluate('string(name)',$node),
                        'title' => $xpath->evaluate('string(progs/prog/title)',$node),
                        'url' => $xpath->evaluate('string(progs/prog/url)',$node),
                        'start' => substr_replace(($xpath->evaluate('string(//prog/@ftl)',$node)),':',2,0),
                        'end' => substr_replace(($xpath->evaluate('string(//prog/@tol)',$node)),':',2,0),
                        );

            }
        }

        //放送局の重複を削除します
        $arr_tmp = $results = array();
        foreach($entries as $entry => $value){
            if(!in_array($value['station'], $arr_tmp)){
                $arr_tmp[] = $value['station'];
                $results[] = $value;
                }
        }
        return view('layouts.home',compact('results'));

    }
}