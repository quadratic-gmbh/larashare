<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $guarded = [
    'id',
    'created_at',
    'updated_at'
  ];
  
  /**
   * Chat type of the chat.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function chatType()
  {
    return $this->belongsTo('App\ChatType');
  }
  
  /**
   * Bike the chat belongs to.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
   */
  public function bikes()
  {
    return $this->belongsToMany('App\Bike');
  }
  
  /**
   * User the chat belongs to.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
   */
  public function users()
  {
    return $this->belongsToMany('App\User');
  }
  
  /**
   * Messages belonging to the chat.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function messages()
  {
    return $this->hasMany('App\ChatMessage');
  }
  
  /**
   * Last reads for the chat.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function lastReads()
  {
    return $this->hasMany('App\ChatLastRead');
  }
}
