<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class Like extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'post_id'];
    protected $hidden = ['created_at', 'updated_at'];

    //リレーション定義
    public function user()
    {
        return $this->belongsTo((User::class));
    }
    public function post()
    {
        return $this->belongsTo((Post::class));
    }

    public function returnLikedPostsTable($user_id)
    {
        return $this->select('user_id as liking_user_id', 'post_id as liked_post_id', 'created_at as liked_at')
            ->where('user_id', $user_id);
    }
    public function check_already_stored($post_id)
    {
        return Like::where('user_id', Auth::id())
            ->where('post_id', $post_id)->count();
    }
}
