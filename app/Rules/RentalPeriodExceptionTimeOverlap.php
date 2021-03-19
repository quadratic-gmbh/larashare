<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;

class RentalPeriodExceptionTimeOverlap implements Rule
{
    private $timespans;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($timespans)
    {
      $this->timespans = $timespans;
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
        $parts = explode('.', $attribute);    
        $name = $parts[0];
        $index = $parts[0];
        $curr = Carbon::createFromFormat('H:i', $value);
        // check if any timespans overlap
        foreach($this->timespans as $timespan) {
          if ($timespan['index'] == $index // ignore current timespan or invalid timespans
            || $timespan['time_from'] == null 
            || $timespan['time_to'] == null) {
            continue;            
          }
          $time_from = Carbon::createFromFormat('H:i', $timespan['time_from']);
          $time_to = Carbon::createFromFormat('H:i', $timespan['time_to']);
          
          if ($curr < $time_to && $curr > $time_from) {
            return false;
          }
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
        return __('validation.custom.rental_period_exception_time_overlap');
    }
}
