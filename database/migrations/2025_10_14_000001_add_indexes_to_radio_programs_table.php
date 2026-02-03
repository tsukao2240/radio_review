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
            // タイムスタンプを追加（データ管理のため）
            // インデックスは2025_10_01_233230_add_performance_indexes_to_tables.phpで既に追加済み
            if (!Schema::hasColumn('radio_programs', 'created_at')) {
                $table->timestamps();
            }
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
            if (Schema::hasColumn('radio_programs', 'created_at')) {
                $table->dropTimestamps();
            }
        });
    }
}
