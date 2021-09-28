<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\RegularExpressionRule;
use Illuminate\Contracts\Validation\Validator;

class EditProfileRequest extends FormRequest
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
        ];
    }

    public function withValidator(Validator $validator)
    {
        //画像用
        $validator->sometimes('icon', 'image|mimes:jpeg,jpg,png,gif|max:3000', function ($input) {
            return $input->icon;
        });
    }


    public function messages()
    {
        $regular_expression_rule = new RegularExpressionRule();
        return [
            'name.required' => '【表示名】入力必須です。',
            'name.not_in' => '【表示名】入力必須です。(not_in)',
            'name.between' => '【表示名】1文字~20文字で入力してください。',
            'name.regex' => '【表示名】' . $regular_expression_rule->message()[0],
            'icon.image' => '【画像】画像ファイルを指定してください。',
            'icon.mimes' => '【画像】「jpeg」「jpg」「png」「gif」形式に対応しています。',
            'icon.max' => '【画像】3.0MBを超える画像は添付できません。',
        ];
    }
}
