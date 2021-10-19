<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Post;



class MuteWord extends Model
{
    protected $table = 'mute_words';
    use HasFactory;

    protected $fillable = [
        'user_id', 'mute_word'
    ];
    protected $hidden = ['created_at', 'updated_at'];


    //リレーション定義
    public function user()
    {
        return $this->belongsTo((User::class));
    }

    //modelメソッド
    public function addHasMuteWordsKeyToPosts(Collection $posts)
    {
        $mute_words = MuteWord::where('user_id', Auth::id())->pluck('mute_word')->toArray();
        if ($mute_words) {
            $regular_expression = '/^.*' . implode('|', $mute_words) . '.*$/';

            foreach ($posts as $post) {
                if (!(preg_match($regular_expression, $post->body))) {
                    $tf = false;
                } else {
                    $tf = true;
                }
                $post['has_mute_words'] = $tf;
            }
        } else {
            foreach ($posts as $post) {
                $post['has_mute_words'] = false;
            }
        }
        return $posts;
    }
}
