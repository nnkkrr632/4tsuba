<?php

namespace App\Http\Requests;

use App\Rules\RegularExpressionRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use PHPUnit\Framework\Constraint\RegularExpression;
use Illuminate\Validation\Rule;


class StoreTPIRequest extends FormRequest
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
                'exclude_unless:title,""',
                'required',
                'not_in:"null"',
                'numeric',
                'exists:threads,id'
            ],
            'body' => [
                'between:1,200',
                'required',
                //vueでやるとき、requiredが効かないようで、not_in:"null"で代用する
                'not_in:"null"',
                $regular_expression_rule->forbidHtmlTag(),
            ],
        ];
    }
    public function withValidator(Validator $validator)
    {
        $regular_expression_rule = new RegularExpressionRule();
        //画像用
        $validator->sometimes('image', 'image|mimes:jpeg,jpg,png,gif|max:3000', function ($input) {
            return $input->image;
        });
        //スレッド用
        $validator->sometimes('title', 'required|not_in:"null"|between:1,20|' . $regular_expression_rule->forbidHtmlTag(), function ($input) {
            return $input->title;
        });
    }


    public function messages()
    {
        $regular_expression_rule = new RegularExpressionRule();
        return [
            'title.required' => '【スレッドタイトル】入力必須です。',
            'title.not_in' => '【スレッドタイトル】入力必須です(not_in)。',
            'title.regex' => '【スレッドタイトル】' . $regular_expression_rule->message()[0],
            'title.between' => '【スレッドタイトル】1文字~20文字で入力してください。',
            'body.required' => '【書込】入力必須です。',
            'body.not_in' => '【書込】入力必須です(not_in)。',
            'body.between' => '【書込】1文字~200文字で入力してください。',
            'body.regex' => '【書込】' . $regular_expression_rule->message()[0],
            'image.image' => '【画像】画像ファイルを指定してください。',
            'image.mimes' => '【画像】「jpeg」「jpg」「png」「gif」形式に対応しています。',
            'image.max' => '【画像】3.0MBを超える画像は添付できません。',
        ];
    }
}
