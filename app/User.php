<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
   
    protected $guarded = [
      'id',
      'email_verified_at',
      'remember_token',
      'created_at',
      'updated_at',
      'deleted_at',
      'newsletter'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    /**
     * Gender set by the user.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function gender()
    {
      return $this->belongsTo('App\Gender');
    }
    
    /**
     * Referrer set by the user.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function referrer()
    {
      return $this->belongsTo('App\UserReferrer');
    }
    
    /**
     * Bikes belonging to the user.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bikes()
    {
      return $this->hasMany('App\Bike');
    }
    
    /**
     * Bikes the user can edit that they don't own.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function editableBikes()
    {
      return $this->belongsToMany('App\Bike','bike_editors','user_id','bike_id');
    }
    
    /**
     * Reservations belonging to the user.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reservations()
    {
      return $this->hasMany('App\BikeReservation');
    }
    
    /**
     * Embeds belonging to the user.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function embeds()
    {
      return $this->hasMany('App\Embed');
    }
    
    /**
     * Chats belonging to the user.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function chats()
    {
      return $this->belongsToMany('App\Chat');
    }
    
    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
      return "{$this->firstname} {$this->lastname}";
    }    
    
    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
      $this->notify(new ResetPasswordNotification($token));
    }
}
