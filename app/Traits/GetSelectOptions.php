<?php

namespace App\Traits;

/**
 * retrieve all entries from database and returned mapped $id => $item
 * @package App\Traits
 */
trait GetSelectOptions
{
  /**
   * Get translation prefix.
   * 
   * @return string
   */
  private static function getSelectOptionsTranslationPrefix()
  {
    return '';
  }
  
  /**
   * Get select options.
   * 
   * @param string $field
   * @return string[][]|array[][]|NULL[][]
   */
  public static function getSelectOptions($field = 'name')
  {
    $entities = self::all();
    $translation_prefix = self::getSelectOptionsTranslationPrefix();
    
    $options = []; 
    foreach($entities as $ent) {      
      $text = ($translation_prefix !== '' ? __($translation_prefix . $ent->$field) : $ent->$field);
      $options[] = [
        'value' => $ent->id,
        'text' => $text
      ];
    }
    
    return $options;
  }
 
}