<?php

namespace App;

use App\Traits\GetIdByField;
use App\Traits\GetSelectOptions;
use Illuminate\Database\Eloquent\Model;

class RentalMode extends Model
{
  use GetIdByField;
  use GetSelectOptions;
  
  const INQUIRY = 'INQUIRY';
  const INSTANT_RESERVATION = 'INSTANT_RESERVATION';  
  
  public $timestamps = false;
  
  /**
   * Get id for inquiry.
   * 
   * @return mixed
   */
  public static function getInquiryId()
  {
    return self::getIdByField(self::INQUIRY);
  }
  
  /**
   * Get id for reservation.
   * 
   * @return mixed
   */
  public static function getInstantReservationId()
  {
    return self::getIdByField(self::INSTANT_RESERVATION);
  }
     
  /**
   * Get translation prefix.
   * 
   * @return string
   */
  private static function getSelectOptionsTranslationPrefix()
  {
    return 'bike.rental_mode.';
  }
}
