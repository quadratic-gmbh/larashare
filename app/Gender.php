<?php

namespace App;

use App\Traits\GetIdByField;
use App\Traits\GetSelectOptions;
use Illuminate\Database\Eloquent\Model;

class Gender extends Model
{
  use GetIdByField;
  use GetSelectOptions;
  
  const MALE = 'MALE';
  const FEMALE = 'FEMALE';
  const NOT_SPECIFIED = null;
  
  public $timestamps = false;
  
  /**
   * Returns the id for male.
   * 
   * @return mixed
   */
  public static function getMaleId()
  {
    return self::getIdByField(self::MALE);
  }
  
  /**
   * Returns the id for female.
   * 
   * @return mixed
   */
  public static function getFemaleId()
  {
    return self::getIdByField(self::FEMALE);
  }
  
  /**
   * Returns the id for not specified.
   * 
   * @return string
   */
  public static function getNotSpecifiedId()
  {
    return self::NOT_SPECIFIED;
  }
  
  /**
   * Get translation prefix.
   * 
   * @return string
   */
  private static function getSelectOptionsTranslationPrefix()
  {
    return 'user.gender.';
  }
  
  
}
