<?php

namespace App\Http\Requests;

use App\Rules\RegularExpressionRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use PHPUnit\Framework\Constraint\RegularExpression;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class EditPIRequest extends FormRequest
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
            'id' => [
                'required',
                'not_in:"null"',
                'numeric',
                //↓のRuleで where id= xx and user_id = yy と勝手にやってくれるため
                // 'exists:posts,id' は不要
                Rule::exists('posts')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                })
            ],
            'thread_id' => [
                'required',
                'not_in:"null"',
                'numeric',
                'exists:threads,id'
            ],
            'body' => [
                'required',
                'not_in:"null"',
                'between:1,200',
                $regular_expression_rule->forbidHtmlTag(),
            ],
        ];
    }

    public function withValidator(Validator $validator)
    {
        //画像用
        //'image','mimes:~としたいところだが、画像の変更がない場合objectでくるため諦め
        $validator->sometimes('image', 'max:3000', function ($input) {
            return $input->image;
        });
        //画像削除用
        //in: "true", "false"ではなくbooleanを使いたかったが、案の定stringで評価されたのでinを使用
        $validator->sometimes('delete_image', 'in: "true", "false"', function ($input) {
            return $input->delete_image;
        });
    }


    public function messages()
    {
        $regular_expression_rule = new RegularExpressionRule();
        return [
            'id.required' => '送信値の変更を検知したためキャンセルしました。',
            'id.not_in' => '送信値の変更を検知したためキャンセルしました。',
            'id.numeric' => '送信値の変更を検知したためキャンセルしました。',
            'id.exists' => '書込者以外は変更できません。',
            'body.required' => '入力必須です。',
            'body.not_in' => '入力必須です(not_in)。',
            'body.between' => '1文字~200文字で入力してください。',
            'body.regex' => $regular_expression_rule->message()[0],
            'image.image' => '添付は画像ファイルを指定してください。',
            'image.mimes' => '画像ファイルは「jpeg」「jpg」「png」「gif」形式のみ可能です。',
            'image.max' => '3.0MBを超える画像は添付できません。',
            'delete_image.in' => '送信値の変更を検知したためキャンセルしました。',
        ];
    }
}
