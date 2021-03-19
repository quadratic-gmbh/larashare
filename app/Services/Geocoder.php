<?php
/**
 * Geocoder.php
 */
namespace App\Services;

use App\RentalPlace;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;


/**
 * Geocoding service - translate location queries into coordinates.
 *
 * @author vadler
 *
 */
class Geocoder
{
  /**
   * 
   * @var string $base_uri used for client constructor
   */
  private $base_uri = 'https://nominatim.openstreetmap.org';
  /**
   * @var string $search_query uri for the service.
   */
  private $search_query = 'search?format=json';
  
  /**
   * @var Client $client http client for querying the service.
   */
  private $client = null;
  
  /**
   * Constructor. Creates a new http client instance.
   */
  public function __construct()
  {
    $this->client = new Client([
      'base_uri' => $this->base_uri
    ]);
  }
  
  /**
   * Query rental place.
   * 
   * @param RentalPlace $rental_place
   * @return boolean|array
   */
  public function queryRentalPlace(RentalPlace $rental_place)
  {
    $query = "{$rental_place->house_number} {$rental_place->street_name} {$rental_place->postal_code} {$rental_place->city}";
    
    return $this->queryLocation($query);
  }
  
  /**
   * Request lon/lat coordinates via a geocoding service by providing a location query.
   *
   * @param string $query the location query.
   *
   * @return boolean|array false or an array of lon and lat.
   */
  public function queryLocation(string $query)
  {
    $cache_key = 'geocoder_' . md5($query);    
    // check if value is cached
    if(Cache::has($cache_key)) {
      $result = Cache::get($cache_key);             
    } else {
      $result = $this->executeGeocodeQuery($query);
      // cache for half an hour
      Cache::put($cache_key, $result, 1800);     
    }
    return $result;               
  }
  
  /**
   * Executes geocode query.
   * 
   * @param unknown $query
   * @return boolean|number[]
   */
  private function executeGeocodeQuery($query)
  {
    $uri = $this->search_query . '&q=' . urlencode($query);    
    try {
      $response = $this->client->request('GET',$uri,[
        'headers' =>  [
          'User-Agent' => 'larashare2.at geocoder/1.0'
        ],
        'connect_timeout' => 5 //timeout after 5s
      ]);
      
      //error handling ?
      if ($response->getStatusCode() != 200) {
        return false;
      }
      
      $content = json_decode($response->getBody(),true);
      if (!$content || empty($content)) {
        return false;
      }
      // take first result and return lat/lon
      $lon = floatval($content[0]['lon']);
      $lat = floatval($content[0]['lat']);
      
      return ['lon' => $lon, 'lat' => $lat];
    } catch(ConnectException  $e) {
      Log::error("Attempted to geocode " . $query . ", failed with " . $e->getMessage());
      return false;
    }
  }
  
}