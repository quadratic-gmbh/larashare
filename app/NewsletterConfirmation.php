<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NewsletterConfirmation extends Model
{
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $guarded = [
    'created_at',
    'updated_at'
  ];
  
}
