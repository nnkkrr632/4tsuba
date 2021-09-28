<?php

namespace App\Http\Requests;

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
                'between:1,10',
                $regular_expression_rule->forbidHtmlTag(),
            ],
            'email' => [
                'required',
                'not_in:"null"',
                'email:strict,dns,spoof',
                'unique:users,email',
                'between:1,100',
            ],
            'password' => [
                'required',
                'not_in:"null"',
                $regular_expression_rule->redefineAlphaNum(),
                'min:8',
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
        $regular_expression_rule = new RegularExpressionRule();
        return [
            'name.required' => '【表示名】入力必須です。',
            'name.not_in' => '【表示名】入力必須です。(not_in)',
            'name.between' => '【表示名】1文字~10文字で入力してください。',
            'name.regex' => '【表示名】' . $regular_expression_rule->message()[0],
            'email.required' => '【メールアドレス】入力必須です。',
            'email.not_in' => '【メールアドレス】入力必須です。(not_in)',
            'email.email' => '【メールアドレス】メールアドレス形式で入力してください。',
            'email.unique' => '【メールアドレス】既に登録済みのメールアドレスです。',
            'email.between' => '【メールアドレス】1文字~100文字で入力してください。',
            'password.required' => '【パスワード】入力必須です。',
            'password.not_in' => '【パスワード】入力必須です(not_in)。',
            'password.regex' => '【パスワード】' . $regular_expression_rule->message()[1],
            'password.min' => '【パスワード】8文字以上でお願いします。',
            'password_confirm.required' => '【パスワード確認】入力必須です。',
            'password_confirm.not_in' => '【パスワード確認】入力必須です(not_in)。',
            'password_confirm.same' => '【パスワード確認】パスワードと一致しません。',
        ];
    }
}
