<?php

namespace App\Http\Controllers;
//フォームリクエスト
use App\Http\Requests\StoreLikeRequest;
use App\Http\Requests\DestroyLikeRequest;

use App\Models\Like;
use App\Models\Thread;
use App\RedisModels\RedisReport;
//authを使用する
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function store(StoreLikeRequest $store_like_request)
    {
        Like::create([
            'user_id' => Auth::id(),
            'post_id' => $store_like_request->post_id,
        ]);

        //threadsテーブルのlikes_countインクリメント
        $thread = new Thread();
        $thread->find($store_like_request->thread_id)->increment('likes_count');
        //redisのOverviewハッシュのインクリメント
        $redis_report = new RedisReport();
        $redis_report->incrementLikesCount(Auth::id());
    }

    public function destroy(DestroyLikeRequest $destroy_like_request)
    {
        $target_like = Like::where('user_id', Auth::id())
            ->where('post_id', $destroy_like_request->post_id)->first();
        $this->authorize('delete', $target_like);
        $target_like->delete();

        //threadsテーブルのlikes_countデクリメント
        $thread = new Thread();
        $thread->find($destroy_like_request->thread_id)->decrement('likes_count');
        //redisのOverviewハッシュのデクリメント
        $redis_report = new RedisReport();
        $redis_report->decrementLikesCount(Auth::id());
    }
}
