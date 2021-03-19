<?php
/**
 * UserWarningManager.php
 */
namespace App\Services;
use App\Bike;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class UserWarningManager 
{  
  public function checkForWarnings(Request $request) 
  {
    /**
     * 
     * @var User|null $user
     */
    $user = $request->user();
    
    /** 
     * cant check if no user, and methods other than "get" dont return a proper 
     * view - so no need to check for warnings and attack them     
     */
    if (!$user || !$request->isMethod('get')) {
      return;
    }    
    
    $warnings = $this->computeWarnings($user);   
    
    if (!empty($warnings)) {
      View::share('user_warnings',$warnings);
    }
  }  
  
  /**
   * Compute warnings.
   * 
   * @param User $user
   * @return array|string[][]|array[][]|NULL[][]
   */
  private function computeWarnings(User $user) 
  {    
    $owned_bike_ids = $user->bikes()->select('id')->get()->pluck('id');
    $editable_bike_ids = $user->editableBikes()->select('id')->get()->pluck('id');    
        
    $bike_ids = $owned_bike_ids->merge($editable_bike_ids)->toArray();      
    
    $warnings = [];
    if(empty($bike_ids)) {
      return $warnings;
    }        
    
    $query_base = Bike::query()    
    ->whereIn('id',$bike_ids)
    ->select('name','id');
    
    // query for images
    $query_img = clone $query_base;
    $need_images = $query_img->doesntHave('images')->get();
    foreach($need_images as $bike) {
      $warning = [
        'text' => __('bike.user_warnings.no_image',['name' => $bike->name]),
        'url' => route('bike.images',['bike_id' => $bike->id])
      ];
      $warnings[] = $warning;
    }
    // query for non-published bikes
    $query_public = clone $query_base;
    $need_publishing = $query_public->where('public',false)->get();
    foreach($need_publishing as $bike) {
      $warning = [
        'text' => __('bike.user_warnings.not_public',['name' => $bike->name]),
        'url' => route('bike.publish',['bike_id' => $bike->id])
      ];
      $warnings[] = $warning;
    }
    
    // query for missing rental periods
    $now = (now())->format('Y-m-d');
    $need_rental_period = $query_base->whereDoesntHave('rentalPeriods', function (Builder $query) use($now) {
      $query->where('date_to','>=',$now);
    })->get();
    foreach($need_rental_period as $bike) {
      $warning = [
        'text' => __('bike.user_warnings.no_rental_period',['name' => $bike->name]),
        'url' => route('bike.rental_period',['bike_id' => $bike->id])
      ];
      $warnings[] = $warning;
    }
    
    return $warnings;
  }   
}