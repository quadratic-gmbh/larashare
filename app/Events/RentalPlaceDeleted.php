<?php

namespace App\Events;

use App\RentalPlace;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RentalPlaceDeleted
{
    use Dispatchable, SerializesModels;

    public $rental_place;
    
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(RentalPlace $rental_place)
    {
        $this->rental_place = $rental_place;
    }

}
