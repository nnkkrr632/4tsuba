<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class StoreMuteUserRequest extends FormRequest
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
        $my_id = Auth::id();
        return [
            'user_id' => [
                'required',
                'not_in:"null",' . $my_id,
                'numeric',
                'exists:users,id',
                //↓のRuleで where user_id= xx and muting_user_id = yy と勝手にやってくれるため
                // 'exists:mute_users,user_id' は不要
                Rule::unique('mute_users')->where(function ($query) use ($my_id) {
                    return $query->where('muting_user_id', $my_id);
                })

            ],
        ];
    }
    public function messages()
    {
        return [
            'user_id.required' => '送信値の変更を検知したためキャンセルしました。',
            'user_id.not_in' => '自分をミュートすることはできません。',
            'user_id.numeric' => '送信値の変更を検知したためキャンセルしました。',
            'user_id.exists' => '送信値の変更を検知したためキャンセルしました。',
            'user_id.unique' => '既にミュート済みです。',
        ];
    }
}
