<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Thread;
use App\Models\Image;
use App\Models\Monitor;

//authを使用する
use Illuminate\Support\Facades\Auth;
//認可gateを使用する
use Illuminate\Support\Facades\Gate;
//フォームリクエスト
use App\Http\Requests\StoreTPIRequest;
use App\Http\Requests\ThreadsOrderByRequest;
use App\Http\Requests\DestroyThreadRequest;


class ThreadController extends Controller
{
    //スレッド取得
    public function index(ThreadsOrderByRequest $threads_order_by_request)
    {
        $image = new Image();
        $thread_images_table = $image->returnThreadImagesTable();

        return Thread::leftJoinSub($thread_images_table, 'thread_images_table', function ($join) {
            $join->on('threads.id', '=', 'thread_images_table.thread_id');
        })
            ->orderBy('threads.' . $threads_order_by_request->column, $threads_order_by_request->desc_asc)->get()
            ->makeVisible(['created_at', 'updated_at', 'user_id', 'posts_count', 'likes_count', 'is_edited']);
    }

    //★個別スレッド show
    public function show($thread_id)
    {
        $image = new Image();
        $thread_images_table = $image->returnThreadImagesTable();

        return Thread
            ::leftJoinSub($thread_images_table, 'thread_images_table', function ($join) {
                $join->on('threads.id', '=', 'thread_images_table.thread_id');
            })
            ->find((int)$thread_id)
            //findからThreadModelクラス。(それまではEloquentBuilderクラス)
            //故に、findより後にThreadModelのメソッドであるmakeVisibleをする必要がある。この順番は重要。
            ->makeVisible(['updated_at', 'user_id', 'posts_count', 'likes_count', 'is_edited']);
    }

    //スレッド store
    public function store(StoreTPIRequest $store_t_p_i_request)
    {
        //NGワード置換
        $monitor = new Monitor();
        $checked_title = $monitor->convertNgWordsIfExist($store_t_p_i_request->title);

        $thread = Thread::create([
            'user_id' => Auth::id(),
            'title' => $checked_title,
        ]);

        //リクエストにポストIDを追加
        $store_t_p_i_request->merge([
            'thread_id' => $thread->id,
        ]);

        $post_controller = new PostController();
        $post_controller->store($store_t_p_i_request);

        return $thread->id;
    }

    //VueRouter遷移前スレッド存在確認
    public function exists($thread_id)
    {
        //文字列をURLに入力されたら404送り
        if (preg_match('/\D/', $thread_id)) {
            return 0;
        } else {
            //文字列をURLに入力されたら無理やり0に変換
            $converted_thread_id = (int)$thread_id;

            return Thread::where('id', $converted_thread_id)->count();
        }
    }

    //スレッド削除(スタッフ用)
    public function destroy(DestroyThreadRequest $destroy_thread_request)
    {
        $target_thread = Thread::find($destroy_thread_request->id);
        $response = Gate::inspect('delete', $target_thread);

        if ($response->allowed()) {
            $image_controller = new ImageController();
            $image_controller->destroyOnlyFiles($target_thread->id);
            $target_thread->delete();
        } else {
            return $response->message();
        }
    }
}
