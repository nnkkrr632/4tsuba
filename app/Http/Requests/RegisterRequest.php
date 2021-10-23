<?php

namespace App\Http\Requests;

use App\Models\FormRequestMessage;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\RegularExpressionRule;

class RegisterRequest extends FormRequest
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
            'name' => [
                'required',
                'not_in:"null"',
                'between:1,20',
                $regular_expression_rule->forbidHtmlTag(),
            ],
            'email' => [
                'required',
                'not_in:"null"',
                'email',
                'unique:users,email',
                'between:1,50',
            ],
            'password' => [
                'required',
                'not_in:"null"',
                $regular_expression_rule->redefineAlphaNum(),
            ],
            'password_confirm' => [
                'required',
                'not_in:"null",',
                'same:password',
            ]
        ];
    }

    public function messages()
    {
        $form_request_message = new FormRequestMessage();
        $heads = ['表示名', 'メールアドレス', 'パスワード', 'パスワード確認'];
        return [
            'name.required' => $form_request_message->required($heads[0]),
            'name.not_in' => $form_request_message->not_in($heads[0]),
            'name.between' => $form_request_message->between(1, 20, $heads[0]),
            'name.regex' => $form_request_message->forbidHtmlTag($heads[0]),
            'email.required' => $form_request_message->required($heads[1]),
            'email.not_in' => $form_request_message->not_in($heads[1]),
            'email.email' => $form_request_message->email($heads[1]),
            'email.unique' => $form_request_message->emailAlreadyRegistered($heads[1]),
            'email.between' => $form_request_message->between(1, 50, $heads[1]),
            'password.required' => $form_request_message->required($heads[2]),
            'password.not_in' => $form_request_message->not_in($heads[2]),
            'password.regex' => $form_request_message->passwordRule($heads[2]),
            'password_confirm.required' => $form_request_message->required($heads[3]),
            'password_confirm.not_in' => $form_request_message->not_in($heads[3]),
            'password_confirm.same' => $form_request_message->passwordConfirm($heads[3]),
        ];
    }
}
