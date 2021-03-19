<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class DecimalRule implements Rule
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
       $matches = preg_match("/^\d{1,8}(?:[\.\,]\d{0,2})?$/", $value);       
       return $matches === 1;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.custom.decimal_format');
    }
}
