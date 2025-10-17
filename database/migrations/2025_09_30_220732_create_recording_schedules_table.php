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
        Schema::create('recording_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('station_id'); // 放送局ID（例: TBS, QRR）
            $table->string('program_title'); // 番組名
            $table->dateTime('scheduled_start_time'); // 予約開始時刻
            $table->dateTime('scheduled_end_time'); // 予約終了時刻
            $table->enum('status', ['pending', 'recording', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->string('recording_id')->nullable(); // 録音ID（録音開始後に設定）
            $table->text('error_message')->nullable(); // エラーメッセージ
            $table->timestamps();

            // インデックス
            $table->index(['user_id', 'status']);
            $table->index('scheduled_start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recording_schedules');
    }
};
