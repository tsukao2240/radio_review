<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeleteDuplicateRecordsController extends Controller
{
    //
    public function delete()
    {

        //DBからデータを取得する
        DB::delete('delete from `radio_programs` where id not in
        (select min_id from (select min(t1.id) as min_id from `radio_programs` as t1 group by t1.title) as t2);
        ');
        $dbData = DB::table('radio_programs')->distinct()->pluck('title');
        dd($dbData);

    }
}
