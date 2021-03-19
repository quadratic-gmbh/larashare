<?php 
namespace App\Services;

use Carbon\Carbon;

class InputFilter
{
  const TYPE_STR = 1;
  const TYPE_INT = 2;
  const TYPE_BOOL = 3;
  const TYPE_FLOAT = 4;
  const TYPE_DATE = 5;
  
  /**
   * Filters input.
   * 
   * @param array $rules
   * @param array $inputs
   */
  public function filter($rules, &$inputs) 
  {
    if(isset($rules['required'])) {
      $this->filterFields($rules['required'],$inputs);
    }
    
    if(isset($rules['nullable'])) {
      $this->filterFields($rules['nullable'],$inputs, true);
    }
  }    
  
  /**
   * Applies a single filter.
   * 
   * @param unknown $value
   * @param unknown $type
   * @param boolean $nullable
   * @return NULL|string
   */
  public function filterSingle($value, $type, $nullable = false) 
  {
    $filtered = null;
    if (!$nullable || $value != null) {      
      switch($type) {
        case self::TYPE_INT:
          $filtered = intval($value);
          break;
        case self::TYPE_BOOL:
          $filtered = boolval($value);
          break;
        case self::TYPE_FLOAT:
          $filtered = floatval($value);
          break;
        case self::TYPE_DATE:
          $filtered = new Carbon($value);
          break;
        default: // string
          $filtered = strip_tags($value);
      }
    }
    
    return $filtered;
  }
  
  /**
   * Applies filters.
   * 
   * @param unknown $rules
   * @param unknown $inputs
   * @param boolean $nullable
   */
  private function filterFields($rules, &$inputs, $nullable = false) 
  {    
    foreach($rules as $field => $type) {
      $inputs[$field] = $this->filterSingle($inputs[$field] ?? null, $type, $nullable);
    }        
  }
}