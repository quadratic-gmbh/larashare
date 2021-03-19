<?php

namespace App;

use App\Traits\GetIdByField;
use App\Traits\GetSelectOptions;
use Illuminate\Database\Eloquent\Model;

class PricingRate extends Model
{
  use GetIdByField;
  use GetSelectOptions;
  
  const HOURLY = 'HOURLY';
  const DAILY = 'DAILY';
  const WEEKLY = 'WEEKLY';
  
  public $timestamps = false;
  
  /**
   * Get translation prefix.
   * 
   * @return string
   */
  private static function getSelectOptionsTranslationPrefix()
  {
    return 'bike.pricing_rate.';
  }
  
  /**
   * Get hourly id.
   * 
   * @return mixed
   */
  public static function getHourlyId()
  {
    return self::getIdByField(self::HOURLY);
  }
  
  /**
   * Get daily id.
   * 
   * @return mixed
   */
  public static function getDailyId()
  {
    return self::getIdByField(self::DAILY);
  }
  
  /**
   * Get weekly id.
   * 
   * @return mixed
   */
  public static function getWeeklyId()
  {
    return self::getIdByField(self::WEEKLY);
  }
  
}
