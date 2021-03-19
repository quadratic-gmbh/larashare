<?php

namespace App\Policies;

use App\Embed;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmbedPolicy
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
     * modification can be done by owner 
     * 
     * @param User $user
     * @param Embed $embed
     * @return boolean
     */
    public function modify(User $user, Embed $embed)
    {     
      return ($user->id === $embed->user_id);
    }
}
