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

    // ユーザーとのリレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}