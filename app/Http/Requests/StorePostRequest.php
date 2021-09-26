<?php

namespace App\Http\Requests;

use App\Rules\RegularExpressionRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use PHPUnit\Framework\Constraint\RegularExpression;
use Illuminate\Validation\Rule;


class StorePostRequest extends FormRequest
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
            'thread_id' => [
                'required',
                'numeric',
                'exists:threads,id'
            ],
            'body' => [
                'required',
                'between:1,200',
                $regular_expression_rule->forbidHtmlTag()
            ],
            // 'post_id' => [
            //     'sometimes',
            //     'required',
            //     'numeric',
            //     'exists:posts,id',
            //     //imagesテーブルthread_id列との複合ユニーク
            //     Rule::unique('images')->where(function ($query) {
            //         return $query->where('thread_id', $this->thread_id);
            //     })
            // ],


        ];
    }

    public function attributes()
    {
        return [
            'body' => '書込',
        ];
    }
    public function withValidator(Validator $validator)
    {
        $validator->sometimes('image', 'required|image|mimes:jpeg,jpg,png,gif,svg', function ($input) {
            return $input->image;
        });
    }

    public function messages()
    {
        $regular_expression_rule = new RegularExpressionRule();
        return [
            'body.required' => '入力必須です。',
            'body.between' => '1文字~200文字で入力してください。',
            'body.regex' => $regular_expression_rule->message()
        ];
    }
}
