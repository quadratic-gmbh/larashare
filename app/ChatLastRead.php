<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChatLastRead extends Model
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
}
