<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordingSchedule extends Model
{
    protected $fillable = [
        'user_id',
        'station_id',
        'program_title',
        'scheduled_start_time',
        'scheduled_end_time',
        'status',
        'recording_id',
        'error_message'
    ];

    protected $casts = [
        'scheduled_start_time' => 'datetime',
        'scheduled_end_time' => 'datetime',
    ];

    // リレーション
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ステータスチェック
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRecording(): bool
    {
        return $this->status === 'recording';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    // 録音開始予定時刻を過ぎているかチェック
    public function shouldStartRecording(): bool
    {
        return $this->isPending() && now()->gte($this->scheduled_start_time);
    }
}
