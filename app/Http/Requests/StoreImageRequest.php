<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreImageRequest extends FormRequest
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
            'image' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png,gif,svg',
            ],
            'thread_id' => [
                'required',
                'numeric',
                'exists:thread,id'
            ],
            'post_id' => [
                'required',
                'numeric',
                'exists:posts,id',
                //imagesテーブルthread_id列との複合ユニーク
                Rule::unique('images')->where(function ($query) {
                    return $query->where('thread_id', $this->thread_id);
                })
            ],
        ];
    }
}
