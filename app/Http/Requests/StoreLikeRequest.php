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
                'numeric',
                'exists:threads,id'
            ],
            'post_id' => [
                'required',
                'numeric',
                'exists:posts,id',
                //likesテーブルuser_id列との複合ユニーク
                Rule::unique('likes')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                })
            ]
        ];
    }
}
