<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class DestroyLikeRequest extends FormRequest
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
            'thread_id' => [
                'required',
                'exists:threads,id'
            ],
            'post_id' => [
                'required',
                //↓で実際のSQLでは「where post_id = xx, and user_id = yy」となっているため、
                //「'exists:likes,post_id'」を指定しないことにした
                Rule::exists('likes')->where(function ($query) {
                    $query->where('user_id', Auth::id());
                }),
            ]
        ];
    }
}
