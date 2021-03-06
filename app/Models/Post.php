<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Carbon\Carbon;

class Post extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'thread_id', 'displayed_post_id',
        'body', 'is_edited'
    ];

    protected $hidden = ['updated_at'];


    //日付のキャスト
    public function getCreatedAtAttribute($value)
    {
        //Carbonはなぜかapp.phpのtimezoneを参照してくれずUTCを使う
        $utc = Carbon::parse($value);
        $tokyo = $utc->addHours(9);
        $formatted_tokyo = $tokyo->format("Y/n/j H:i");
        return $formatted_tokyo;
    }
    public function getUpdatedAtAttribute($value)
    {
        return  Carbon::parse($value)->addHours(9)->format("Y/n/j H:i");
    }
    public function getDeletedAtAttribute($value)
    {
        //if条件つけないと全てに+9時間ついて、deleted_atがnullではなくなってしまう
        if ($value != null) {
            return  Carbon::parse($value)->addHours(9)->format("Y/n/j H:i");
        }
    }


    //リレーション定義
    public function user()
    {
        return $this->belongsTo((User::class));
    }

    public function thread()
    {
        return $this->belongsTo((Thread::class));
    }

    public function image()
    {
        return $this->hasOne(Image::class);
    }
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function returnMaxDisplayedPostId($thread_id)
    {
        return $this->where('thread_id', $thread_id)->withTrashed()->count();
    }
    public function hiddenColumnsForDeletedPost()
    {
        return $this->makeHidden([
            'created_at', 'updated_at', 'user_id', 'body',
            'is_edited', 'likes_count', 'posted_by_mute_users', 'thread',
            'image', 'user', 'has_mute_words'
        ]);
    }
}
