<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Models\FormRequestMessage;

class DestroyMuteWordRequest extends FormRequest
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
        return [
            'id' => [
                'required',
                'not_in:"null"',
                'numeric',
                //↓のRuleで where id= xx and user_id = yy と勝手にやってくれるため
                // 'exists:mute_words,id' は不要
                Rule::exists('mute_words')->where(function ($query) {
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
            'id.required' => $form_request_message->cancel($head),
            'id.not_in' => $form_request_message->cancel($head),
            'id.numeric' => $form_request_message->cancel($head),
            'id.exists' => $form_request_message->cancel($head),
        ];
    }
}
