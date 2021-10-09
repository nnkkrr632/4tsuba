<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\RegularExpressionRule;
use App\Models\FormRequestMessage;

class LoginRequest extends FormRequest
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
            'email' => [
                'required',
                'not_in:"null"',
                'email:strict,dns,spoof',
                'exists:users,email',
                'between:1,50',
            ],
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
        $heads = ['メールアドレス', 'パスワード'];

        return [
            'email.required' => $form_request_message->required($heads[0]),
            'email.not_in' => $form_request_message->not_in($heads[0]),
            'email.email' => $form_request_message->email($heads[0]),
            'email.exists' => $form_request_message->emailNotRegistered($heads[0]),
            'email.between' => $form_request_message->between(1, 50, $heads[0]),
            'password.required' => $form_request_message->required($heads[1]),
            'password.not_in' => $form_request_message->not_in($heads[1]),
            'password.regex' => $form_request_message->passwordRule($heads[1]),
        ];
    }
}
