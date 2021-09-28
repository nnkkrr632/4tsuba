<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\RegularExpressionRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreMuteWordRequest extends FormRequest
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
            'mute_word' => [
                'between:1,10',
                'required',
                //vueでやるとき、requiredが効かないようで、not_in:"null"で代用する
                'not_in:"null"',
                $regular_expression_rule->forbidHtmlTag(),
                //mute_wordsテーブルuser_id列との複合ユニーク
                Rule::unique('mute_words')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                })

            ],
        ];
    }
    public function messages()
    {
        $regular_expression_rule = new RegularExpressionRule();
        return [
            'mute_word.required' => '入力必須です。',
            'mute_word.not_in' => '入力必須です(not_in)。',
            'mute_word.between' => '1文字~10文字で入力してください。',
            'mute_word.regex' => $regular_expression_rule->message(),
            'mute_word.unique' => '既に登録されています。',
        ];
    }
}
