<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class MuteUser extends Model
{
    protected $table = 'mute_users';
    use HasFactory;

    protected $fillable = [
        'muting_user_id', 'user_id'
    ];
    protected $hidden = ['created_at', 'updated_at'];


    //リレーション定義
    public function user()
    {
        return $this->belongsTo((User::class));
    }

    //modelメソッド
    public function addPostedByMuteUsersKeyToPosts($posts)
    {
        $mute_user_id_list = MuteUser::where('muting_user_id', Auth::id())->pluck('user_id')->toArray();

        foreach ($posts as $post) {
            if (in_array($post->user_id, $mute_user_id_list, true)) {
                $tf = true;
            } else {
                $tf = false;
            }
            $post['posted_by_mute_users'] = $tf;
        }

        return $posts;
    }
}
