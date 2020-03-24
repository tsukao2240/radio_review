<?php

namespace App\Http\Controllers;

use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;

class RadioProgramController extends Controller
{
    //radikoの番組表から今放送されている番組をとってくる
    public function fetch(){
        $entries = [];
        $dom = new DOMDocument();
        $arr = app()->make('App\Http\Controllers\RadioBroadcastCotroller');
        $prefectureIds = $arr->getBroadCastId();

        foreach($prefectureIds as $prefectureId){

            $url = 'http://radiko.jp/v3/program/now/' . $prefectureId . '.xml';
            @$dom->load($url);
            $xpath = new DOMXPath($dom);

            foreach($xpath->query('//radiko/stations/station') as $node){

                    $entries[] = array(
                        'station' => $xpath->evaluate('string(name)',$node),
                        'title' => $xpath->evaluate('string(progs/prog/title)',$node),
                        'url' => $xpath->evaluate('string(progs/prog/url)',$node),
                        //'info' => $xpath->evaluate('string(progs/prog/info)',$node),
                        'start' => $xpath->evaluate('string(//prog/@ftl)',$node),
                        'end' => $xpath->evaluate('string(//prog/@tol)',$node),
                        //'duration' => $xpath->evaluate('string(//prog/@dur)',$node),
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
