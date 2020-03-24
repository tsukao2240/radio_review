<?php

namespace App\Http\Controllers;

use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;

class RadioBroadcastCotroller extends Controller
{
    //
    public function getBroadCastId(){

        $dom = new DOMDocument;
        $broadcast = array();
        $url = 'http://radiko.jp/v3/station/region/full.xml';

        @$dom->load($url);
        $xpath = new DOMXPath($dom);

        foreach($xpath->query('//region/stations/station') as $node){

            $broadcast[] = $xpath->evaluate('string(area_id)',$node);

        }
        $broadcast[] = 'JP29';
        $broadcast[] = 'JP32';
        $broadcast[] = 'JP41';

        $res = array_unique($broadcast,SORT_REGULAR);
        natsort($res);

        return $res;
    }
}
