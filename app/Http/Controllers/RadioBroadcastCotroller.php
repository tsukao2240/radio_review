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
        $broadcast = [];
        $url = 'http://radiko.jp/v3/station/region/full.xml';

        @$dom->load($url);
        $xpath = new DOMXPath($dom);

        foreach($xpath->query('//region/stations/station') as $node){

            $broadcast[] = array
                            (
                                $xpath->evaluate('string(id)',$node),

                            );

        }

        $res = array_unique($broadcast,SORT_REGULAR);
        return $res;
    }

}
