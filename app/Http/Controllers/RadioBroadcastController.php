<?php

namespace App\Http\Controllers;

use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;
use App\RadioProgram;
use Carbon\Carbon;
use DateInterval;
use DateTime as GlobalDateTime;

class RadioBroadcastController extends Controller
{
    //放送局ごとの番組表の情報を取得します
    public function getBroadCastId($id)
    {
        $entries = [];
        $date = new GlobalDateTime();
        //日付の切り替えを午前5時に行うために一旦時間までを取得し、判別を行ったあとにYmdの形式に戻している
        $today = $date->format('YmdH');
        if (substr($today, 9, 10) < 5) {
            $today = $today - 1;
            $today = substr($today, 0, 8);
        } else {
            $today = substr($today, 0, 8);
        }
        $url = 'http://radiko.jp/v3/program/station/weekly/' . $id . '.xml';
        $dom = new DOMDocument();
        @$dom->load($url);
        $xpath = new DOMXPath($dom);

        foreach ($xpath->query('//radiko/stations/station/progs/prog') as $node) {

            if (intval($today) <= substr($xpath->evaluate('string(./@ft)', $node), 0, 8)) {
                $entries[] = array(

                    'id' => $xpath->evaluate('string(../../@id)', $node),
                    'date' => substr($xpath->evaluate('string(./@ft)', $node), 0, 8),
                    'title' => $xpath->evaluate('string(title)', $node),
                    'cast' => $xpath->evaluate('string(pfm)', $node),
                    'start' => substr_replace(($xpath->evaluate('string(./@ftl)', $node)), ':', 2, 0),
                    'end' => substr_replace(($xpath->evaluate('string(./@tol)', $node)), ':', 2, 0),

                );
                $thisWeek[] = substr($xpath->evaluate('string(./@ft)', $node), 0, 8);
                $broadcast_name = $xpath->evaluate('string(../.././name)', $node);
            }
        }
        $thisWeek = array_unique($thisWeek, SORT_REGULAR);
        $thisWeek = array_merge($thisWeek);

        $count = 0;
        foreach ($entries as $entry) {
            //24時以降の番組の処理
            if ($entry['date'] === $today && $entry['start'] >= 24) {

                unset($entries[$count]);
            }
            $count++;
        }
        $entries = array_merge($entries);
        return view('radioprogram.weekly_schedule', compact(['entries', 'thisWeek', 'broadcast_name']));
    }

    public function getBroadcastName()
    {
        $station_id = RadioProgram::select('station_id')->distinct()->pluck();
    }
}
