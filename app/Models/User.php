<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
//Laravel Sanctum APIトークン？
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;



class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password', 'icon_name', 'icon_size',];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['created_at', 'updated_at', 'email', 'email_verified_at', 'password', 'remember_token', 'icon_size'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    //日付のキャスト Userモデルを使うとき、下記を整形する
    public function getCreatedAtAttribute($value)
    {
        //Carbonはなぜかapp.phpのtimezoneを参照してくれずUTCを使う
        return  Carbon::parse($value)->addHours(9)->format("Y/n/j H:i");
    }
    public function getUpdatedAtAttribute($value)
    {
        return  Carbon::parse($value)->addHours(9)->format("Y/n/j H:i");
    }

    //リレーション定義
    public function threads()
    {
        return $this->hasMany(Thread::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
    public function likes()
    {
        return $this->hasMany(Like::class);
    }
    public function mute_words()
    {
        return $this->hasMany(MuteWord::class);
    }
    public function mute_users()
    {
        return $this->hasMany(MuteUser::class, 'muting_user_id');
    }

    public function checkPassword(String $typed_password)
    {

        $password = Auth::user()->password;
        return Hash::check($typed_password, $password);
    }
}
