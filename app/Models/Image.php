<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Like;



class Image extends Model
{
    use HasFactory;
    protected $fillable = ['thread_id', 'post_id', 'image_name', 'image_size'];
    protected $hidden = ['created_at', 'updated_at', 'image_size'];

    //リレーション定義
    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    //呼び出しメソッド
    public function returnThreadImagesTable()
    {
        return $this->select(
            'id as image_id',
            'thread_id',
            'image_name',
            'image_size'
        )->whereIn('post_id', function ($query) {
            $query->select(DB::raw('min(post_id)'))->from('images')->groupBy('thread_id');
        })->orderBy('thread_id', 'asc');
    }

    //以下LightBox用
    //スレッド
    public function returnImagesForTheThread(int $thread_id)
    {
        $images = DB::table('images')->leftJoin('posts', 'images.post_id', '=', 'posts.id')
            ->leftJoin('users', 'posts.user_id', '=', 'users.id')->select(
                'posts.displayed_post_id',
                'images.post_id',
                DB::raw('CONCAT("/storage/images/", images.image_name) as thumb'),
                DB::raw('CONCAT("/storage/images/", images.image_name) as src'),
                DB::raw('CONCAT("【レス番:", posts.displayed_post_id, "】【ユーザー:", users.name, "】「", posts.body, "」") as caption'),
            )->where('images.thread_id', $thread_id)->orderBy('posts.displayed_post_id')->get();
        return $images;
    }
    //返信
    public function returnImagesForTheResponses(int $thread_id, int $displayed_post_id)
    {
        $images = DB::table('images')->leftJoin('posts', 'images.post_id', '=', 'posts.id')
            ->leftJoin('users', 'posts.user_id', '=', 'users.id')
            ->select(
                'posts.displayed_post_id',
                'images.post_id',
                DB::raw('CONCAT("/storage/images/", images.image_name) as thumb'),
                DB::raw('CONCAT("/storage/images/", images.image_name) as src'),
                DB::raw('CONCAT("【レス番:", posts.displayed_post_id, "】【ユーザー:", users.name, "】「", posts.body, "」") as caption'),
            )->where('images.thread_id', $thread_id)->where(function ($query) use ($thread_id, $displayed_post_id) {
                $query->orWhereIn('posts.displayed_post_id', function ($query) use ($thread_id, $displayed_post_id) {
                    $query->select('origin_d_post_id')->from('responses')->where('thread_id', $thread_id)->where('dest_d_post_id', $displayed_post_id);
                })->orWhereIn('posts.displayed_post_id', function ($query) use ($thread_id, $displayed_post_id) {
                    $query->select('dest_d_post_id')->from('responses')->where('thread_id', $thread_id)->where('dest_d_post_id', $displayed_post_id);
                });
            })->orderBy('posts.id')->get();
        return $images;
    }
    //ユーザープロフィール(書込)
    public function returnImagesTheUserPosted(int $user_id)
    {
        $images = DB::table('images')->leftJoin('posts', 'images.post_id', '=', 'posts.id')
            ->leftJoin('threads', 'images.thread_id', '=', 'threads.id')
            ->select(
                'posts.displayed_post_id',
                'images.post_id',
                DB::raw('CONCAT("/storage/images/", images.image_name) as thumb'),
                DB::raw('CONCAT("/storage/images/", images.image_name) as src'),
                DB::raw('CONCAT("【スレッド:", threads.title, "】【レス番:", posts.displayed_post_id, "】「", posts.body, "」") as caption'),
            )->where('posts.user_id', $user_id)->orderBy('posts.id', 'desc')->get();
        return $images;
    }
    //ユーザープロフィール(いいね)
    public function returnImagesTheUserLiked(int $user_id)
    {
        $like = new Like();
        $liked_posts_table = $like->returnLikedPostsTable($user_id);


        $images = DB::table('images')->leftJoin('posts', 'images.post_id', '=', 'posts.id')
            ->leftJoin('threads', 'images.thread_id', '=', 'threads.id')
            ->joinSub($liked_posts_table, 'liked_posts_table', function ($join) {
                $join->on('images.post_id', '=', 'liked_posts_table.liked_post_id');
            })->select(
                'posts.displayed_post_id',
                'images.post_id',
                DB::raw('CONCAT("/storage/images/", images.image_name) as thumb'),
                DB::raw('CONCAT("/storage/images/", images.image_name) as src'),
                DB::raw('CONCAT("【スレッド:", threads.title, "】【レス番:", posts.displayed_post_id, "】「", posts.body, "」") as caption'),
            )->orderBy('liked_posts_table.liked_at', 'desc')->get();
        return $images;
    }
    //語句検索
    public function returnImagesForTheSearch(array $search_word_list)
    {
        $images = DB::table('images')->leftJoin('posts', 'images.post_id', '=', 'posts.id')
            ->leftJoin('threads', 'images.thread_id', '=', 'threads.id')->select(
                'posts.displayed_post_id',
                'images.post_id',
                DB::raw('CONCAT("/storage/images/", images.image_name) as thumb'),
                DB::raw('CONCAT("/storage/images/", images.image_name) as src'),
                DB::raw('CONCAT("【スレッド:", threads.title, "】【レス番:", posts.displayed_post_id, "】「", posts.body, "」") as caption'),
            )->where(function ($query) use ($search_word_list) {
                foreach ($search_word_list as $search_word) {
                    $query->orWhere('posts.body', 'LIKE', "%" . $search_word . "%");
                }
            })->orderBy('posts.created_at', 'desc')->get();
        return $images;
    }
}
