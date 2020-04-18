<?php

namespace App\Http\Controllers;

use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;
use App\RadioProgram;
use DateInterval;
use DateTime as GlobalDateTime;

class RadioBroadcastController extends Controller
{
    //
    public function getBroadCastId($id)
    {

        $entries = [];
        $date = new GlobalDateTime();
        $today = $date->format('Ymd');

        $url = 'http://radiko.jp/v3/program/station/weekly/' . $id . '.xml';
        $dom = new DOMDocument();
        @$dom->load($url);
        $xpath = new DOMXPath($dom);

        foreach ($xpath->query('//radiko/stations/station/progs/prog') as $node) {

            if ($today <= substr($xpath->evaluate('string(./@ft)', $node), 0, 8)) {

                $entries[] = array(

                    'id' => $xpath->evaluate('string(../../@id)', $node),
                    'date' => substr($xpath->evaluate('string(./@ft)', $node), 0, 8),
                    'title' => $xpath->evaluate('string(title)', $node),
                    'cast' => $xpath->evaluate('string(pfm)', $node),
                    'start' => substr_replace(($xpath->evaluate('string(./@ftl)', $node)), ':', 2, 0),
                    'end' => substr_replace(($xpath->evaluate('string(./@tol)', $node)), ':', 2, 0),

                );

                $thisWeek[] = substr($xpath->evaluate('string(./@ft)', $node), 0, 8);

            }

        }
        $thisWeek = array_unique($thisWeek,SORT_REGULAR);
        $thisWeek = array_merge($thisWeek);
        //
        $count = 0;
        foreach($entries as $entry){

            if($entry['date'] === $today && $entry['start'] >= 24){

                unset($entries[$count]);

            }
            $count++;

        }
        $entries = array_merge($entries);
        return view('layouts.weeklySchedule', compact(['entries', 'thisWeek']));
    }
}
