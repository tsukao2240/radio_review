<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'body',
    ];

    protected $with = ['user'];

    /**
     * コメントされた投稿
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * コメントしたユーザー
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
