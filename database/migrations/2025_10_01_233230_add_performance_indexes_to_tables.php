<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // radio_programsテーブルにインデックスを追加
        Schema::table('radio_programs', function (Blueprint $table) {
            $table->index('title', 'idx_radio_programs_title');
            $table->index('station_id', 'idx_radio_programs_station_id');
            $table->index(['station_id', 'title'], 'idx_radio_programs_station_title');
        });

        // postsテーブルにインデックスを追加
        Schema::table('posts', function (Blueprint $table) {
            $table->index('program_id');
            $table->index('user_id');
            $table->index('created_at');
        });

        // favorite_programsテーブルにインデックスを追加
        Schema::table('favorite_programs', function (Blueprint $table) {
            $table->index('user_id', 'idx_favorite_programs_user_id');
            $table->index(['user_id', 'station_id', 'program_title'], 'idx_favorite_programs_unique');
        });

        // recording_schedulesテーブルにインデックスを追加
        Schema::table('recording_schedules', function (Blueprint $table) {
            $table->index('user_id', 'idx_recording_schedules_user_id');
            $table->index('status', 'idx_recording_schedules_status');
            $table->index('scheduled_start_time', 'idx_recording_schedules_start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // radio_programsテーブルのインデックスを削除
        Schema::table('radio_programs', function (Blueprint $table) {
            $table->dropIndex('idx_radio_programs_title');
            $table->dropIndex('idx_radio_programs_station_id');
            $table->dropIndex('idx_radio_programs_station_title');
        });

        // postsテーブルのインデックスを削除
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['program_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at']);
        });

        // favorite_programsテーブルのインデックスを削除
        Schema::table('favorite_programs', function (Blueprint $table) {
            $table->dropIndex('idx_favorite_programs_user_id');
            $table->dropIndex('idx_favorite_programs_unique');
        });

        // recording_schedulesテーブルのインデックスを削除
        Schema::table('recording_schedules', function (Blueprint $table) {
            $table->dropIndex('idx_recording_schedules_user_id');
            $table->dropIndex('idx_recording_schedules_status');
            $table->dropIndex('idx_recording_schedules_start_time');
        });
    }
};
