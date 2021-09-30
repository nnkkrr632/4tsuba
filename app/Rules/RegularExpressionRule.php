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
        //半角英字と数字の両方を少なくとも1つは含む & 記号可 & 8~24文字
        return 'regex: /^(?=.*[0-9])(?=.*[a-zA-Z])[ -~]{8,24}$/';
    }
    //implementの影響でmessage()メソッドを持たなくてはならないらしい
    public function message()
    {
        //
    }
}
