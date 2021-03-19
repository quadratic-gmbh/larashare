<?php

namespace App\Http\Controllers;

use App\Bike;
use App\Embed;
use App\Services\Geocoder;
use App\Services\SearchEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SearchController extends Controller
{
  /**
   * Embed index.
   * 
   * @param Request $request
   * @param SearchEngine $search_engine
   * @param Geocoder $geocoder
   * @param unknown $embed
   * @return \Illuminate\Http\JsonResponse|\App\Http\Controllers\unknown
   */
  public function embedIndex(Request $request, SearchEngine $search_engine, Geocoder $geocoder, $embed = null) 
  {
//     $embed = Embed::find($id);
    return $this->search($request, $search_engine, $geocoder, $embed);
  }
  
  /**
   * Embed map.
   * 
   * @param Request $request
   * @param SearchEngine $search_engine
   * @param unknown $embed
   * @return \Illuminate\Http\JsonResponse|\App\Http\Controllers\unknown
   */
  public function embedMap(Request $request, SearchEngine $search_engine, $embed = null)
  {    
//     $embed = Embed::find($id);

    return $this->browse($request, $search_engine, $embed);
  }
  
  /**
   * Show index.
   * 
   * @param Request $request
   * @param SearchEngine $search_engine
   * @param Geocoder $geocoder
   * @return \Illuminate\Http\JsonResponse|\App\Http\Controllers\unknown
   */
  public function index(Request $request, SearchEngine $search_engine, Geocoder $geocoder)
  {
    return $this->search($request, $search_engine, $geocoder);
  }  
  
  /**
   * Api search.
   * 
   * @param Request $request
   * @param SearchEngine $search_engine
   * @param Geocoder $geocoder
   * @return \Illuminate\Http\JsonResponse|\App\Http\Controllers\unknown
   */
  public function apiSearch(Request $request, SearchEngine $search_engine, Geocoder $geocoder)
  {
    $embed = null;
    if($request->has('embed_id')) {
      $embed = Embed::find(intval($request->input('embed_id')));
    }
    
    return $this->search($request, $search_engine, $geocoder, $embed, true);  
  }
  
  /**
   * Do search.
   * 
   * @param Request $request
   * @param SearchEngine $search_engine
   * @param Geocoder $geocoder
   * @param Embed $embed
   * @param bool $as_json
   * @return \Illuminate\Http\JsonResponse|unknown
   */
  private function search(Request $request, SearchEngine $search_engine, Geocoder $geocoder, Embed $embed = null, bool $as_json = false)
  {
    $input = ($as_json ? $request->except('embed_id') : $request->query());
    
    $form_data = [];
    $view_data = [];
    if (!empty($input)) {          
      
      $validator = Validator::make($input, $this->searchRules());                  
      if ($validator->fails()) { 
        if ($as_json) {
          return response()->json(['errors' => $validator->errors()],400);
        } else {
          return view('search.index',['form_data'=> $input])
          ->withErrors($validator->errors());
        }        
      }       
            
      // perform first pass of input filtering
      list($location_lon, $location_lat) = $this->extractLocationQuery($geocoder, $input);
      
      // filter int fields where necessary
      if(!$as_json) {
        $this->searchFilterIntegers($input);
        $form_data = $input;                                    
      } else {
        $view_data['location'] = [
          'cur' => $input['location'],
          'old' => $input['location_old'],
          'lon' => $location_lon,
          'lat' => $location_lat
        ];
      }
      
      // perform search
      $filtered = $search_engine->filterSearchInput($input);
      $bikes = $search_engine->performSearch($filtered, $embed);      

      // for each bike find the first image
      $bike_images = Bike::getImagesForBikes($bikes->pluck('id')->toArray());
      
      // find all associated rental places      
      $rental_places = DB::table('rental_places')      
      ->where('deleted_at',null)
      ->whereIn('id',$bikes->pluck('rental_place_id')->toArray())
      ->select([
        'id',
        'bike_id',
        'name',
        'street_name',
        'house_number',
        'postal_code',
        'city',
        'lon',
        'lat',
        'description'
      ])
      ->get();      
      
      $search_engine->randomizeRentalPlaceCoordinates($rental_places);
      
      // calculate distance
      $calculated_distance = false;
      if($location_lon !== null && $location_lat !== null) {
        $loc_lon = floatval($location_lon);
        $loc_lat = floatval($location_lat);
        
        foreach($rental_places as $rp) {
          if($rp->lon === null || $rp->lat === null) {
            continue;
          }
          $rp_lon = floatval($rp->lon);
          $rp_lat = floatval($rp->lat);
          
          $a = cos(deg2rad($loc_lat)) * cos(deg2rad($rp_lat)) * cos(deg2rad($rp_lon) - deg2rad($loc_lon));
          $b = sin(deg2rad($loc_lat)) * sin(deg2rad($rp_lat));
          $d = round(6371 * acos($a + $b),2);
                  
          $rp->distance = $d;
        }
        
        $calculated_distance = true;
      } else {
        $view_data['invalid_location'] = true;
      }                      
      $rental_places = $rental_places->mapWithKeys(function($item) {
        return [$item->id => $item];
      });  
            
      // order bikes by distance
      if ($calculated_distance) {
        $bikes = $bikes->sort(function($a,$b) use ($rental_places) {
          $a_d = $rental_places[$a->rental_place_id]->distance ?? null;
          $b_d = $rental_places[$b->rental_place_id]->distance ?? null;
          
          if($a_d === null) return 1;                    
          if($b_d === null) return -1;
          
          if ($a_d === $b_d) return 0;
          
          return ($a_d > $b_d) ? 1 : -1;
        });
      }
      
      // determine duplicate bikes
      $duplicate_bikes = $bikes->mapToGroups(function ($item, $key) {
        return [$item->id => $item->rental_place_id];
      })->filter(function($value, $key) {
        return $value->count() > 1;
      });
      $bike_instances = [];
      foreach($duplicate_bikes as $db) {
        $counter = 1;
        foreach($db as $rp_id) {
          $bike_instances[$rp_id] = $counter++;
        }
      }
            
      $view_data['bikes'] = $bikes->values();           
      $view_data['bike_images'] = $bike_images;
      $view_data['rental_places'] = $rental_places;
      $view_data['search_mode'] = true;
      $view_data['bike_instances'] = $bike_instances;
    } else { // default form values
      list($bikes, $rental_places, $bike_images) = $search_engine->performMapSearch([], $embed);
      
      $view_data['bikes'] = $bikes;
      $view_data['rental_places'] = $rental_places;
      $view_data['bike_images'] = $bike_images;
      $view_data['search_mode'] = false;
      
      $form_data = [
        'location' => 'Wien',
        'date' => now()->format('Y-m-d'),
        'duration' => 2,
      ];
    }            
    
    if($as_json) {       
      return response()->json($view_data);
    } else {
      $view_data['form_data'] = $form_data;
      $view_data['embed'] = ($embed !== null);
      return view('search.index',$view_data);
    }
  }   
  
  /**
   * Api browse.
   * 
   * @param Request $request
   * @param SearchEngine $search_engine
   * @return \Illuminate\Http\JsonResponse|\App\Http\Controllers\unknown
   */
  public function apiBrowse(Request $request, SearchEngine $search_engine) 
  {
    $embed = null;
    if($request->has('embed_id')) {
      $embed = Embed::find(intval($request->input('embed_id')));
    }
    
    return $this->browse($request, $search_engine, $embed, true);  
  }
  
  /**
   * Map.
   * 
   * @param Request $request
   * @param SearchEngine $search_engine
   * @return \Illuminate\Http\JsonResponse|\App\Http\Controllers\unknown
   */
  public function map(Request $request, SearchEngine $search_engine) 
  {
    return $this->browse($request, $search_engine);
  }
  
  /**
   * Browse search.
   * 
   * @param Request $request
   * @param SearchEngine $search_engine
   * @param Embed $embed
   * @param bool $as_json
   * @return \Illuminate\Http\JsonResponse|unknown
   */
  private function browse(Request $request, SearchEngine $search_engine, Embed $embed = null, bool $as_json = false)
  {
    $input = ($as_json ? $request->except('embed_id') : $request->query());
    
    $form_data = [];
    $view_data = [];
    if (!empty($input)) {      
      $rules = [      
        'wheels' => ['nullable','integer','min:2','max:4'],
        'children' => ['nullable','integer','min:0','max:4'],
        'electric' => ['nullable','integer','min:1','max:2'],
        'box_type_id' => ['nullable','integer','exists:box_types,id'],
        'cargo_weight' => ['nullable','integer','min:0','max:200'],
        'cargo_length' => ['nullable','integer','min:0','max:300'],
        'cargo_width' => ['nullable','integer','min:0','max:150'],
      ];      
      
      $validator = Validator::make($input, $rules);
      if ($validator->fails()) {
        if($as_json){
          return response()->json(['errors' => $validator->errors()], 400);
        } else {
          return view('search.map',['form_data'=> $input])
          ->withErrors($validator->errors());
        }
      }  
      
      //filter input
      foreach(array_keys($rules) as $field) {
        $input[$field] = isset($input[$field]) ? intval($input[$field]) : null;
      }
      // special treatment for electric      
      $form_data = $input;      
    }
    
    list($bikes, $rental_places, $bike_images) = $search_engine->performMapSearch($form_data,$embed);
    
    $view_data['bikes'] = $bikes;
    $view_data['rental_places'] = $rental_places;
    $view_data['bike_images'] = $bike_images;
    
    if($as_json) {
      return response()->json($view_data);
    }
    
    $view_data['form_data'] = $form_data;    
    $view_data['embed'] = ($embed !== null);
    
    return view('search.map',$view_data);
  }
  
  /**
   * Filters integer fields.
   * 
   * @param unknown $input
   */
  private function searchFilterIntegers(&$input) {
    $int_fields = [
      'duration',
      'flex',
      'wheels',
      'box_type_id',
      'children',
      'electric',
      'cargo_weight',
      'cargo_length',
      'cargo_width'
    ];
    foreach($int_fields as $field) {
      $val = $input[$field] ?? null;
      if($val !== null) {
        $input[$field] = intval($val);
      }
    }
  }
  
  /**
   * Search rules.
   * 
   * @return string[][]|\Illuminate\Validation\Rules\In[][]
   */
  private function searchRules()
  {
    return [
      'location' => ['required','string'],
      'location_old' => ['present'],
      'location_lon' => ['present'],
      'location_lat' => ['present'],
      'date' =>  ['required','date_format:"Y-m-d"'],
      'duration' =>  ['required','integer'],
      'duration_type' => ['required', Rule::in(['h','d'])],
      'flex' => ['nullable', 'integer','min:1','max:3'],
      'time_from' => ['nullable','date_format:"H:i"'],
      'time_to' => ['nullable','date_format:"H:i"','after:time_from'],
      'wheels' => ['nullable','integer','min:2','max:4'],
      'children' => ['nullable','integer','min:0','max:4'],
      'electric' => ['nullable','integer','min:1','max:2'],
      'box_type_id' => ['nullable','integer','exists:box_types,id'],
      'cargo_weight' => ['nullable','integer','min:0','max:200'],
      'cargo_length' => ['nullable','integer','min:0','max:300'],
      'cargo_width' => ['nullable','integer','min:0','max:150'],
    ];
  }
  
  /**
   * Extract location query.
   * 
   * @param Geocoder $geocoder
   * @param unknown $input
   * @return unknown[]
   */
  private function extractLocationQuery(Geocoder $geocoder, &$input) 
  {
    $location = $input['location'];
    $location_old = $input['location_old'];
    if ($location !== $location_old) {
      $location_old = $location;
      $input['location_old'] = $location_old;
      $result = $geocoder->queryLocation($location);
      if ($result !== false) {
        $input['location_lon'] = $result['lon'];
        $input['location_lat'] = $result['lat'];
      } else {
        $input['location_lon'] = null;
        $input['location_lat'] = null;
      }
    }
    $location_lon = $input['location_lon'];
    $location_lat = $input['location_lat'];
    
    return [$location_lon, $location_lat];
  }
}
