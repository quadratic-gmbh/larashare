<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\File;

class Embed extends Model
{
    use SoftDeletes;
    
    protected $attributes = [
      'has_custom_css' => false //deprecated
    ];
    
    protected $guarded = [
      'id'      
    ];
    
    protected $casts = [
      'has_custom_css' => 'boolean', //deprecated
      'simple_css' => 'array',
      'defaults' => 'array'
    ];
    
    /**
     * User the embed belongs to.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
      return $this->belongsTo('App\User','user_id');  
    }
    
    /**
     * Bikes the embed shows.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function bikes()
    {
      return $this->belongsToMany('App\Bike');
    }    
    
    /**
     * Returns custom css path.
     * 
     * @deprecated
     * 
     * @return NULL|string
     */
    public function getCustomCssPathAttribute()
    {
      $path = "storage/embeds/{$this->id}.css";
      if (!$this->has_custom_css || !File::exists(public_path($path))) {
        return null;
      }
            
      return $path;
    }
}
