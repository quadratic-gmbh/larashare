<?php

namespace App\Console\Commands;

use App\RentalPlace;
use App\Services\Geocoder;
use App\Services\Notifier;
use Illuminate\Console\Command;

class GeocodeRentalPlaces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geocode:rental_places {--F|force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Query coordinates for all rental places that dont have lon/lat yet.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Geocoder $geocoder, Notifier $notifier)
    {
      $force = $this->option('force');
      
      $rental_places = RentalPlace::whereNull('lon')
      ->whereNull('lat')
      ->get();
      
      $cnt = $rental_places->count();
      if(!$force) {
        $continue = $this->confirm("Geocode {$cnt} rental places?",true);
        if (!$continue) {
          exit();
        }
      } 
      
      for($i = 0; $i < $cnt; $i++) {
        $rp = $rental_places[$i];         
        echo "{$i}: {$rp->name}\n";                  

        $result = $geocoder->queryRentalPlace($rp);
        if($result != false){            
          $lon = floatval($result['lon']);
          $lat = floatval($result['lat']);
          echo "Result: {$lon}|{$lat}\n";
          $rp->lon = $lon;
          $rp->lat = $lat;
          $rp->save();
        } else {
          echo "Geocoding failed. continue.\n";
          $notifier->notifyGeocodeRentalPlaceProblem($rp);
        }
        // nominatim allows at most 1 request per second          
        sleep(2);
      }
      
      echo "done\n";
    }
}
