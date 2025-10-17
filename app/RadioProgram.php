<?php

namespace App;

use App\Http\Controllers\RadioBroadcastCotroller;
use App\Http\Controllers\RadioProgramController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Log\Logger;
use App\Http\Controllers\Log;

class RadioProgram extends Model
{
    use HasFactory;

    protected $fillable =
    [
        'station_id',
        'title',
        'cast',
        'start',
        'end',
        'info',
        'url',
        'image',
    ];

    protected $table = 'radio_programs';

    public $timestamps = false;
}
