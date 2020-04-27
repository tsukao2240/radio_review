<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeleteDuplicateRecordsController extends Controller
{
    //
    public function delete()
    {
        //タイトルが重複しているものを削除
        // DB::delete('delete from `radio_programs` where id not in
        // (select min_id from (select min(t1.id) as min_id from `radio_programs` as t1 group by t1.title) as t2);
        // ');
        // $dbData = DB::table('radio_programs')->distinct()->pluck('title');
        DB::beginTransaction();
        try {
            DB::delete('delete from `radio_programs` where id not in
            (select min_id from (select min(t1.id) as min_id from `radio_programs` as t1 group by t1.title) as t2);
            ');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            echo $e;
        }
    }
}
