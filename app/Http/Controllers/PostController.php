<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Response;
use App\Models\Like;
use App\Models\MuteWord;
use App\Models\MuteUser;
use App\Models\Gatekeeper;
//authを使用する
use Illuminate\Support\Facades\Auth;
//認可gateを使用する
use Illuminate\Support\Facades\Gate;
//フォームリクエスト
use App\Http\Requests\StoreTPIRequest;
use App\Http\Requests\EditPIRequest;
use App\Http\Requests\DestroyPIRequest;
use App\Http\Requests\GetPostsRequest;

class PostController extends Controller
{
    //★ポスト取得
    //※query() ：動的にwhere句を生成。=>スレッド個別とユーザープロフィールでこのメソッドを使い回す。
    //クエリパラメータで値を渡す api/posts?where=thread_id&value=2
    public function index(GetPostsRequest $get_posts_request)
    {
        // $temp_post = new Post();
        // $login_user_post_table = $temp_post->returnLoginUserPostTable($thread_id);


        //select(*)ないと外部結合した列がでなくなるので注意
        $query = Post::query();
        $query->select('*')->with(['thread', 'image', 'user',])
            ->withCount([
                'likes',
                'likes AS login_user_liked' => function ($query) {
                    $query->where('user_id', Auth::id());
                },
            ]);
        //スレッド
        if ($get_posts_request->where == 'thread_id') {
            //スレッド内の返信関係を取得 thread_idを特定しないと掴みようがないため、この位置
            $response = new Response();
            $responded_count_table = $response->returnRespondedCountTable($get_posts_request->value);

            $query->withTrashed()->leftJoinSub($responded_count_table, 'responded_count_table', function ($join) {
                $join->on('posts.displayed_post_id', '=', 'responded_count_table.dest_d_post_id');
            })->where('posts.thread_id', $get_posts_request->value)->orderBy('posts.id');
        }
        //返信
        else if ($get_posts_request->where == 'responses') {
            $response = new Response();
            $responded_count_table = $response->returnRespondedCountTable($get_posts_request->value);

            $query->withTrashed()->where('posts.thread_id', $get_posts_request->value[0])
                ->leftJoinSub($responded_count_table, 'responded_count_table', function ($join) {
                    $join->on('posts.displayed_post_id', '=', 'responded_count_table.dest_d_post_id');
                })->where(function ($query) use ($get_posts_request) {
                    $query->orWhereIn('posts.displayed_post_id', function ($query) use ($get_posts_request) {
                        $query->select('origin_d_post_id')->from('responses')->where('thread_id', $get_posts_request->value[0])
                            ->where('dest_d_post_id', $get_posts_request->value[1]);
                    })->orWhereIn('posts.displayed_post_id', function ($query) use ($get_posts_request) {
                        $query->select('dest_d_post_id')->from('responses')->where('thread_id', $get_posts_request->value[0])
                            ->where('dest_d_post_id', $get_posts_request->value[1]);
                    });
                })->orderBy('posts.id');
        }
        //プロフィール書込
        else if ($get_posts_request->where == 'user_id') {
            $query->where('posts.user_id', $get_posts_request->value)->orderBy('posts.id', 'desc');
        }
        //プロフィールいいね欄
        else if ($get_posts_request->where == 'user_like') {
            $like = new Like();
            $liked_posts_table = $like->returnLikedPostsTable($get_posts_request->value);

            $query->withTrashed()->leftJoinSub($liked_posts_table, 'liked_posts_table', function ($join) {
                $join->on('posts.id', '=', 'liked_posts_table.liked_post_id');
            })->whereNotNull('liked_posts_table.liked_post_id')->orderBy('liked_posts_table.liked_at', 'desc');
        }
        //ワード検索
        else if ($get_posts_request->where == 'search') {
            //$get_posts_request->valueは検索単語の配列(vue側でsplit)
            $search_word_list = $get_posts_request->value;
            $query->where(function ($query) use ($search_word_list) {
                foreach ($search_word_list as $search_word) {
                    $query->orWhere('posts.body', 'LIKE', "%" . $search_word . "%");
                }
            });
            $query->orderBy('posts.created_at', 'desc');
        }

        //ポストの加工
        $posts = $query->get();
        $mute_word = new MuteWord();
        $posts = $mute_word->addHasMuteWordsKeyToPosts($posts);
        $mute_user = new MuteUser();
        $posts = $mute_user->addPostedByMuteUsersKeyToPosts($posts);

        $lightbox_index = 0;
        foreach ($posts as $post) {
            //削除済み書込の見せたくないプロパティをマスク
            if ($post['deleted_at'] != null) {
                $post->makeHidden([
                    'created_at', 'updated_at', 'user_id', 'body',
                    'is_edited', 'like_count', 'posted_by_mute_users', 'thread',
                    'image', 'user', 'has_mute_words'
                ]);
            }
            //lightboxのためのインデックスを付与
            else if ($post['image']) {
                $post['lightbox_index'] = $lightbox_index;
                $lightbox_index++;
            }
        }
        return $posts;
    }


