<?php

namespace App;

use App\Events\RentalPlaceDeleted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentalPlace extends Model
{
  use SoftDeletes;
  
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $guarded = [
    'id',
    'updated_at',
    'created_at'
  ];
  
  
  /**
   * The event map for the model.
   *
   * @var array
   */
  protected $dispatchesEvents = [    
    'deleted' => RentalPlaceDeleted::class,
  ];

  /**
   * Bike that the rental place belongs to.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function bike()
  {
    return $this->belongsTo('App\Bike');
  }

  /**
   * Email addresses for the rental place.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function emails()
  {
    return $this->hasMany('App\RentalPlaceEmail');
  }

  /**
   * Rental periods belonging to the rental place.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function rentalPeriods()
  {
    return $this->hasMany('App\RentalPeriod');
  }
  
  /**
   * Rental period exceptions belonging to the rental place.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function rentalPeriodExceptions()
  {
    return $this->hasMany('App\RentalPeriodException');
  }
  
  /**
   * Returns the full address of the rental place.
   * 
   * @return string
   */
  public function getFullAddressAttribute()
  {
    return "{$this->postal_code} {$this->city}, {$this->street_name} {$this->house_number}";
  }     
}
