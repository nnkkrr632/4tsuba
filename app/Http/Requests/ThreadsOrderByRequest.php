<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ThreadsOrderByRequest extends FormRequest
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
            'column' => [
                'required',
                'not_in:"null"',
                'in:"updated_at","created_at","posts_count","likes_count"',
            ],
            'desc_asc' => [
                'required',
                'not_in:"null"',
                'in:"desc","asc"',
            ],
        ];
    }
}
