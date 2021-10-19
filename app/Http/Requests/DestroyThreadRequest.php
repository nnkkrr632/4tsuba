<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\FormRequestMessage;

class DestroyThreadRequest extends FormRequest
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
            'id' => [
                'required',
                'not_in:"null"',
                'numeric',
                //usersテーブルのidカラムに存在
                'exists:users,id',
            ],
        ];
    }
    public function messages()
    {
        $form_request_message = new FormRequestMessage();
        $head = 'スレッド';
        return [
            'id.required' => $form_request_message->cancel($head),
            'id.not_in' => $form_request_message->cancel($head),
            'id.numeric' => $form_request_message->cancel($head),
            'id.exists' => $form_request_message->cancel($head),
        ];
    }
}
