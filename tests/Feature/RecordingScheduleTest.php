<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use App\RecordingSchedule;
use Carbon\Carbon;

class RecordingScheduleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 録音予約作成テスト
     */
    public function test_can_create_recording_schedule()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/recording/schedule', [
            'station_id' => 'TBS',
            'program_title' => 'テスト番組',
            'scheduled_start_time' => Carbon::now()->addHours(2)->format('YmdHis'),
            'scheduled_end_time' => Carbon::now()->addHours(3)->format('YmdHis'),
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                 ]);

        $this->assertDatabaseHas('recording_schedules', [
            'user_id' => $user->id,
            'station_id' => 'TBS',
            'program_title' => 'テスト番組',
            'status' => 'pending',
        ]);
    }

    /**
     * 複数の予約作成テスト
     */
    public function test_can_create_multiple_schedules()
    {
        $user = User::factory()->create();

        // 1回目の予約
        $response1 = $this->actingAs($user)->postJson('/recording/schedule', [
            'station_id' => 'TBS',
            'program_title' => 'テスト番組1',
            'scheduled_start_time' => Carbon::now()->addHours(2)->format('YmdHis'),
            'scheduled_end_time' => Carbon::now()->addHours(3)->format('YmdHis'),
        ]);

        $response1->assertStatus(200)
                  ->assertJson(['success' => true]);

        // 2回目の予約（異なる番組）
        $response2 = $this->actingAs($user)->postJson('/recording/schedule', [
            'station_id' => 'TBS',
            'program_title' => 'テスト番組2',
            'scheduled_start_time' => Carbon::now()->addHours(4)->format('YmdHis'),
            'scheduled_end_time' => Carbon::now()->addHours(5)->format('YmdHis'),
        ]);

        $response2->assertStatus(200)
                  ->assertJson(['success' => true]);

        // データベースに2件存在することを確認
        $this->assertEquals(2, RecordingSchedule::count());
    }

    /**
     * 1週間制限テスト
     */
    public function test_cannot_schedule_beyond_one_week()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/recording/schedule', [
            'station_id' => 'TBS',
            'program_title' => 'テスト番組',
            'scheduled_start_time' => Carbon::now()->addWeeks(2)->format('YmdHi00'),
            'scheduled_end_time' => Carbon::now()->addWeeks(2)->addHour()->format('YmdHi00'),
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => false,
                 ]);

        $this->assertEquals(0, RecordingSchedule::count());
    }

    /**
     * 予約キャンセルテスト
     */
    public function test_can_cancel_pending_schedule()
    {
        $user = User::factory()->create();

        $schedule = RecordingSchedule::create([
            'user_id' => $user->id,
            'station_id' => 'TBS',
            'program_title' => 'テスト番組',
            'scheduled_start_time' => Carbon::now()->addHours(2),
            'scheduled_end_time' => Carbon::now()->addHours(3),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->postJson('/recording/schedule/cancel', [
            'schedule_id' => $schedule->id,
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                 ]);

        $this->assertDatabaseHas('recording_schedules', [
            'id' => $schedule->id,
            'status' => 'cancelled',
        ]);
    }

    /**
     * 録音中の予約をキャンセルできないことを確認
     */
    public function test_cannot_cancel_recording_schedule()
    {
        $user = User::factory()->create();

        $schedule = RecordingSchedule::create([
            'user_id' => $user->id,
            'station_id' => 'TBS',
            'program_title' => 'テスト番組',
            'scheduled_start_time' => Carbon::now()->addHours(2),
            'scheduled_end_time' => Carbon::now()->addHours(3),
            'status' => 'recording',
        ]);

        $response = $this->actingAs($user)->postJson('/recording/schedule/cancel', [
            'schedule_id' => $schedule->id,
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => false,
                 ]);

        // ステータスが変更されていないことを確認
        $this->assertDatabaseHas('recording_schedules', [
            'id' => $schedule->id,
            'status' => 'recording',
        ]);
    }

    /**
     * 未ログイン時のリダイレクトテスト
     */
    public function test_guest_cannot_create_schedule()
    {
        $response = $this->postJson('/recording/schedule', [
            'station_id' => 'TBS',
            'program_title' => 'テスト番組',
            'scheduled_start_time' => Carbon::now()->addHours(2)->format('YmdHi00'),
            'scheduled_end_time' => Carbon::now()->addHours(3)->format('YmdHi00'),
        ]);

        $response->assertStatus(401);
    }

    /**
     * 録音予約一覧画面表示テスト
     */
    public function test_can_view_schedules_list()
    {
        $user = User::factory()->create();

        RecordingSchedule::create([
            'user_id' => $user->id,
            'station_id' => 'TBS',
            'program_title' => 'テスト番組',
            'scheduled_start_time' => Carbon::now()->addHours(2),
            'scheduled_end_time' => Carbon::now()->addHours(3),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get('/recording/schedules');

        $response->assertStatus(200)
                 ->assertViewHas('schedules');
    }
}