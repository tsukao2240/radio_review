<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_order',
    ];

    /**
     * このタグが付けられた投稿
     */
    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_post_tag');
    }

    /**
     * 表示順でソートするスコープ
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }
}
