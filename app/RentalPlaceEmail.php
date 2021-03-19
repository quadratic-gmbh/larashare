<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RentalPlaceEmail extends Model
{ 
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

  protected $casts = [
    'notify_on_reservation' => 'boolean'
  ];

  /**
   * Rental place the email belongs to.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function rentalPlace()
  {
    return $this->belongsTo('App\RentalPlace');
  } 
}
