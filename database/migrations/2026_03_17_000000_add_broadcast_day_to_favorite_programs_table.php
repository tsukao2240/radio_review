<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('favorite_programs', function (Blueprint $table) {
            if (!Schema::hasColumn('favorite_programs', 'broadcast_day')) {
                $table->tinyInteger('broadcast_day')->nullable()->after('program_title')->comment('0=月, 1=火, 2=水, 3=木, 4=金, 5=土, 6=日');
            }
        });

        // Drop old unique constraint if it exists, then add new one
        $indexes = collect(\DB::select("SHOW INDEX FROM favorite_programs"))->pluck('Key_name')->unique()->toArray();
        if (in_array('idx_favorite_programs_unique', $indexes)) {
            \DB::statement('ALTER TABLE favorite_programs DROP INDEX idx_favorite_programs_unique');
        }
        if (!in_array('fav_programs_unique', $indexes)) {
            \DB::statement('ALTER TABLE favorite_programs ADD UNIQUE fav_programs_unique (user_id, station_id, program_title, broadcast_day)');
        }
    }

    public function down(): void
    {
        Schema::table('favorite_programs', function (Blueprint $table) {
            $table->dropUnique('fav_programs_unique');
            $table->dropColumn('broadcast_day');
            $table->unique(['user_id', 'station_id', 'program_title']);
        });
    }
};
