<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PostTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tags = [
            ['name' => '感動した', 'display_order' => 1],
            ['name' => '笑った', 'display_order' => 2],
            ['name' => '勉強になった', 'display_order' => 3],
            ['name' => '考えさせられた', 'display_order' => 4],
            ['name' => '癒された', 'display_order' => 5],
            ['name' => '面白かった', 'display_order' => 6],
            ['name' => 'ためになった', 'display_order' => 7],
        ];

        foreach ($tags as $tag) {
            DB::table('post_tags')->updateOrInsert(
                ['name' => $tag['name']],
                [
                    'display_order' => $tag['display_order'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }
    }
}
