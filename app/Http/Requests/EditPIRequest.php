<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use PHPUnit\Framework\Constraint\RegularExpression;
use App\Rules\RegularExpressionRule;
use Illuminate\Validation\Rule;
use App\Models\FormRequestMessage;
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
            'displayed_post_id' => [
                'required',
                'not_in:"null"',
                'numeric',
                //$thisで取り出すことができる。コントローラーでは$edit_pi_request->thread_idで
                //取り出しているが、それと同じ。自らのインスタンス(staticではない)では$thisで指せる。
                Rule::exists('posts')->where(function ($query) {
                    return $query->where('thread_id', $this->thread_id);
                })
            ],
            'body' => [
                'required',
                'not_in:"null"',
                'between:1,200',
                $regular_expression_rule->forbidHtmlTag(),
            ],
            'image' => [
                'sometimes',
                'required',
                'not_in:"null"',
                'max:3000',
            ],
            'delete_image' => [
                'sometimes',
                'required',
                'in:"true","false"',
            ],
        ];
    }

    // public function withValidator(Validator $validator)
    // {
    //     // //画像用
    //     //'image','mimes:~としたいところだが、画像の変更がない場合objectでくるため諦め
    //     $validator->sometimes('image', 'max:3000', function ($input) {
    //         return $input->image;
    //     });
    //     //画像削除用
    //     //in: "true", "false"ではなくbooleanを使いたかったが、案の定stringで評価されたのでinを使用
    //     $validator->sometimes('delete_image', 'in: "true", "false"', function ($input) {
    //         return $input->delete_image;
    //     });
    // }


    public function messages()
    {
        $regular_expression_rule = new RegularExpressionRule();
        $form_request_message = new FormRequestMessage();

        $heads = ['書込', '画像'];
        return [
            'id.required' => $form_request_message->cancel($heads[0]),
            'id.not_in' => $form_request_message->cancel($heads[0]),
            'id.numeric' => $form_request_message->cancel($heads[0]),
            'id.exists' => $form_request_message->OnlyOwnerCanEdit($heads[0]),
            'thread_id.required' => $form_request_message->cancel($heads[0]),
            'thread_id.not_in' => $form_request_message->cancel($heads[0]),
            'thread_id.numeric' => $form_request_message->cancel($heads[0]),
            'thread_id.exists' => $form_request_message->OnlyOwnerCanEdit($heads[0]),
            'displayed_post_id.required' => $form_request_message->cancel($heads[0]),
            'displayed_post_id.not_in' => $form_request_message->cancel($heads[0]),
            'displayed_post_id.numeric' => $form_request_message->cancel($heads[0]),
            'displayed_post_id.exists' => $form_request_message->OnlyOwnerCanEdit($heads[0]),
            'body.required' => $form_request_message->required($heads[0]),
            'body.not_in' => $form_request_message->not_in($heads[0]),
            'body.between' => $form_request_message->between(1, 200, $heads[0]),
            'body.regex' => $form_request_message->forbidHtmlTag($heads[0]),
            'image.required' => $form_request_message->required($heads[1]),
            'image.not_in' => $form_request_message->not_in($heads[1]),
            'image.max' => $form_request_message->imageMaxSize($heads[1]),
            'delete_image.in' => $form_request_message->cancel($heads[0]),
        ];
    }
}
