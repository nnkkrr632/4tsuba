<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Models\FormRequestMessage;

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
        $form_request_message = new FormRequestMessage();
        $heads = ['スレッド', '書込'];
        return [
            'thread_id.required' => $form_request_message->cancel($heads[0]),
            'thread_id.not_in' => $form_request_message->cancel($heads[0]),
            'thread_id.numeric' => $form_request_message->cancel($heads[0]),
            'thread_id.exists' => $form_request_message->cancel($heads[0]),
            'post_id.required' => $form_request_message->cancel($heads[1]),
            'post_id.not_in' => $form_request_message->cancel($heads[1]),
            'post_id.numeric' => $form_request_message->cancel($heads[1]),
            'post_id.exists' => $form_request_message->cancel($heads[1]),
            'post_id.unique' => $form_request_message->cancel($heads[1]),
        ];
    }
}
