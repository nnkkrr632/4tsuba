<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class DestroyLikeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'thread_id' => [
                'required',
                'not_in:"null"',
                'numeric',
                'exists:threads,id'
            ],
            'post_id' => [
                'required',
                'not_in:"null"',
                'numeric',
                //store_likes側にあるpost_id.existsは不要 because削除済ポストのいいねを外したい時があるから
                //likesテーブルuser_id列との複合ユニーク
                //↓実際に流れるsqlはwhere post_id = xx, and user_id = yy と、勝手にpost_idも
                //where句に入れてくれるため、 exists:likes,post_id は不要
                Rule::exists('likes')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                })
            ]
        ];
    }
    public function messages()
    {
        return [
            'thread_id.required' => '送信値の変更を検知したためキャンセルしました。',
            'thread_id.not_in' => '送信値の変更を検知したためキャンセルしました。',
            'thread_id.numeric' => '送信値の変更を検知したためキャンセルしました。',
            'thread_id.exists' => '送信値の変更を検知したためキャンセルしました。',
            'post_id.required' => '送信値の変更を検知したためキャンセルしました。',
            'post_id.not_in' => '送信値の変更を検知したためキャンセルしました。',
            'post_id.numeric' => '送信値の変更を検知したためキャンセルしました。',
            'post_id.exists' => '送信値の変更を検知したためキャンセルしました。',
        ];
    }
}
