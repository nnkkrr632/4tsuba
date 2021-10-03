<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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
        return $this->hasMany(like::class);
    }

    public function returnMaxDisplayedPostId($thread_id)
    {
        return $this->where('thread_id', $thread_id)->withTrashed()->count();
    }

    public function returnLoginUserPostTable($thread_id)
    {
        return $this->select('id as login_user_posted_post_id', DB::raw('1 as is_login_user_posted'))
            ->where('thread_id', $thread_id)->where('user_id', Auth::id())
            ->whereNotNull('user_id');
    }
}
