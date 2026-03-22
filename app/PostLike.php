<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
    ];

    /**
     * いいねされた投稿
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * いいねしたユーザー
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
