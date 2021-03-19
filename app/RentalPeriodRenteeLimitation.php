<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RentalPeriodRenteeLimitation extends Model
{
  
  protected $guarded = [];
  
  public $timestamps = false;
  
  /**
   * Rental period the rentee limitation belongs to.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function rentalPeriod()
  {
    return $this->belongsTo('App\RentalPeriod');
  }
  
}
