<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
  const UPDATED_AT = null;
  
  const SZ_50 = 1;
  const SZ_150 = 2;
  const SZ_300 = 3;
  const SZ_1000 = 4;
  
  protected $guarded = [];    
  
  /**
   * Get image url.
   * 
   * @param int $sz
   * @return string
   */
  public function getUrl($sz = self::SZ_150)
  {
    return self::getUrlById($this->id, $sz);
  }
  
  /**
   * Get image url by id.
   * 
   * @param int $id
   * @param int $sz
   * @return string
   */
  public static function getUrlById(int $id, $sz = self::SZ_300)
  {
    $path_str = "storage/images/";
    switch($sz) {
      case self::SZ_50:
        $path_str .= 50;
        break;
      case self::SZ_300:
        $path_str .= 300;
        break;
      case self::SZ_1000:
        $path_str .= 1000;
        break;
      default:
        $path_str .= 150;
    }
    
    $path_str .= "/{$id}.jpg";
    
    return asset($path_str);
  }
}
