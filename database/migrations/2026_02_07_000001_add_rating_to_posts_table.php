<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddRatingToPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->decimal('rating', 2, 1)->nullable()->after('body')->comment('評価（1.0-5.0）');
            $table->index('rating');
        });

        // 既存の投稿にデフォルト評価3.0を設定
        DB::table('posts')->whereNull('rating')->update(['rating' => 3.0]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['rating']);
            $table->dropColumn('rating');
        });
    }
}
