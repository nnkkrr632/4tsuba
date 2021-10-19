<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

//ソフトデリートを有効にするために追加
use Illuminate\Database\Eloquent\SoftDeletes;


class Thread extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'posts_count', 'likes_count'];
    protected $hidden = ['created_at', 'updated_at', 'user_id', 'is_edited', 'posts_count', 'likes_count'];

    //日付のキャスト Threadモデルを使うとき、下記を整形する
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
    public function user()
    {
        return $this->belongsTo((User::class));
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function responses()
    {
        return $this->hasMany(Response::class);
    }
}
