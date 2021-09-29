<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\FormRequestMessage;

class DestroyPIRequest extends FormRequest
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
                // 'exists:posts,id' は不要
                // Rule::exists('posts')->where(function ($query) {
                //     return $query->where('user_id', Auth::id())->orWhereIn('user_id', function ($query) {
                //         $query->select('id')->from('users')->where('users.role', 'staff');
                //     });
                // })
                Rule::exists('posts')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                })
            ],
        ];
    }
    public function messages()
    {
        $form_request_message = new FormRequestMessage();
        $head = '書込';
        return [
            'id.required' => $form_request_message->cancel($head),
            'id.not_in' => $form_request_message->cancel($head),
            'id.numeric' => $form_request_message->cancel($head),
            'id.exists' => $form_request_message->onlyOwnerCanDestroy($head),
        ];
    }
}
