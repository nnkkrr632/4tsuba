<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\RegularExpressionRule;

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
                'between:1,100',
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
        $regular_expression_rule = new RegularExpressionRule();
        return [
            'email.required' => '【メールアドレス】入力必須です。',
            'email.not_in' => '【メールアドレス】入力必須です。(not_in)',
            'email.email' => '【メールアドレス】メールアドレス形式で入力してください。',
            'email.exists' => '【メールアドレス】未登録のメールアドレスです。',
            'email.between' => '【メールアドレス】1文字~100文字で入力してください。',
            'password.required' => '【パスワード】入力必須です。',
            'password.not_in' => '【パスワード】入力必須です(not_in)。',
            'password.regex' => '【パスワード】' . $regular_expression_rule->message()[1],
        ];
    }
}
