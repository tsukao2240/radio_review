<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FavoriteProgram extends Model
{
    protected $fillable = [
        'user_id',
        'station_id',
        'program_title',
        'broadcast_day',
    ];

    // broadcast_day: 0=月, 1=火, 2=水, 3=木, 4=金, 5=土, 6=日（nullable）
    protected $casts = [
        'broadcast_day' => 'integer',
    ];

    // ユーザーとのリレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}