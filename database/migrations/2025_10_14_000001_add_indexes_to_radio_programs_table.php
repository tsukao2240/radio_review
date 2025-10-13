<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToRadioProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('radio_programs', function (Blueprint $table) {
            // 検索パフォーマンスを向上させるためのインデックス
            $table->index('station_id');
            $table->index('title');
            $table->index('cast');

            // タイムスタンプを追加（データ管理のため）
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('radio_programs', function (Blueprint $table) {
            $table->dropIndex(['station_id']);
            $table->dropIndex(['title']);
            $table->dropIndex(['cast']);
            $table->dropTimestamps();
        });
    }
}
