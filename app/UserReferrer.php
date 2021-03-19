<?php

namespace App;

use App\Traits\GetIdByField;
use App\Traits\GetSelectOptions;
use Illuminate\Database\Eloquent\Model;

class UserReferrer extends Model
{
    use GetSelectOptions;
        
    public $timestamps = false;
    
    
}