    //作成 store
    public function store(StoreTPIRequest $store_t_p_i_request)
    {
        //リクエストにディスプレイドポストidを追加
        $temp_post = new Post();
        $store_t_p_i_request->merge([
            'displayed_post_id' => $temp_post->returnMaxDisplayedPostId($store_t_p_i_request->thread_id) + 1,
        ]);

        //返信関係登録
        if (strpos($store_t_p_i_request->body, '>>') !== false) {
            $response_controller = new ResponseController();
            $response_controller->store($store_t_p_i_request);
        }

        //NGワード置換
        $gate_keeper = new GateKeeper();
        //$checked_body = $gate_keeper->convertNgWordsIfExist($store_t_p_i_request->body);

        $post = Post::create([
            'user_id' => Auth::id(),
            'thread_id' => $store_t_p_i_request->thread_id,
            'displayed_post_id' => $store_t_p_i_request->displayed_post_id,
            'body' => $store_t_p_i_request->body,
        ]);

        //スレッドのpost_countをインクリメント
        //modelにリレーションを定義しているからできること
        $post->thread()->increment('post_count');

        //画像があれば
        if ($store_t_p_i_request->image) {
            $store_t_p_i_request->merge([
                'post_id' => $post->id,
            ]);
            $image_controller = new ImageController();
            $image_controller->store($store_t_p_i_request);
        }
    }


    //ポスト更新
    public function edit(EditPIRequest $edit_pi_request)
    {
        $target_post = Post::find($edit_pi_request->id);
        $response = Gate::inspect('delete', $target_post);

        if ($response->allowed()) {
            //NGワード置換
            $gate_keeper = new GateKeeper();
            $checked_body = $gate_keeper->convertNgWordsIfExist($edit_pi_request->body);

            $target_post->update([
                'body' => $checked_body,
                'is_edited' => 1,
            ]);

            //一度返信関係をリセット  and 再取得
            $response_controller = new ResponseController();
            $response_controller->destroy($edit_pi_request);
            //返信関係再登録
            if (strpos($edit_pi_request->body, '>>') !== false) {
                $response_controller->store($edit_pi_request);
            }

            //画像があれば
            if ($edit_pi_request->hasFile('image')) {
                $image_controller = new ImageController();
                $image_controller->edit($edit_pi_request);
            }
            //編集前の画像削除の場合(画像変更の場合でも画像を削除にチェックしてたら画像を削除するからこの位置)
            if ($edit_pi_request->delete_image) {
                $image_controller = new ImageController();
                $image_controller->destroy($target_post->id);
            }
        } else {
            return $response->message();
        }
    }


    //ポスト削除
    public function destroy(DestroyPIRequest $destroy_pi_request)
    {
        $target_post = Post::find($destroy_pi_request->id);
        $response = Gate::inspect('delete', $target_post);

        if ($response->allowed()) {
            $target_post->delete();
            $image_controller = new ImageController();
            $image_controller->destroy($destroy_pi_request->id);
        } else {
            return $response->message();
        }
    }
}
