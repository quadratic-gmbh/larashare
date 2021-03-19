<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RentalPeriodExceptionRenteeLimitation extends Model
{
  
  protected $guarded = [];
  
  public $timestamps = false;
  
  /**
   * Rental period exception the rentee limitation belongs to.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function rentalPeriod()
  {
    return $this->belongsTo('App\RentalPeriodException');
  }
  
}
