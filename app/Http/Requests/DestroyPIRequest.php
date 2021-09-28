<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
        return [
            'id.required' => '送信値の変更を検知したためキャンセルしました。',
            'id.not_in' => '送信値の変更を検知したためキャンセルしました。',
            'id.numeric' => '送信値の変更を検知したためキャンセルしました。',
            'id.exists' => '書込者以外は削除できません。',
        ];
    }
}
