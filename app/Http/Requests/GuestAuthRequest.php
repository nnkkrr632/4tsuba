<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\FormRequestMessage;

class GuestAuthRequest extends FormRequest
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
            'user_id' => [
                'in:1,2,3',
            ],
        ];
    }

    public function messages()
    {
        $form_request_message = new FormRequestMessage();
        $head = 'ゲストユーザー';

        return [
            'user_id.in' => $form_request_message->cancel($head),
        ];
    }
}
