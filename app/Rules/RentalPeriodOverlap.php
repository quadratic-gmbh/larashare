<?php

namespace App\Rules;

use App\Bike;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RentalPeriodOverlap implements Rule
{
    private $data;
    private $bike;
    
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($data, Bike $bike)
    {
      $this->data = $data;
      $this->bike = $bike;
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
      $id = $this->data['rp_id'];
      $time_from = $this->data['time_from'];
      $time_to = $this->data['time_to'];
      $date_from = $this->data['date_from'];
      $date_to = $this->data['date_to'];      
      
      $weekdays = [];
      for ($i = 1; $i <= 7; $i++) {
        $val = $this->data['weekday'][$i] ?? false;
        if (boolval($val)) {
          $weekdays[] = $i;
        }
      }
      
      // find all rental periods that fit the dates
      $query = DB::table('bikes')
      ->join('rental_places','rental_places.bike_id','=','bikes.id')
      ->join('rental_periods','rental_periods.rental_place_id','=','rental_places.id')
      ->join('rental_period_weekday','rental_periods.id','=','rental_period_weekday.rental_period_id')
      ->whereIn('rental_period_weekday.weekday_id',$weekdays)
      ->where([
        ['bikes.id','=',$this->bike->id],
        ['rental_places.deleted_at','=',null]
      ])      
      ->whereNested(function ($query) use($date_from, $date_to) {
        $query->where('rental_periods.date_to','<',$date_from);
        $query->orWhere('rental_periods.date_from','>',$date_to);
      },'and not')
      ->whereNested(function($query) use($time_from, $time_to) {
        $query->where('rental_periods.time_from','>=',$time_to);
        $query->orWhere('rental_periods.time_to','<=',$time_from);
      },'and not');
      
      if ($id !== null) {
       $query->where('rental_periods.id','<>',$id); 
      }
      
      $query->select([
        'rental_periods.id'        
      ])
      ->groupBy('rental_periods.id');
            
      // if empty no conflicts found
      return !$query->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.custom.rental_period_overlap');
    }
}
