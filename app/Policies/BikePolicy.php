<?php

namespace App\Policies;

use App\Bike;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BikePolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    
    /**
     * modification can be done by owner or any editor
     * 
     * @param User $user
     * @param Bike $bike
     * @return boolean
     */
    public function modify(User $user, Bike $bike)
    {     
      return (($user->id === $bike->user_id) || ($bike->editors()->where('id',$user->id)->exists()));
    }
    
    /**
     * only owner can delete 
     * 
     * @param User $user
     * @param Bike $bike
     * @return boolean
     */
    public function delete(User $user, Bike $bike)
    {
      return $user->id === $bike->user_id;
    }
}
