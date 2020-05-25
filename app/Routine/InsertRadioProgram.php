<?php

namespace App\Routine;

use Illuminate\Support\Facades\DB;
use DOMDocument;
use DOMXPath;

class InsertRadioProgram
{
    //1週間分の放送番組の情報を取得し、DBに格納する。

    public function __invoke()
    {
        $entries = [];
        $brodCastIds = $this->getBroadCastId();
        $arr_count = count($brodCastIds);

        for ($i = 0; $i < $arr_count; $i++) {

            $url = 'http://radiko.jp/v3/program/station/weekly/' . implode($brodCastIds[$i]) . '.xml';
            $dom = new DOMDocument();
            @$dom->load($url);
            $xpath = new DOMXPath($dom);

            foreach ($xpath->query('//radiko/stations/station/progs/prog') as $node) {

                $entries[] = array(
                    'station_id' => implode($brodCastIds[$i]),
                    'title' => $xpath->evaluate('string(title)', $node),
                    'cast' => $xpath->evaluate('string(pfm)', $node),
                    'start' => substr_replace(($xpath->evaluate('string(./@ftl)', $node)), ':', 2, 0),
                    'end' => substr_replace(($xpath->evaluate('string(./@tol)', $node)), ':', 2, 0),
                    'info' => $xpath->evaluate('string(info)', $node),
                    'url' => $xpath->evaluate('string(url)', $node),
                    'image' => $xpath->evaluate('string(img)', $node),
                );
            }
        }
        $res = array_unique($entries, SORT_REGULAR);

        //データが多いためSQLエラーが発生するため、1000件ずつに分けてDBに格納している。
        $collection = collect($res);
        $data = $collection->chunk(1000);
        DB::beginTransaction();
        try {
            foreach ($data as $value) {

                DB::table('radio_programs')
                    ->insert(
                        $value->toarray()
                    );

                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            echo $e;
        }
    }
    public function getBroadCastId()
    {

        $dom = new DOMDocument;
        $broadcast = [];
        $url = 'http://radiko.jp/v3/station/region/full.xml';

        @$dom->load($url);
        $xpath = new DOMXPath($dom);

        foreach ($xpath->query('//region/stations/station') as $node) {

            $broadcast[] = array(
                $xpath->evaluate('string(id)', $node),

            );
        }

        $res = array_unique($broadcast, SORT_REGULAR);
        return $res;
    }
}
