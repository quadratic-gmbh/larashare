<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Bike extends Model
{
  use SoftDeletes;
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $guarded = [
    'id',
    'accepts_tos',
    'has_permission',
    'public',
    'deleted_at',
    'updated_at',
    'created_at',
    'no_tos_upload'
  ];
  
  protected $casts = [
    'electric' => 'boolean',        
    'terms_of_use_file' => 'boolean',
    'accepts_tos' => 'boolean',
    'has_permission' => 'boolean',
    'public' => 'boolean',
    'no_tos_upload' => 'boolean',
    'kel_bike' => 'boolean',
    'pricing_values' => 'array'
  ];
  
  protected $dates = [
    'rp_reminder_at'
  ];  
  
  /**
   * User the bike belongs to.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function owner()
  {
    return $this->belongsTo('App\User','user_id');
  }
  
  /**
   * Users that can edit the bike.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
   */
  public function editors()
  { 
    return $this->belongsToMany('App\User','bike_editors','bike_id','user_id');
  }
  
  /**
   * Box type of the bike.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function boxType()
  {
    return $this->belongsTo('App\BoxType');
  }
  
  /**
   * Pricing type of the bike.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function pricingType()
  {
    return $this->belongsTo('App\PricingType');
  }
  
  /**
   * Pricing rate of the bike.
   * 
   * @deprecated
   * 
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function pricingRate()
  {
    return $this->belongsTo('App\PricingRate');
  }
  
  /**
   * Rental places for the bike.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function rentalPlaces()
  {
    return $this->hasMany('App\RentalPlace');
  }
  
  /**
   * Returns select options for the rental places.
   * 
   * @return \Illuminate\Support\Collection
   */
  public function rentalPlaceSelectOptions()
  {
    $rental_places = $this->rentalPlaces;        
    
    $rental_place_options = collect([]);
    foreach($rental_places as $rp) {
      $rental_place_options->push([
        'value' => $rp->id,
        'text' => $rp->name
      ]);
    }    
    
    return $rental_place_options;
  }
  
  /**
   * Rental periods for the bike.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
   */
  public function rentalPeriods()
  {
    return $this->hasManyThrough('App\RentalPeriod','App\RentalPlace');
  }
  
  /**
   * Rental period exceptions for the bike.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function rentalPeriodExceptions()
  {
    return $this->hasMany('App\RentalPeriodException');
  }  
  
  /**
   * Reservations for the bike.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function reservations()
  {
    return $this->hasMany('App\BikeReservation');
  }
  
  /**
   * Images for the bike.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
   */
  public function images()
  {
    return $this->belongsToMany('App\Image');
  }
  
  /**
   * Chats for the bike.
   * 
   * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
   */
  public function chats()
  {
    return $this->belongsToMany('App\Chat');
  }
  
  /**
   * Returns public scope.
   * 
   * @param unknown $query
   * @return unknown
   */
  public function scopePublic($query)
  {
    return $query->where('public',true);
  }
  
  /**
   * Returns kel_bike scope.
   * 
   * @param unknown $query
   * @return unknown
   */
  public function scopeIsKelBike($query)
  {
    return $query->where('kel_bike',true);
  }
  
  /**
   * Returns images for the bike.
   * 
   * @param unknown $bike_ids
   * @return \Illuminate\Support\Collection
   */
  public static function getImagesForBikes($bike_ids)
  {
    $bike_images = DB::table('bike_image')
    ->whereIn('bike_image.bike_id',$bike_ids)
    ->select([
      'bike_id',
      DB::raw('MIN(image_id) as image_id')
    ])
    ->groupBy([
      'bike_id'
    ])
    ->get()
    ->mapWithKeys(function($item) {
      return[$item->bike_id => $item->image_id];
    });
      
      return $bike_images;
  }
}
