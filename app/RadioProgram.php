<?php

namespace App;

use App\Http\Controllers\RadioBroadcastCotroller;
use App\Http\Controllers\RadioProgramController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Log\Logger;
use App\Http\Controllers\Log;

class RadioProgram extends Model
{
    //
    protected $fillable =
    [
        'station_id',
        'title',
        'cast',
        'start',
        'end',
        'info',
        'url',
        'image',

    ];

    protected $table = 'radio_programs';

    //1週間分の放送番組の情報を取得し、DBに格納します。
    //Todo
    //1週間分の情報を取得するが、差分があった場合のみDBに保存するようにする。
    //番組タイトルが重複しているものがあるので、それらを統合し開始時間と終了時間をいい感じにする。

    public function fetchRadioInfoOneweek(){
        $entries = [];

        $results = new RadioBroadcastCotroller();
        $brodCastIds = $results->getBroadCastId();
        $arr_count = count($brodCastIds);
        for($i = 0;$i < $arr_count;$i++){
            $url = 'http://radiko.jp/v3/program/station/weekly/'. implode($brodCastIds[$i]) . '.xml';
            $dom = new DOMDocument();
            @$dom->load($url);
            $xpath = new DOMXPath($dom);
            foreach($xpath->query('//radiko/stations/station/progs/prog') as $node){
                $entries[] = array(
                    'station_id' => implode($brodCastIds[$i]),
                    'title' => $xpath->evaluate('string(title)',$node),
                    'cast' => $xpath->evaluate('string(pfm)',$node),
                    'start' => substr_replace(($xpath->evaluate('string(./@ftl)',$node)),':',2,0),
                    'end' => substr_replace(($xpath->evaluate('string(./@tol)',$node)),':',2,0),
                    'info' => $xpath->evaluate('string(info)',$node),
                    'url' => $xpath->evaluate('string(url)',$node),
                    'image' => $xpath->evaluate('string(img)',$node),

                    );
            }
        }
        $res = array_unique($entries,SORT_REGULAR);

        $collection = collect($res);
        $data = $collection->chunk(1000);
        // try{
            // DB::beginTransaction();
            foreach($data as $value){
                DB::table('radio_programs')
                        ->insertOrIgnore($value->toarray());
            }
        //     DB::commit();
        // }catch(\Throwable $e){
        //     DB::rollBack();
        //     Log::error($e->getMessage());
        //     abort(404,'fail');
        // }
        }

}
