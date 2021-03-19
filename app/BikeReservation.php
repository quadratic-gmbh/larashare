<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BikeReservation extends Model
{
  use SoftDeletes;
  
  protected $casts = [
    'survey_mail_sent' => 'boolean'
  ];
  
  protected $attributes= [
    'survey_mail_sent' => false
  ];
  
  protected $dates = [
    'reserved_from',
    'reserved_to',
    'confirmed_on'
  ];
  
  protected $guarded = [
    'id',
    'deleted_at',
    'updated_at',
    'created_at'
  ];
  
  /**
   * User the bike reservation belongs to.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function user()
  {
    return $this->belongsTo('App\User');    
  }
  
  /**
   * Bike the bike reservation belongs to.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function bike()
  {
    return $this->belongsTo('App\Bike');
  }
  
  /**
   * Remove buffer times from reservation.
   * 
   * @param BikeReservation $reservation
   */
  public static function removeBufferTimes($reservation)
  {
    if($reservation->buffer_time_before){
      $reservation->reserved_from = $reservation->reserved_from->addMinutes($reservation->buffer_time_before);
    }
    if($reservation->buffer_time_after){
      $reservation->reserved_to = $reservation->reserved_to->subMinutes($reservation->buffer_time_after);
    }
  }
}
