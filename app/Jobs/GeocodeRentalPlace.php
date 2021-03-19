<?php

namespace App\Jobs;

use App\Services\Geocoder;
use App\Services\Notifier;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GeocodeRentalPlace implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $rental_place;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($rental_place)
    {
      $this->rental_place = $rental_place;
      $this->onQueue('geocode');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Geocoder $geocoder, Notifier $notifier)
    {
      $result = $geocoder->queryRentalPlace($this->rental_place);
      if($result != false){
        $lon = floatval($result['lon']);
        $lat = floatval($result['lat']);
        //echo "Result: {$lon}|{$lat}\n";
        $this->rental_place->lon = $lon;
        $this->rental_place->lat = $lat;
        $this->rental_place->save();
      } else {
        //echo "Geocoding failed. continue.\n";
        $notifier->notifyGeocodeRentalPlaceProblem($this->rental_place);
      }
      // nominatim allows at most 1 request per second
      sleep(2);
    }
}
