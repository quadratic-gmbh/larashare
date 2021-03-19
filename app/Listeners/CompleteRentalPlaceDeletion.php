<?php

namespace App\Listeners;

use App\RentalPeriod;
use App\RentalPeriodException;
use App\RentalPeriodRenteeLimitation;
use App\Events\RentalPlaceDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use App\RentalPeriodExceptionRenteeLimitation;

class CompleteRentalPlaceDeletion
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  RentalPlaceDeleted  $event
     * @return void
     */
    public function handle(RentalPlaceDeleted $event)
    {
        $rental_place = $event->rental_place;
        
        $r_period_ids = $rental_place->rentalPeriods->pluck('id')->toArray();
        // delete the weekday mappings and rentee limitations
        DB::table('rental_period_weekday')->whereIn('rental_period_id',$r_period_ids)->delete();
        RentalPeriodRenteeLimitation::whereIn('rental_period_id',$r_period_ids)->delete();
        // delete rental periods
        RentalPeriod::whereIn('id',$r_period_ids)->delete();   
                        
        $r_exception_ids = RentalPeriodException::where('rental_place_id', $rental_place->id)
        ->get()
        ->pluck('id')
        ->toArray();
        // delete rentee limitations for exceptions
        RentalPeriodExceptionRenteeLimitation::whereIn('rental_period_exception_id',$r_exception_ids)
        ->delete();                
        // mark exception slots as non available
        RentalPeriodException::whereIn('id',$r_exception_ids)
        ->update([
          'rental_place_id' => null,
          'rental_mode_id' => null,
          'available' => 0
        ]);               
    }
}
