<?php

namespace App\Http\Requests;

use App\Rules\RegularExpressionRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use PHPUnit\Framework\Constraint\RegularExpression;
use App\Models\FormRequestMessage;

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
        $form_request_message = new FormRequestMessage();
        $heads = ['スレッドタイトル', '書込', '画像'];
        return [
            'title.required' => $form_request_message->required($heads[0]),
            'title.not_in' => $form_request_message->not_in($heads[0]),
            'title.regex' => $form_request_message->forbidHtmlTag($heads[0]),
            'title.between' => $form_request_message->between(1, 20, $heads[0]),
            'body.required' => $form_request_message->required($heads[1]),
            'body.not_in' => $form_request_message->not_in($heads[1]),
            'body.between' => $form_request_message->between(1, 200, $heads[1]),
            'body.regex' => $form_request_message->forbidHtmlTag($heads[1]),
            'image.image' => $form_request_message->image($heads[2]),
            'image.mimes' => $form_request_message->imageMime($heads[2]),
            'image.max' => $form_request_message->imageMaxSize($heads[2]),
        ];
    }
}
