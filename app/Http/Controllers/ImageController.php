<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Image;
use App\Models\Like;
use Illuminate\Support\Facades\Storage;
//フォームリクエスト
use App\Http\Requests\StoreTPIRequest;
use App\Http\Requests\EditPIRequest;

class ImageController extends Controller
{
    public function store(StoreTPIRequest $store_t_p_i_request)
    {
        //リクエストされたファイルの情報を持ったUploadedFileクラスのインスタンス
        $uploaded_image = $store_t_p_i_request->file('image');
        //ファイル保存処理  storage/app/public/images の意味 app配下から記述する
        $uploaded_image->store('public/images');

        Image::create([
            'thread_id' => $store_t_p_i_request->thread_id,
            'post_id' => $store_t_p_i_request->post_id,
            'image_name' => $uploaded_image->hashName(),
            'image_size' => $uploaded_image->getSize(),
        ]);
    }

    public function edit(EditPIRequest $edit_pi_request)
    {
        $uploaded_image = $edit_pi_request->file('image');
        //新しい画像ストレージ保存
        $uploaded_image->store('public/images');

        $image = Image::where('post_id', $edit_pi_request->id)->first();
        if ($image) {
            //ファイル削除処理  storage/app/public/images の意味 app配下から記述する
            Storage::delete('public/images/' . $image->image_name);
            //DB更新
            $image->update([
                'image_name' => $uploaded_image->hashName(),
                'image_size' => $uploaded_image->getSize(),
            ]);
        } else {
            Image::create([
                'thread_id' => $edit_pi_request->thread_id,
                'post_id' => $edit_pi_request->id,
                'image_name' => $uploaded_image->hashName(),
                'image_size' => $uploaded_image->getSize(),
            ]);
        }
    }

    public function destroy(int $post_id)
    {
        //ない場合はnullが返る
        $del_image = Image::where('post_id', $post_id)->first();

        if ($del_image) {
            //ファイル削除処理  storage/app/public/images の意味 app配下から記述する
            Storage::delete('public/images/' . $del_image->image_name);
            //テーブルから削除
            $del_image->delete();
        } else {
            //画像編集画面から、既存の投稿に画像ないのに「画像を削除する」にチェックしたときここに入ってくる
        }
    }

    //以下LightBoxのAPI
    public function returnImagesForTheThread(int $thread_id)
    {
        $image = new Image();
        return $image->returnImagesForTheThread($thread_id);
    }
    public function returnImagesForTheResponses(int $thread_id, int $displayed_post_id)
    {
        $image = new Image();
        return $image->returnImagesForTheResponses($thread_id, $displayed_post_id);
    }
    public function returnImagesTheUserPosted(int $user_id)
    {
        $image = new Image();
        return $image->returnImagesTheUserPosted($user_id);
    }
    public function returnImagesTheUserLiked(int $user_id)
    {
        $image = new Image();
        return $image->returnImagesTheUserLiked($user_id);
    }
    public function returnImagesForTheSearch(Request $request)
    {
        $search_word_list = $request->unique_word_list;

        $image = new Image();
        return $image->returnImagesForTheSearch($search_word_list);
    }
}
