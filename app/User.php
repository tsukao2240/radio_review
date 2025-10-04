<?php

namespace App;

use App\Notifications\CustomVerifyEmail;
use App\Notifications\CustomResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContact;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable implements MustVerifyEmailContact
{
    use Notifiable, MustVerifyEmail, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function posts()
    {
        return $this->hasMany('App\Post');
    }

    public function favoritePrograms()
    {
        return $this->hasMany(FavoriteProgram::class)->orderBy('created_at', 'desc');
    }

    public function recordingSchedules()
    {
        return $this->hasMany(RecordingSchedule::class)->orderBy('scheduled_start_time', 'desc');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class)->orderBy('created_at', 'desc');
    }

    //会員登録時の仮メール送信
    public function sendmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail());
    }

    //パスワードリセットメール
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPassword($token));
    }
}
