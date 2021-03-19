<?php

namespace App;

use App\Traits\GetIdByField;
use Illuminate\Database\Eloquent\Model;

class ChatType extends Model
{
  use GetIdByField;
  
  const BIKE = 'BIKE';
  
  public $timestamps = false;
  
  /**
   * Return bike id.
   * 
   * @return mixed
   */
  public static function getBikeId()
  {
    return self::getIdByField(self::BIKE);
  }
}
