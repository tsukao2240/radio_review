<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;
    //
    protected $fillable =
    [
        'id',
        'user_id',
        'program_id',
        'program_title',
        'title',
        'body',
    ];


    public function post()
    {
        return $this->belongsTo('App\Post');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function radioProgram()
    {
        return $this->belongsTo('App\RadioProgram');
    }
}
