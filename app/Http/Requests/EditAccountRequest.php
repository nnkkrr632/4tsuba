<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\RegularExpressionRule;
use App\Models\FormRequestMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class EditAccountRequest extends FormRequest
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
                Rule::unique('users')->ignore(Auth::id()),
                'between:1,100',
            ],
            'current_password' => [
                'required',
                'not_in:"null"',
                $regular_expression_rule->redefineAlphaNum(),
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
        $heads = ['メールアドレス', '現在のパスワード', '新しいパスワード', '新しいパスワード(確認)'];

        return [
            'email.required' => $form_request_message->required($heads[0]),
            'email.not_in' => $form_request_message->not_in($heads[0]),
            'email.email' => $form_request_message->email($heads[0]),
            'email.unique' => $form_request_message->emailAlreadyRegistered($heads[0]),
            'email.between' => $form_request_message->between(1, 100, $heads[0]),
            'current_password.required' => $form_request_message->required($heads[1]),
            'current_password.not_in' => $form_request_message->not_in($heads[1]),
            'current_password.regex' => $form_request_message->passwordRule($heads[1]),
            'password.required' => $form_request_message->required($heads[2]),
            'password.not_in' => $form_request_message->not_in($heads[2]),
            'password.regex' => $form_request_message->passwordRule($heads[2]),
            'password_confirm.required' => $form_request_message->required($heads[3]),
            'password_confirm.not_in' => $form_request_message->not_in($heads[3]),
            'password_confirm.same' => $form_request_message->passwordConfirm($heads[3]),
        ];
    }
}
