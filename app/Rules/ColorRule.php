<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ColorRule implements Rule
{
    const REGEX_PATTERN = '/#([0-9]|[a-f]){6}/i';
    
    private $message = null;
    
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
      if (!preg_match(self::REGEX_PATTERN, $value)) {
        $this->message = __('validation.custom.color_not_matching',['value' => $value]);
        return false;
      }
      
      return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
