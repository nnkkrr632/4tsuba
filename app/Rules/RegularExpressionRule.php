<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use phpDocumentor\Reflection\Types\Static_;

class RegularExpressionRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        //return 
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function forbidHtmlTag()
    {
        return 'regex: /^(?!.*<.{1,40}>).*$/s';
    }
    public function redefineAlphaNum()
    {
        //半角英数字「混合で」8~24文字
        return 'regex: /^(?=.*[0-9])(?=.*[a-zA-Z])[a-zA-Z0-9]{8,24}$/';
    }
    //implementの影響でmessage()メソッドを持たなくてはならないらしい
    public function message()
    {
        return ['HTMLタグを入力できません。', '半角英数字混合の8~24文字でお願いいたします。'];
    }
}
