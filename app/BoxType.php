<?php

namespace App;

use App\Traits\GetIdByField;
use App\Traits\GetSelectOptions;
use Illuminate\Database\Eloquent\Model;

class BoxType extends Model
{
  use GetIdByField;
  use GetSelectOptions;
 
  const NO_BOX = 'NO_BOX';
  const LOCKABLE = 'LOCKABLE';
  const NON_LOCKABLE = 'NON_LOCKABLE';
 
  public $timestamps = false;
  
  /**
   * Returns translation prefix.
   * 
   * @return string
   */
  private static function getSelectOptionsTranslationPrefix()
  {
    return 'bike.box_type.';
  }
  
  /**
   * Returns id mapping.
   * 
   * @return mixed[]
   */
  public static function getIdMapping()
  {
    $map = [];
    $fields = [self::NO_BOX, self::LOCKABLE, self::NON_LOCKABLE];
    foreach($fields as $field) {
      $map[$field] = self::getIdByField($field); 
    }    
    return $map;
  }
  
  /**
   * Returns id for lockable.
   * 
   * @return mixed
   */
  public static function getLockableId()
  {
    return self::getIdByField(self::LOCKABLE);
  }
  
  /**
   * Returns id for non lockable.
   * 
   * @return mixed
   */
  public static function getNonLockableId()
  {
    return self::getIdByField(self::NON_LOCKABLE);
  }
  
  /**
   * Returns id for no box.
   * 
   * @return mixed
   */
  public static function getNoBoxId()
  {
    return self::getIdByField(self::NO_BOX);
  }
}
