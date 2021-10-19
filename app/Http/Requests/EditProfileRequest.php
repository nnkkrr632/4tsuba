<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\RegularExpressionRule;
use Illuminate\Contracts\Validation\Validator;
use App\Models\FormRequestMessage;

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
            'icon' => [
                'sometimes',
                'required',
                'not_in:"null"',
                'image',
                'mimes:jpeg,jpg,png,gif',
                'max:3000',
            ],

        ];
    }

    // public function withValidator(Validator $validator)
    // {
    //     //画像用
    //     $validator->sometimes('icon', 'image|mimes:jpeg,jpg,png,gif|max:3000', function ($input) {
    //         return $input->icon;
    //     });
    // }


    public function messages()
    {
        $form_request_message = new FormRequestMessage();
        $heads = ['表示名', 'アイコン'];
        return [
            'name.required' => $form_request_message->required($heads[0]),
            'name.not_in' => $form_request_message->not_in($heads[0]),
            'name.between' => $form_request_message->between(1, 20, $heads[0]),
            'name.regex' => $form_request_message->forbidHtmlTag($heads[0]),
            'icon.required' => $form_request_message->required($heads[1]),
            'icon.not_in' => $form_request_message->not_in($heads[1]),
            'icon.image' => $form_request_message->image($heads[1]),
            'icon.mimes' => $form_request_message->imageMime($heads[1]),
            'icon.max' => $form_request_message->imageMaxSize($heads[1]),
        ];
    }
}
