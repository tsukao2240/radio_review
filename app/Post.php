<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'station_id',
        'title',
        'body',
        'rating',
        'likes_count',
        'comments_count',
    ];

    protected $attributes = [
        'rating' => 3.0,
        'likes_count' => 0,
        'comments_count' => 0,
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
        return $this->belongsTo(RadioProgram::class, 'program_id');
    }

    /**
     * この投稿に付けられたタグ
     */
    public function tags()
    {
        return $this->belongsToMany(PostTag::class, 'post_post_tag');
    }

    /**
     * この投稿へのいいね
     */
    public function likes()
    {
        return $this->hasMany(PostLike::class);
    }

    /**
     * この投稿へのコメント
     */
    public function comments()
    {
        return $this->hasMany(PostComment::class)->orderBy('created_at', 'desc');
    }

    /**
     * 指定ユーザーがいいねしているか確認
     */
    public function isLikedBy($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    /**
     * 最小評価でフィルタリング
     */
    public function scopeWithMinRating($query, $minRating)
    {
        return $query->where('rating', '>=', $minRating);
    }

    /**
     * タグでフィルタリング
     */
    public function scopeWithTag($query, $tagId)
    {
        return $query->whereHas('tags', function ($q) use ($tagId) {
            $q->where('post_tags.id', $tagId);
        });
    }

    /**
     * いいね数順でソート
     */
    public function scopePopular($query)
    {
        return $query->orderBy('likes_count', 'desc');
    }
}
