<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RentalPeriod extends Model
{

    protected $guarded = [
      'id',       
    ];
    
    protected $casts = [
      'date_from' => 'date',
      'date_to' => 'date',
      'rental_duration_in_days' => 'boolean',
      'no_interrupt' => 'boolean',
    ];
    
    protected $attributes = [
      'no_interrupt' => false,
      'rental_duration_in_days' => false,
    ];
    
    /**
     * Rental place for the rental period.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function rentalPlace()
    {
      return $this->belongsTo('App\RentalPlace');
    }
    
    /**
     * Rental mode for the rental period.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function rentalMode()
    {
      return $this->belongsTo('App\RentalMode');
    }
    
    /**
     * Weekdays for the rental period.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function weekdays()
    {
      return $this->belongsToMany('App\Weekday');
    }
    
    /**
     * Rentee limitations for the rental period.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function renteeLimitations()
    {
      return $this->hasMany('App\RentalPeriodRenteeLimitation');
    }
    
    /**
     * Get time to attribute.
     * 
     * @param unknown $value
     * @return \Carbon\Carbon
     */
    public function getTimeToAttribute($value)
    {
      return Carbon::createFromFormat('H:i:s', $value);
    }
    
    /**
     * Get time from attribute.
     * 
     * @param unknown $value
     * @return \Carbon\Carbon
     */
    public function getTimeFromAttribute($value)
    {
      return Carbon::createFromFormat('H:i:s', $value);
    }
    
}
