<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $guarded = [
    'id',
    'created_at',
    'updated_at',
  ];
  
  /**
   * Get created_at converted to Europe/Vienna timezone.
   * 
   * @return DateTime
   */
  public function getDateTimeAttribute()
  {
    $date_time = $this->created_at;
    $date_time->setTimezone('Europe/Vienna');
    return $date_time;
  }
  
  /**
   * User the chat message belongs to.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function sender()
  {
    return $this->belongsTo('App\User','user_id');
  }
  
}
