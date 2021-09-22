<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Like;
use App\Models\Post;
use App\Models\Thread;
use Faker\Core\Number;
//authを使用する
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function store(Request $request)
    {
        //ポストが存在する(既に削除済みじゃない)ときのみいいねできる
        $post = new Post();
        if ($post->find($request->post_id)) {
            Like::create([
                'user_id' => Auth::id(),
                'post_id' => $request->post_id,
            ]);

            //threadsテーブルのlike_countインクリメント
            $thread = new Thread();
            $thread->find($request->thread_id)->increment('like_count');
        }
    }

    public function destroy(Request $request)
    {
        $target_like = Like::where('user_id', Auth::id())
            ->where('post_id', $request->post_id)->first();
        $this->authorize('delete', $target_like);
        $target_like->delete();

        //threadsテーブルのlike_countデクリメント
        $thread = new Thread();
        $thread->find($request->thread_id)->decrement('like_count');
    }
}
