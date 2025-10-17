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
            if (!$this->indexExists('radio_programs', 'idx_radio_programs_title')) {
                $table->index('title', 'idx_radio_programs_title');
            }
            if (!$this->indexExists('radio_programs', 'idx_radio_programs_station_id')) {
                $table->index('station_id', 'idx_radio_programs_station_id');
            }
            if (!$this->indexExists('radio_programs', 'idx_radio_programs_station_title')) {
                $table->index(['station_id', 'title'], 'idx_radio_programs_station_title');
            }
        });

        // postsテーブルにインデックスを追加
        // masterブランチの2025_10_14_000002と重複しないように
        // インデックスが存在しない場合のみ追加
        Schema::table('posts', function (Blueprint $table) {
            // 既にインデックスが存在する可能性があるのでチェック
            if (!$this->hasIndexOnColumn('posts', 'program_id')) {
                $table->index('program_id');  // Laravelのデフォルト命名: posts_program_id_index
            }
            if (!$this->hasIndexOnColumn('posts', 'user_id')) {
                $table->index('user_id');  // Laravelのデフォルト命名: posts_user_id_index
            }
            if (!$this->hasIndexOnColumn('posts', 'created_at')) {
                $table->index('created_at');  // Laravelのデフォルト命名: posts_created_at_index
            }
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
            $table->dropIndex(['program_id']);  // posts_program_id_index
            $table->dropIndex(['user_id']);  // posts_user_id_index
            $table->dropIndex(['created_at']);  // posts_created_at_index
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

    /**
     * インデックスが存在するかチェック
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $indexes = \DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }

    /**
     * カラムにインデックスが存在するかチェック
     */
    private function hasIndexOnColumn(string $table, string $column): bool
    {
        $indexes = \DB::select("SHOW INDEX FROM `{$table}` WHERE Column_name = ?", [$column]);
        return count($indexes) > 0;
    }
};
