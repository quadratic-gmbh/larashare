<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RentalPeriodException extends Model
{
  protected $dates = [
    'date_time_from',
    'date_time_to'  
  ];
  
  protected $casts = [
    'available' => 'boolean',
    'rental_duration_in_days' => 'boolean',
    'no_interrupt' => 'boolean',
  ];
  
  protected $guarded = [];
  
  protected $attributes = [
    'available' => false,    
    'no_interrupt' => false,
    'rental_duration_in_days' => false,
  ];
  
  /**
   * Weekdays for the rental period exception.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function weekday()
  {
    return $this->belongsTo('App\Weekday');
  }
  
  /**
   * The bike the rental period exception belongs to.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function bike()
  {
    return $this->belongsTo('App\Bike');
  }
  
  /**
   * Rental place the rental period exception belongs to.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function rentalPlace()
  {
    return $this->belongsTo('App\RentalPlace');
  }
  
  /**
   * Rentee limitations for the rental period exception.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function renteeLimitations()
  {
    return $this->hasMany('App\RentalPeriodExceptionRenteeLimitation');
  }

  /**
   * Scope query by bike and date.
   * 
   * @param unknown $query
   * @param Bike $bike
   * @param Carbon $date
   * @return query where
   */
  public function scopeByBikeAndDate($query, Bike $bike, Carbon $date)
  {
    return $query->where([
      ['bike_id','=',$bike->id],
      [DB::Raw('DATE(date_time_from)'),'=',$date->format('Y-m-d')],
    ])
    ->orderBy('date_time_from','ASC');
  }
}
