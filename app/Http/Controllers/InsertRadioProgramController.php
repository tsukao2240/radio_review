<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\RadioProgramController;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use DOMDocument;
use DOMXPath;

class InsertRadioProgramController extends Controller
{
    //1週間分の放送番組の情報を取得し、DBに格納します。
    //Todo
    //1週間分の情報を取得するが、差分があった場合のみDBに保存するようにする。

    public function fetchRadioInfoOneweek()
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
        //DBからデータを取得する
        // $dbData = DB::table('radio_programs')->get();
        // $insertData = [];
        // //番組表に新しい番組が登録された場合に新しいレコードを作成する
        // foreach ($res as $value) {

        //     if (!$dbData->contains('title', $value['title'])) {

        //         $insertData[] = $value;
        //     }
        // }
        // dd($insertData);
        // if (count($insertData) > 0) {

        //     foreach ($insertData as $value) {

        //         DB::table('radio_programs')
        //             ->insertOrIgnore($value->toarray());
        //     }
        // }


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

        // if (count($res) > 1000) {

        //     $collection = collect($res);
        //     $data = $collection->chunk(1000);
        //     dd($data);
        //     // try{
        //     // DB::beginTransaction();

        //     foreach ($data as $value) {

        //         DB::table('radio_programs')
        //             ->insertOrIgnore($value->toarray());

        //     }
        //     DB::commit();
        // }catch(\Throwable $e){
        //     DB::rollBack();
        //     Log::error($e->getMessage());
        //     abort(404,'fail');
        // }

        // } else {

        //     DB::table('radio_programs')
        //         ->insertOrIgnore($value->toarray());

        // }
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
