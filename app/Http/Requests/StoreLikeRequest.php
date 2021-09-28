<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class StoreLikeRequest extends FormRequest
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
                'exists:posts,id',
                //likesテーブルuser_id列との複合ユニーク
                Rule::unique('likes')->where(function ($query) {
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
            'post_id.unique' => '送信値の変更を検知したためキャンセルしました。',
        ];
    }
}
