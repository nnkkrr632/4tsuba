<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\FormRequestMessage;

class GetPostsRequest extends FormRequest
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
            'where' => [
                'required',
                'in:"thread_id","responses","user_id","user_like","search"',
            ],
            'value' => [
                'required',
                'not_in:"null",".*",".+"',
            ],

        ];
    }
    public function messages()
    {
        $form_request_message = new FormRequestMessage();
        $heads = ['where', 'value'];
        return [
            'where.required' => $form_request_message->cancel($heads[0]),
            'where.in' => $form_request_message->cancel($heads[0]),
            'value.required' => $form_request_message->cancel($heads[1]),
            'value.not_in' => $form_request_message->notUseRegularExpression($heads[1]),
        ];
    }
}
