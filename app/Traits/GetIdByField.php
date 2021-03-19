<?php
namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

trait GetIdByField
{
  
  /**
   * Get id by field.
   * 
   * @param unknown $value
   * @param string $field
   * @return mixed
   */
  public static function getIdByField($value, $field = 'name')
  {
    $class_name = Str::snake((new \ReflectionClass(get_called_class()))->getShortName());
    $cache_key = $class_name . '_' . $value;
    
    $entity_id = Cache::get($cache_key, function() use ($value, $field, $cache_key) {
      $entity = self::where($field,$value)->first();
      if ($entity === null) {        
        abort(500);
      }
      
      Cache::forever($cache_key, $entity->id);
      return $entity->id;
    });
      
    return $entity_id;
  }
}