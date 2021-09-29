<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\RegularExpressionRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\FormRequestMessage;

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
        $form_request_message = new FormRequestMessage();
        $head = 'ミュートワード';
        return [
            'mute_word.required' => $form_request_message->required($head),
            'mute_word.not_in' => $form_request_message->not_in($head),
            'mute_word.between' => $form_request_message->between(1, 10, $head),
            'mute_word.regex' => $form_request_message->forbidHtmlTag($head),
            'mute_word.unique' => $form_request_message->muteWordAlreadyRegistered($head),
        ];
    }
}
