<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gatekeeper;
use App\Models\Response;


class ResponseController extends Controller
{
    public function store(Request $request)
    {
        $gate_keeper = new Gatekeeper();
        $dest_displayed_post_id_list = $gate_keeper->returnDestinationDisplayedPostIdList($request->body);

        if ($dest_displayed_post_id_list) {
            for ($i = 0; $i < count($dest_displayed_post_id_list); $i++) {
                Response::create([
                    'thread_id' => $request->thread_id,
                    'origin_d_post_id' => $request->displayed_post_id,
                    'dest_d_post_id' => $dest_displayed_post_id_list[$i]
                ]);
            }
        }
    }

    public function destroy(Request $request)
    {
        Response::where('thread_id', $request->thread_id)
            ->where('origin_d_post_id', $request->displayed_post_id)->delete();
    }
    //VueRouter遷移前スレッド存在確認
    public function exists($thread_id, $displayed_post_id)
    {
        //文字列をURLに入力されたら無理やり0に変換
        $converted_thread_id = (int)$thread_id;
        $converted_displayed_post_id = (int)$displayed_post_id;

        return Response::where('thread_id', $converted_thread_id)
            ->where('dest_d_post_id', $converted_displayed_post_id)->count();
    }
}
