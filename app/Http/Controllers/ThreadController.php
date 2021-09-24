<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Thread;
use App\Models\Image;
use App\Models\Gatekeeper;

//authを使用する
use Illuminate\Support\Facades\Auth;
//認可gateを使用する
use Illuminate\Support\Facades\Gate;


class ThreadController extends Controller
{
    //スレッド取得
    public function index(Request $request)
    {

        $image = new Image();
        $thread_image_table = $image->returnThreadImageTable();

        $sort_list = array("最終更新", "作成日時", "書込数", "いいね数");
        $order_set = array();

        switch ($request->sort) {
            case $sort_list[0]:
                array_push($order_set, 'updated_at');
                break;
            case $sort_list[1]:
                array_push($order_set, 'created_at');
                break;
            case $sort_list[2]:
                array_push($order_set, 'post_count');
                break;
            case $sort_list[3]:
                array_push($order_set, 'like_count');
                break;
            default:
                array_push($order_set, 'updated_at');
        }

        switch ($request->order) {
            case 'desc':
                array_push($order_set, 'desc');
                break;
            case 'asc':
                array_push($order_set, 'asc');
                break;
            default:
                array_push($order_set, 'desc');
        }

        return Thread::leftJoinSub($thread_image_table, 'thread_image_table', function ($join) {
            $join->on('threads.id', '=', 'thread_image_table.thread_id');
        })
            ->orderBy('threads.' . $order_set[0], $order_set[1])->get()
            ->makeVisible(['created_at', 'updated_at', 'user_id', 'post_count', 'like_count', 'is_edited']);
    }

    //★個別スレッド show
    public function show($thread_id)
    {
        $image = new Image();
        $thread_image_table = $image->returnThreadImageTable();

        return Thread
            ::leftJoinSub($thread_image_table, 'thread_image_table', function ($join) {
                $join->on('threads.id', '=', 'thread_image_table.thread_id');
            })
            ->find((int)$thread_id)
            //findからThreadModelクラス。(それまではEloquentBuilderクラス)
            //故に、findより後にThreadModelのメソッドであるmakeVisibleをする必要がある。この順番は重要。
            ->makeVisible(['updated_at', 'user_id', 'post_count', 'like_count', 'is_edited']);
    }

    //スレッド store
    public function store(Request $request)
    {
        //NGワード置換
        $gate_keeper = new GateKeeper();
        $checked_title = $gate_keeper->convertNgWordsIfExist($request->title);

        $thread = Thread::create([
            'user_id' => Auth::id(),
            'title' => $checked_title,
        ]);

        //リクエストにスレッドidを追加
        $request->merge([
            'thread_id' => $thread->id,
        ]);

        $post_controller = new PostController();
        $post_controller->store($request);

        return $thread->id;
    }

    //VueRouter遷移前スレッド存在確認
    public function exists($thread_id)
    {
        //文字列をURLに入力されたら404送り
        if (preg_match('/\D/', $thread_id)) {
            return 0;
        } else {
            $converted_thread_id = (int)$thread_id;
            return Thread::where('id', $converted_thread_id)->count();
        }
    }

    //スレッド削除(スタッフ用)
    public function destroy(Request $request)
    {
        $target_thread = Thread::find($request->id);
        $response = Gate::inspect('delete', $target_thread);

        if ($response->allowed()) {
            $target_thread->delete();
        } else {
            return $response->message();
        }
    }
}
