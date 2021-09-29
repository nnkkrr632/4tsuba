<?php

namespace App\Http\Requests;

use App\Models\FormRequestMessage;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\RegularExpressionRule;

class DestroyAccountRequest extends FormRequest
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
        $regular_expression_rule = new RegularExpressionRule();

        return [
            'password' => [
                'required',
                'not_in:"null"',
                $regular_expression_rule->redefineAlphaNum(),
            ],

        ];
    }
    public function messages()
    {
        $form_request_message = new FormRequestMessage();
        $head = 'パスワード';
        return [
            'password.required' => $form_request_message->required($head),
            'password.not_in' => $form_request_message->not_in($head),
            'password.regex' => $form_request_message->passwordRule($head),
        ];
    }
}
