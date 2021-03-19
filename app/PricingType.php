<?php

namespace App;

use App\Traits\GetIdByField;
use Illuminate\Database\Eloquent\Model;

class PricingType extends Model
{
  use GetIdByField;
  
  const FREE = 'FREE';
  const DONATION = 'DONATION';
  const FREE_OR_DONATION = 'FREE_OR_DONATION';
  const FIXED = 'FIXED';
  
  public $timestamps = false;
  
  /**
   * Get id for free.
   * 
   * @return mixed
   */
  public static function getFreeId()
  {
    return self::getIdByField(self::FREE);
  }
  
  /**
   * Get id for donation.
   * 
   * @return mixed
   */
  public static function getDonationId()
  {
    return self::getIdByField(self::DONATION);
  }
  
  /**
   * Get id for free or donation.
   * 
   * @return mixed
   */
  public static function getFreeOrDonationId()
  {
    return self::getIdByField(self::FREE_OR_DONATION);
  }
  
  /**
   * Get id for fixed.
   * 
   * @return mixed
   */
  public static function getFixedId()
  {
    return self::getIdByField(self::FIXED);
  }
}
