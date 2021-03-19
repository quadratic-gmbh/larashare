<?php
/**
 * SearchEngine.php
 */
namespace App\Services;

use App\Bike;
use App\BikeReservation;
use App\Embed;
use App\RentalMode;
use App\RentalPeriodException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Util\IntervalTree\DateTimeRange;
use App\Util\IntervalTree\IntervalTree;
use App\Util\IntervalTree\CarbonRange;
use Carbon\CarbonInterval;

class SearchEngine
{  
  
  /**
   * Find rental places for reservation.
   * 
   * @param BikeReservation $reservation
   * @return NULL[]
   */
  public function findRentalPlacesForReservation(BikeReservation $reservation)
  {
    $from = $reservation->reserved_from;
    $to = $reservation->reserved_to;
    
    $rental_places = [];
    $rental_periods = DB::table('rental_periods')
    ->join('rental_places','rental_places.id','=','rental_periods.rental_place_id')
    ->join('rental_period_weekday','rental_period_weekday.rental_period_id','=','rental_periods.id')
    ->where('rental_places.deleted_at',null)
    ->where('rental_places.bike_id',$reservation->bike_id)
    ->select([
      'rental_period_weekday.weekday_id as weekday',
      'rental_periods.rental_place_id',
      'rental_periods.date_from',
      'rental_periods.date_to',
      'rental_periods.time_from',
      'rental_periods.time_to',
    ])    
    ->whereNested(function($query) use($from, $to) {              
      $query->where([
        ['rental_period_weekday.weekday_id','=',$from->dayOfWeekIso],
        ['rental_periods.date_from','<=',$from->format('Y-m-d')],
        ['rental_periods.date_to','>=',$from->format('Y-m-d')],        
        ['rental_periods.time_from','<=',$from->format('H:i:s')],
        ['rental_periods.time_to','>=',$from->format('H:i:s')],
      ]);      
      $query->orWhere([
        ['rental_period_weekday.weekday_id','=',$to->dayOfWeekIso],
        ['rental_periods.date_from','<=',$to->format('Y-m-d')],
        ['rental_periods.date_to','>=',$to->format('Y-m-d')],
        ['rental_periods.time_from','<=',$to->format('H:i:s')],
        ['rental_periods.time_to','>=',$to->format('H:i:s')],
      ]);      
    })
    ->distinct()
    ->get()
    ->mapToGroups(function($item, $key) {      
      return [$item->weekday => $item];    
    });

    // find exceptions
    $exceptions = $reservation->bike->rentalPeriodExceptions()
    ->select([
      'rental_place_id',
      'date_time_from',
      'date_time_to',
      'available'
    ])
    ->whereNested(function($query) use($from,$to) {
      $query->whereNested(function($query) use ($from) {
        $query->where('date_time_to','<',$from->format('Y-m-d H:i:s'))
        ->orWhere('date_time_from','>',$from->format('Y-m-d H:i:s'));
      },'and not');
      $query->whereNested(function($query) use ($to) {
        $query->where('date_time_to','<',$to->format('Y-m-d H:i:s'))
        ->orWhere('date_time_from','>',$to->format('Y-m-d H:i:s'));
      },'or not');
    })
    ->get()
    ->mapToGroups(function($item, $key) {
      return [$item->date_time_from->format('Y-m-d') => $item];
    });
    
    
    $rental_place_ids = [null,null];
    foreach([$from,$to] as $i=>$dt) {      
      $date = $dt->format('Y-m-d');
      $time = $dt->format('H:i:s');
      $weekday = $dt->dayOfWeekIso;
      if(isset($exceptions[$date])) {
        foreach($exceptions[$date] as $e) {          
          if (!$e->available || $e->date_time_to < $dt || $e->date_time_from > $dt) {
            continue;
          }          
          $rental_place_ids[$i] = $e->rental_place_id;
          break;
        }
      } else if(isset($rental_periods[$weekday])) {
        foreach($rental_periods[$weekday] as $rp) {          
          if ($rp->date_to < $date || $rp->date_from > $date || $rp->time_from > $time || $rp->time_to < $time) {
            continue;
          }
          $rental_place_ids[$i] = $rp->rental_place_id;
          break;
        }
      }
    }

    return $rental_place_ids;
  }
  
  /**
   * verify that given period is not blocked
   * 
   * @param Carbon $from
   * @param Carbon $to
   * @param Bike $bike
   * @param int $duration
   * @return boolean
   */
  public function validatePossibleReservation(Carbon $from, Carbon $to, Bike $bike, int $duration, $ignored_reservation = null)
  {
    $query = DB::table('bikes');
    $query->join('rental_places','rental_places.bike_id','=','bikes.id');
    $query->select([
      'bikes.id',
      'bikes.user_id',
      'rental_places.id as rental_place_id'
    ]);
    $query->where([
      ['bikes.id','=',$bike->id],
      ['bikes.public','=',1],
      ['bikes.deleted_at','=',null],
      ['rental_places.deleted_at','=',null]
    ]);
    
    $date_pair = $this->makeDatePair($from, $from, $to, $to);

    // filter out bikes that are reserved already
    $query->whereNested(function($query) use ($date_pair, $ignored_reservation) {
      $this->searchFilterNoReservations($query, $date_pair, $ignored_reservation);
    });
      
    // get the filtered results
    $results = $query->get();
    
    if($results->isEmpty()) {
      return 'already_reserved';
    }
    
    $bike_ids = $results->pluck('id')->unique()->toArray();
    $bike_owners = $results->pluck('user_id', 'id')->toArray();
    $bike_rental_places = $results->mapToGroups(function($item, $key) {
      return [$item->id => $item->rental_place_id];
    });
      
    //get bike editors
    $bike_editors = Db::table('bike_editors')
    ->whereIn('bike_id', $bike_ids)
    ->select('bike_id', DB::raw('GROUP_CONCAT(`user_id` SEPARATOR \',\') as user_ids'))
    ->groupBy('bike_id')
    ->get();
    
    $bike_editors_sorted = [];
    
    foreach($bike_editors as $item){
      $bike_editors_sorted[$item->bike_id] = array_map('intval', (explode(',', $item->user_ids)));
    }
    foreach($bike_owners as $k => $v){
      $bike_editors_sorted[$k][] = $v;
    }
    
    // get all reservations that wall within the given period
    // TODO: cache trees ?
    $reservation_trees = $this->searchCreateReservationsTrees($bike_ids, $from, $to, $ignored_reservation);
    $rental_period_trees = $this->searchCreateRentalPeriodTrees(
      $bike_ids, $bike_rental_places, $from, $to, $bike_editors_sorted
    );
    
    $filtered_results = collect([]);
    $issues = ['not_available'];
    foreach($results as $result) {
      $b_id = $result->id;
      $rp_id = $result->rental_place_id;
      $r_tree = $reservation_trees[$b_id] ?? null;
      $rp_trees = $rental_period_trees[$b_id] ?? null;
      
      $no_interrupt_tree = $rp_trees['places'][$rp_id][1];
      $interrupt_tree = $rp_trees['places'][$rp_id][0];
      $all_tree = $rp_trees['all'];
      $all_no_interrupt_tree = $rp_trees['no_interrupt'];
      $all_no_interrupt_except_end_tree = $rp_trees['no_interrupt_except_end'];
      $no_access = $rp_trees['no_access'];
      $duration_data = $rp_trees['duration_data'];
        
      // check if there are even any slots available
      if($no_interrupt_tree === null &&  $interrupt_tree === null) {
        $issues[] = 'not_available';
        continue;
      }
        
      $range = new CarbonRange($date_pair['from_min'], $date_pair['to_min']);
      
      // check reservation for conflict
      if(isset($r_tree) && $r_tree->intersects($range)) {
        $issues[] = 'already_reserved';
        continue;
      }
      
      //check if there is a period with limited users the user has no access to
      if($no_access !== null){
        if($no_access->intersects($range)){
          $issues[] = 'not_available';
          continue;
        }
      }
      
      //check if there is an interrupt period except end
      if (isset($all_no_interrupt_except_end_tree) && $all_no_interrupt_except_end_tree->intersects($range)) {
        $issues[] = 'not_available';
        continue;
      }
      
      //check duration
      foreach($duration_data as $entry){
        if(($range->getStart() >= $entry['range']->getStart()) && ($range->getStart() <= $entry['range']->getEnd())){
          if($duration > $entry['rental_duration']){
            $issues[] = 'duration';
            continue 2;
          } else {
            break;
          }
        }
      }
        
      // check no_interrupt tree for a match
      if (isset($no_interrupt_tree) && $no_interrupt_tree->overlaps($range)) {
        $filtered_results->push($result);
        continue;
      }
        
      // check interrupt tree and all tree for a match
      if (isset($interrupt_tree) &&
        isset($all_tree) &&
        $interrupt_tree->containsPoint($range->getStart()) &&
        ($all_tree->containsPoint($range->getEnd()) || (isset($all_no_interrupt_tree) && $all_no_interrupt_tree->containsPoint($range->getEnd())))) {
          $filtered_results->push($result);
          continue;
      }
    }
    
    return ($filtered_results->isNotEmpty() ? true : end($issues));
  }
  
  /**
   * verify that with buffer times it doesn't collide with another reservation
   * 
   * @param Carbon $from
   * @param Carbon $to
   * @param Bike $bike
   * @return boolean
   */
  public function validateReservationBuffer(Carbon $from, Carbon $to, Bike $bike, $ignored_reservation = null)
  {
    $dp = $this->makeDatePair($from, $from, $to, $to);
    
    $query = DB::table('bike_reservations');
    $query->where([
      ['bike_reservations.bike_id','=',$bike->id],
      ['bike_reservations.deleted_at','=',null]
    ]);
    
    $query->whereNested(function ($query) use ($dp) {
      // reservations covering the start window
      $query->whereNested(function ($query) use ($dp) {
        $query->where('bike_reservations.reserved_from', '<', $dp['from_min_dt'])
          ->where('bike_reservations.reserved_to', '>', $dp['from_max_dt']);
      }, 'or');
      // reservations covering the return window
      $query->whereNested(function ($query) use ($dp) {
        $query->where('bike_reservations.reserved_from', '<', $dp['to_min_dt'])
          ->where('bike_reservations.reserved_to', '>', $dp['to_max_dt']);
      }, 'or');
      // reservations covering everything
      $query->whereNested(function ($query) use ($dp) {
        $query->where('bike_reservations.reserved_from', '<', $dp['from_min_dt'])
          ->where('bike_reservations.reserved_to', '>', $dp['to_max_dt']);
      }, 'or');
      // reservations between open window and return window
      $query->whereNested(function ($query) use ($dp) {
        $query->where('bike_reservations.reserved_from','>',$dp['from_max_dt']);
        $query->where('bike_reservations.reserved_to','<',$dp['to_min_dt']);
      });
    });
    
    if ($ignored_reservation !== null){
      $query->where('id', '<>', $ignored_reservation->id);
    }
    
    $results = $query->get();
    
    foreach($results as $res){
      $res_from = new Carbon($res->reserved_from);
      $res_to = new Carbon($res->reserved_to);
      
      //new reservation would be before the one it clashes with
      if($from < $res_from){
        //only if there is buffer times on both it would be possible to merge
        if($bike->buffer_time_after && $res->buffer_time_before){
          $min_buffer = (($bike->buffer_time_after > $res->buffer_time_before) ? $bike->buffer_time_after : $res->buffer_time_before);

          $to_no_buffer = clone $to;
          $to_no_buffer = $to_no_buffer->subMinutes($bike->buffer_time_after);
          
          $res_from_no_buffer = clone $res_from;
          $res_from_no_buffer = $res_from_no_buffer->addMinutes($res->buffer_time_before);
          
          if($min_buffer > $to_no_buffer->diffInMinutes($res_from_no_buffer)){
            return false;
          }
        }else{
          return false;
        }
        //new reservation would be after the one it clashes with
      }else{ 
        //only if there is buffer times on both it would be possible to merge
        if($bike->buffer_time_before && $res->buffer_time_after){
          $min_buffer = (($bike->buffer_time_before > $res->buffer_time_after) ? $bike->buffer_time_before : $res->buffer_time_after);
          
          $from_no_buffer = clone $from;
          $from_no_buffer = $from_no_buffer->addMinutes($bike->buffer_time_before);
          
          $res_to_no_buffer = clone $res_to;
          $res_to_no_buffer = $res_to_no_buffer->subMinutes($res->buffer_time_after);
          
          if($min_buffer > $from_no_buffer->diffInMinutes($res_to_no_buffer)){
            return false;
          }
        }else{
          return false;
        }
      }
    }
      
    return true;
  }
  
  /**
   * check which rental places are affected and what rental mode it is
   * 
   * @param Carbon $from
   * @param Carbon $to
   * @param Bike $bike
   * @return boolean
   */
  public function checkRentalPlaces(Carbon $from, Carbon $to, Bike $bike)
  {      
    $mode_id = RentalMode::getInquiryId();    
    
    // get all rental periods and exceptions for the affected timeframe
    $exceptions = $bike->rentalPeriodExceptions()
    ->whereNested(function($query) use ($from, $to) {
      $query->where('date_time_to','<',$from->format('Y-m-d H:i:s'));
      $query->orWhere('date_time_from','>',$to->format('Y-m-d H:i:s'));
    },'and not')
    ->get()
    ->mapToGroups(function($item, $key) {
      return [$item['date_time_from']->format('Y-m-d') => $item];
    });
    $rental_periods = $bike->rentalPeriods()
    ->with([
      'weekdays'
    ])->whereNested(function($query) use ($from, $to) {
      $query->where('date_to','<',$from->format('Y-m-d'));
      $query->orWhere('date_from','>',$to->format('Y-m-d'));
    },'and not')
    ->get();        
    
    $map = [];
    foreach($rental_periods as $rp) {
      foreach($rp->weekdays as $wd) {
        $day = $wd->id;
        if (!isset($map[$day])) {
          $map[$day] = [];
        }
        
        $map[$day][] = [
          'time_from' => $rp->time_from->format('H:i'),
          'time_to' => $rp->time_to->format('H:i'),
          'date_from' => $rp->date_from,
          'date_to' => $rp->date_to,          
          'rental_mode_id' => $rp->rental_mode_id,
          'rental_place_id' => $rp->rental_place_id
        ];
      }
    };
           
    $time_from = $from->format('H:i');
    $time_to = $to->format('H:i');
    $date_from = $from->format('Y-m-d');
    $date_to = $to->format('Y-m-d');
    $tmp = $from->copy();   
    $tmp_date = $tmp->format('Y-m-d');
    $tmp_time_from = null;
    $tmp_time_to = null;
    
    $is_inquiry = false;
    // check if there are any inquiry periods
    while($tmp_date <= $date_to) {               
      $tmp_time_from = ($tmp_date === $date_from ? $time_from : '00:00');
      $tmp_time_to = ($tmp_date === $date_to ? $time_to : '23:59');
      
      $tmp_from = $tmp->copy()->setTimeFromTimeString($tmp_time_to);
      $tmp_to = $tmp->copy()->setTimeFromTimeString($tmp_time_to);            
      $day = $tmp->dayOfWeekIso;
      if (isset($exceptions[$tmp_date])) {
        foreach($exceptions[$tmp_date] as $e) {
          // ignore blocking exceptions and exceptions that dont touch the 
          if (!$e->available || $e->date_time_to < $tmp_from || $e->date_time_from > $tmp_to) {
            continue;
          }          
          if ($e->rental_mode_id === $mode_id) {
            $is_inquiry = true;
            break 2;
          }                    
        }        
      } else if(isset($map[$day])){       
        // check all rental periods that have this weekday
        foreach($map[$day] as $entry) {
          if ($entry['date_from']->format('Y-m-d') > $tmp_date 
            || $entry['date_to']->format('Y-m-d') < $tmp_date
            || $entry['time_from'] > $tmp_time_to 
            || $entry['time_to'] < $tmp_time_from)
          {            
            continue;
          }
          
          if ($entry['rental_mode_id'] === $mode_id) {
            $is_inquiry = true;
            break 2;
          }
        }    
      }
      $tmp->addDay();
      $tmp_date = $tmp->format('Y-m-d');
    }   
    // check rental_place id for start and end
    $rental_place_ids = [];
    foreach([$from, $to] as $i => $date) {
      $day = $date->dayOfWeekIso;
      $date_str = $date->format('Y-m-d');      
      $time = $date->format('H:i');
      if (isset($exceptions[$date_str])) {
        foreach($exceptions[$date_str] as $e) {
          // ignore blocking exceptions and exceptions that dont touch the
          if (!$e->available || $e->date_time_to < $date || $e->date_time_from > $date) {
            continue;
          }
          $rental_place_ids[$i] = $e->rental_place_id;
          continue 2;
        }
      } else {
        // check all rental periods that have this weekday
        foreach($map[$day] as $entry) {          
          if ($entry['date_from']->format('Y-m-d') > $date_str
            || $entry['date_to']->format('Y-m-d') < $date_str
            || $entry['time_from'] > $time
            || $entry['time_to'] < $time)
          {
            continue;
          }
          $rental_place_ids[$i] = $entry['rental_place_id'];
          continue 2;
        }
      }
    }
    return [$is_inquiry, $rental_place_ids]; 
  }
  
  /**
   * Filter the search input.
   * 
   * @param unknown $input
   * @return array[]|number[]|NULL[]|unknown[]|string[]|\Carbon\Carbon[]
   */
  public function filterSearchInput($input)
  {
    $filtered = [];
    // rental duration is in hours, so if duration type is d or w we have to convert
    $duration = intval($input['duration']);
    switch($input['duration_type']) {
      case 'd':
        $duration *= 24;
        break;
      case 'w':
        $duration *= (7*24);
        break;
      default: // 'h'
        break;
    }
    $filtered['rental_duration'] = $duration;
    
    // timeframe if present
    $time_from = '00:00';
    $time_to = '23:59';
    if(($input['time_from'] ?? false) && ($input['time_to'] ?? false)) {
      $time_from = $input['time_from'];
      $time_to = $input['time_to'];
    }
    $tmp1 = Carbon::createFromFormat('H:i',$time_from);
    $tmp2 = carbon::createFromFormat('H:i',$time_to);
    $time_diff = $tmp1->diffInMinutes($tmp2);
    $filtered['time_from'] = $time_from;
    $filtered['time_to'] = $time_to;
    
    // define date min, max and pairs
    $date_min = Carbon::createFromFormat('Y-m-d',$input['date']);
    $date_max = $date_min->copy();
    if (array_key_exists('flex',$input)) {
      $amount = intval($input['flex']);
      $date_max->addDays($amount);
      $date_min->subDays($amount);
    }
    
    $date_from = $date_min->copy()->setTimeFromTimeString($time_from);
    $date_to = null;
    $filtered['date_pairs'] = [];
    // for each date there is also a return date that needs to be available, so create pairs
    for($tmp = $date_min->copy(); $tmp <= $date_max; $tmp->addDay()) {
      $tmp_from_min = $tmp->copy()->setTimeFromTimeString($time_from);
      $tmp_from_max = $tmp_from_min->copy()->addMinutes($time_diff);
      $tmp_to_min = $tmp_from_min->copy()->addHours($duration);
      $tmp_to_max = $tmp_from_max->copy()->addHours($duration);
      $filtered['date_pairs'][] = $this->makeDatePair($tmp_from_min,$tmp_from_max,$tmp_to_min,$tmp_to_max);      
      $date_to = $tmp_to_max;
    }
    $filtered['date_from'] = $date_from;
    $filtered['date_to'] = $date_to;
    
    foreach([
      'wheels',
      'children',
      'electric',
      'box_type_id',
      'cargo_weight',
      'cargo_length',
      'cargo_width'
    ] as $field) {
      $filtered[$field] = $input[$field] ?? null;
    }
    
    return $filtered;
  }
  
  /**
   * Makes date pairs.
   * 
   * @param Carbon $from_min
   * @param Carbon $from_max
   * @param Carbon $to_min
   * @param Carbon $to_max
   * @return unknown[]|NULL[]
   */
  private function makeDatePair($from_min, $from_max, $to_min,$to_max) 
  {
    $pair = [
      'from_min' => $from_min,    
      'from_min_dt' => $from_min->format('Y-m-d H:i:s'),    
      'from_max' => $from_max,    
      'from_max_dt' => $from_max->format('Y-m-d H:i:s'),    
      'to_min' => $to_min,   
      'to_min_dt' => $to_min->format('Y-m-d H:i:s'),    
      'to_max' => $to_max,    
      'to_max_dt' => $to_max->format('Y-m-d H:i:s'),    
    ];
    
    return $pair;
  }
  
  /**
   * Find bikes fitting given filters
   * 
   * @param [] $input
   * @param Embed $embed
   */
  public function performMapSearch($input, Embed $embed = null)
  {
    $query = DB::table('bikes');
    
    if ($embed !== null) {
      $embed_bikes = DB::table('bike_embed')->where('embed_id',$embed->id)->select('bike_id')->get();
      if ($embed_bikes->isNotEmpty()) {
        $query->whereIn('bikes.id',$embed_bikes->pluck('bike_id')->toArray());
      }
    }
    
    $query->join('pricing_types','pricing_types.id','=','bikes.pricing_type_id');
    $query->join('box_types','box_types.id','=','bikes.box_type_id');
    $query->select([
      'bikes.id',
      'bikes.name',
      'bikes.wheels',
      'bikes.children',
      'bikes.electric',
      'bikes.cargo_weight',
      'bikes.cargo_width',
      'bikes.cargo_length',
      'bikes.pricing_values',
      'bikes.pricing_deposit',
      'bikes.terms_of_use_file',
      'pricing_types.name as pricing_type',
      'box_types.name as box_type' 
    ]);
    $query->where([
      ['bikes.public','=',1],
      ['bikes.deleted_at','=',null]
    ]);
    
    // rental periods must exist
    $query->whereExists(function($query) {
      $query->select(DB::raw(1))
      ->from('rental_periods')
      ->join('rental_places','rental_places.id','=','rental_periods.rental_place_id')
      ->where('rental_places.deleted_at',null)
      ->whereRaw('rental_places.bike_id = bikes.id')
      ->where(function ($query) {
        $query->orWhereNotExists(function($query) {
          $query->select(DB::raw(1))
          ->from('rental_period_rentee_limitations')
          ->whereRaw('rental_periods.id = rental_period_rentee_limitations.rental_period_id');
        });
        if(Auth::check()){
          $user = Auth::user();
          $query->orWhereExists(function($query) use ($user) {
            $query->select(DB::raw(1))
            ->from('rental_period_rentee_limitations')
            ->whereRaw('rental_periods.id = rental_period_rentee_limitations.rental_period_id')
            ->where('rental_period_rentee_limitations.email', '=',  $user->email);
          });
           $query->orWhereExists(function($query) use ($user) {
            $query->select(DB::raw(1))
              ->from('bikes')
              ->whereRaw('rental_places.bike_id = bikes.id')
              ->where('bikes.user_id', '=', $user->id);
          });
           $query->orWhereExists(function($query) use ($user) {
            $query->select(DB::raw(1))
            ->from('bikes')
            ->whereRaw('rental_places.bike_id = bikes.id')
            ->join('bike_editors','bikes.id','=','bike_editors.bike_id')
            ->where('bike_editors.user_id', '=', $user->id);
          });
        }
      });
    });
    
    // apply filters  
    $equal_fields = [
      'box_type_id',
      'electric',
      'wheels',
    ];
    $gte_fields = [
      'cargo_weight',
      'cargo_width',
      'cargo_length',
    ];
    
    //children
    if(isset($input['children'])){
      if($input['children'] === 0){
        $equal_fields[] = 'children';
      }else if($input['children'] >= 1){
        $gte_fields[] = 'children';
      }
    }
    
    //special treatment for electric
    if(isset($input['electric']) && $input['electric'] != 1) {
      $input['electric'] = 0;
    }
    
    foreach($equal_fields as  $field) {
      if (isset($input[$field])) {        
        $query->where('bikes.' . $field,$input[$field]);
      }
    }
    
    foreach($gte_fields as $field) {
      if (isset($input[$field])) {        
        $query->where('bikes.' . $field, '>=', $input[$field]);
      }
    }      
    
    $bikes = $query->get();
    
    // find the rental_places
    $rental_places = DB::table('rental_places')
    ->where('deleted_at',null)
    ->whereIn('bike_id',$bikes->pluck('id')->toArray())
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
    // randomize coordinates
    $this->randomizeRentalPlaceCoordinates($rental_places);
    
    $rental_places = $rental_places->mapToGroups(function($item,$key) {
      return [$item->bike_id => $item];
    });
    
    // for each bike find the first image
    $bike_images = Bike::getImagesForBikes($bikes->pluck('id')->toArray());           
    
    return [$bikes, $rental_places, $bike_images];    
  }   
  
  /**
   * randomize coordinates in given rental place collection
   * 
   * @param collection $rental_places
   */
  public function randomizeRentalPlaceCoordinates(&$rental_places) {
    foreach($rental_places as $rp) {
      if ($rp->lat === null || $rp->lon === null) continue;
      
      list ($lon, $lat) = $this->randomizeCoordinates($rp->lat, $rp->lon);
      $rp->lon = $lon;
      $rp->lat = $lat;
    } 
  }
  
  /**
   *
   * find bikes fitting search criteria
   * 
   * @param [] $input
   * @param Embed|null $embed
   * @return []|boolean
   */
  public function performSearch($input, Embed $embed = null)
  {    
    // apply bike filters
    $query = DB::table('bikes');
    $query->join('rental_places','rental_places.bike_id','=','bikes.id');
    $query->join('pricing_types','pricing_types.id','=','bikes.pricing_type_id');
    $query->join('box_types','box_types.id','=','bikes.box_type_id');
    $query->select([
      'bikes.id',
      'bikes.user_id',
      'bikes.name',
      'bikes.wheels',
      'bikes.children',
      'bikes.electric',
      'bikes.cargo_weight',
      'bikes.cargo_width',
      'bikes.cargo_length',
      'bikes.pricing_values',
      'bikes.pricing_deposit',
      'bikes.terms_of_use_file',
      'pricing_types.name as pricing_type',
      'box_types.name as box_type',
      'rental_places.id as rental_place_id'
    ]);
    
    $query->where([      
      ['bikes.public','=',1],
      ['bikes.deleted_at','=',null],
      ['rental_places.deleted_at','=',null]
    ]);        

    if ($embed !== null) {
      $embed_bikes = DB::table('bike_embed')->where('embed_id',$embed->id)->select('bike_id')->get();
      if ($embed_bikes->isNotEmpty()) {
        $query->whereIn('bikes.id',$embed_bikes->pluck('bike_id')->toArray());
      }
    }
    
    $equal_fields = [
      'box_type_id',
      'electric',
      'wheels',
    ];
    $gte_fields = [
      'cargo_weight',
      'cargo_width',
      'cargo_length',
    ];
    
    //children
    if(isset($input['children'])){
      if($input['children'] === 0){
        $equal_fields[] = 'children';
      }else if($input['children'] >= 1){
        $gte_fields[] = 'children';
      }
    }
    
    // special treatment for electric
    if(isset($input['electric']) && $input['electric'] != 1) {
      $input['electric'] = 0;
    }
  
    foreach($equal_fields as  $field) {
      if (isset($input[$field])) {        
        $query->where('bikes.' . $field,$input[$field]);
      }
    }
    
    foreach($gte_fields as $field) {
      if(isset($input[$field])) {
        $query->where('bikes.' . $field, '>=', $input[$field]);
      }
    }                     
    
    $result = $this->searchFilter($query, $input);            
    
    return $result;
  }
  
  /**
   * Filters search.
   * 
   * @param unknown $query
   * @param unknown $input
   * @return \Illuminate\Support\Collection
   */
  private function searchFilter($query, $input)
  {
    $date_pairs = $input['date_pairs'];
    $duration = $input['rental_duration'];
    $dt_min = $input['date_from'];
    $dt_max = $input['date_to'];
    // filter out bikes that are reserved already
    $query->whereNested(function($query) use ($date_pairs) {
      foreach($date_pairs as $dp) {
        $query->whereNested(function($query) use ($dp) {
          $this->searchFilterNoReservations($query, $dp);
        }, 'or');
      }
    });
    
    // get the filtered results
    $results = $query->get();
    
    $bike_ids = $results->pluck('id')->unique()->toArray();
    $bike_owners = $results->pluck('user_id', 'id')->toArray();
    $bike_rental_places = $results->mapToGroups(function($item, $key) {
      return [$item->id => $item->rental_place_id];
    });
    
    //get bike editors
    $bike_editors = Db::table('bike_editors')
     ->whereIn('bike_id', $bike_ids)
     ->select('bike_id', DB::raw('GROUP_CONCAT(`user_id` SEPARATOR \',\') as user_ids'))
     ->groupBy('bike_id')
     ->get();
      
     $bike_editors_sorted = [];
      
     foreach($bike_editors as $item){
       $bike_editors_sorted[$item->bike_id] = array_map('intval', (explode(',', $item->user_ids)));
     }
     foreach($bike_owners as $k => $v){
       $bike_editors_sorted[$k][] = $v;
     }
        
    // get all reservations that wall within the given period
    // TODO: cache trees ?    
    $reservation_trees = $this->searchCreateReservationsTrees($bike_ids, $dt_min, $dt_max);  
    $rental_period_trees = $this->searchCreateRentalPeriodTrees(
      $bike_ids, $bike_rental_places, $dt_min, $dt_max, $bike_editors_sorted
    );    
    $filtered_results = collect([]);
    // check for every result item
    foreach($results as $result) {
      $b_id = $result->id;
      $rp_id = $result->rental_place_id;
      
      $r_tree = $reservation_trees[$b_id] ?? null;
      $rp_trees = $rental_period_trees[$b_id] ?? null;
      $no_interrupt_tree = $rp_trees['places'][$rp_id][1];
      $interrupt_tree = $rp_trees['places'][$rp_id][0];
      $all_tree = $rp_trees['all'];
      $duration_data = $rp_trees['duration_data'];
      
      // check if there are even any slots available
      if($no_interrupt_tree === null &&  $interrupt_tree === null) {
        continue;
      }
      
      $r_fails[$b_id] = [];
      foreach($date_pairs as $dp) {
        // iterate over the time ranges in steps of 30 mins
        foreach($this->generateIntervalsForDatePair($dp) as $range) {
          // check reservation for conflict        
          if(isset($r_tree) && $r_tree->intersects($range)) {       
            continue;
          }
          
          //check duration
          foreach($duration_data as $entry){
            if(($range->getStart() >= $entry['range']->getStart()) && ($range->getStart() <= $entry['range']->getEnd())){
              if($duration > $entry['rental_duration']){
                continue 2;
              } else {
                break;
              }
            }
          }
          
          // check no_interrupt tree for a match
          if (isset($no_interrupt_tree) && $no_interrupt_tree->overlaps($range)) {
            $filtered_results->push($result);
            continue 3;
          }
            
          // check interrupt tree and all tree for a match
          if (isset($interrupt_tree) && 
            isset($all_tree)&& 
            $interrupt_tree->containsPoint($range->getStart()) && 
            $all_tree->containsPoint($range->getEnd())) {
            $filtered_results->push($result);
            continue 3;
          }          
        }
      }
    }
    
    
    return $filtered_results;
  }
  
  
  /**
   * randomize given coordinates in a radius
   *
   * @param float $lat
   * @param float $lon
   * @param number $max_r
   */
  private function randomizeCoordinates($lat, $lon, $max_r = 100)
  {
    // number of km per degree = ~111km (111.32 in google maps, but range varies
    // between 110.567km at the equator and 111.699km at the poles)
    $constant = 1 / 111320; //distance per degree
    
    $rand = mt_rand() / mt_getrandmax();
    $phi = 2*pi() * $rand;
    $r = $max_r;
    $x = $r * cos($phi);
    $y = $r * sin($phi);
    
    $coef_lat = $y * $constant;
    $coef_lon = $x * $constant;
    
    $new_lat = $lat + $coef_lat;
    $new_lon = $lon + ($coef_lon / cos($lat * (pi() / 180)));
    
    return [$new_lon,$new_lat];    
  }
  
  /**
   * Generate intervals for date pair.
   * 
   * @param unknown $dp
   * @return Generator
   */
  private function generateIntervalsForDatePair($dp, $minutes = 30) 
  {
    $range = new CarbonRange($dp['from_min'], $dp['to_min']);
    while($range->getStart() <= $dp['from_max']) {
      yield $range;
      $range->applyCarbonInterval(CarbonInterval::minutes($minutes));
    }
  }
  
  /**
   * Create rental period trees.
   * 
   * @param unknown $bike_ids
   * @param unknown $b_rp_ids
   * @param unknown $dt_min
   * @param unknown $dt_max
   * @param unknown $duration
   * @param unknown $bike_editors_sorted
   * @return array[][]|NULL|\App\Util\IntervalTree\IntervalTree
   */
  private function searchCreateRentalPeriodTrees($bike_ids, $b_rp_ids, $dt_min, $dt_max, $bike_editors_sorted)
  {
    // get relevant rental periods 
    $rental_periods = DB::table('rental_places')
    ->join('rental_periods','rental_periods.rental_place_id','=','rental_places.id')
    ->join('rental_period_weekday','rental_period_weekday.rental_period_id','=','rental_periods.id')
    ->whereIn('rental_places.bike_id',$bike_ids)
    ->whereNested(function($query) use ($dt_min, $dt_max) {
      $query->where('rental_periods.date_to','<',$dt_min->format('Y-m-d'));
      $query->orWhere('rental_periods.date_from','>',$dt_max->format('Y-m-d'));
    },'and not')
    ->whereNull('rental_places.deleted_at')
    ->select([
      'rental_places.bike_id as bike_id',
      'rental_places.id as rental_place_id',      
      'rental_periods.id as rental_period_id',
      'rental_periods.no_interrupt',
      'rental_periods.date_from',
      'rental_periods.date_to',
      'rental_periods.time_from',
      'rental_periods.time_to',
      'rental_periods.rental_duration',
      'rental_period_weekday.weekday_id as weekday'
    ])    
    ->get();
    
    //limitations
    $rental_period_ids = $rental_periods->pluck('rental_period_id')->toArray();
    $rental_period_limitations = Db::table('rental_period_rentee_limitations')
    ->whereIn('rental_period_id', $rental_period_ids)
    ->select('rental_period_id', DB::raw('GROUP_CONCAT(`email` SEPARATOR \',\') as emails'))
    ->groupBy('rental_period_id')
    ->get();
    $rental_period_limitations_sorted = [];
    foreach($rental_period_limitations as $item){
      $rental_period_limitations_sorted[$item->rental_period_id] = explode(',', $item->emails);
    }
    
    // get exceptions
    $exceptions = DB::table('rental_period_exceptions')
    ->whereIn('bike_id',$bike_ids)
    ->whereNested(function($query) use ($dt_min, $dt_max) {
      $query->where('date_time_to','<',$dt_min->format('Y-m-d H:i:s'));
      $query->orWhere('date_time_from','>',$dt_max->format('Y-m-d H:i:s'));
    },'and not')
    ->select([
      'id',
      'bike_id',
      'rental_place_id',
      'no_interrupt',
      'available',
      'date_time_from',
      'date_time_to',
      'rental_duration'
    ])
    ->get();
    
    //exception limitations
    $exception_ids = $exceptions->pluck('id')->toArray();
    $exception_limitations = Db::table('rental_period_exception_rentee_limitations')
    ->whereIn('rental_period_exception_id', $exception_ids)
    ->select('rental_period_exception_id', DB::raw('GROUP_CONCAT(`email` SEPARATOR \',\') as emails'))
    ->groupBy('rental_period_exception_id')
    ->get();
    $exception_limitations_sorted = [];
    foreach($exception_limitations as $item){
      $exception_limitations_sorted[$item->rental_period_exception_id] = explode(',', $item->emails);
    }
    
    // organize rental periods
    $rp_map = [];    
    foreach($rental_periods as $rp) {
      $weekday = $rp->weekday;
      $b_id = $rp->bike_id;
      if(!isset($rp_map[$b_id])) {
        $rp_map[$b_id] = [];
      }
      if(!isset($rp_map[$b_id][$weekday])) {
        $rp_map[$b_id][$weekday] = [];
      }
      
      $rp->limitations = (isset($rental_period_limitations_sorted[$rp->rental_period_id]) ? $rental_period_limitations_sorted[$rp->rental_period_id] : false);
      $rp->editors = $bike_editors_sorted[$b_id];
      $rp_map[$b_id][$weekday][] = $rp;
    }
    
    // organize exceptions
    $e_map = [];
    foreach($exceptions as $e) {
      $b_id = $e->bike_id;
      $e->date_time_from = new Carbon($e->date_time_from);
      $e->date_time_to = new Carbon($e->date_time_to);
      $e->limitations = (isset($exception_limitations_sorted[$e->id]) ? $exception_limitations_sorted[$e->id] : false);
      $e->editors = $bike_editors_sorted[$b_id];
      $date = $e->date_time_from->format('Y-m-d');
      
      if(!isset($e_map[$b_id])) {
        $e_map[$b_id] = [];
      }
      if(!isset($e_map[$b_id][$date])) {
        $e_map[$b_id][$date] = [];
      }
            
      $e_map[$b_id][$date][] = $e;
    }

    // initialize arrays for ranges and trees
    $dt_ranges = [];
    $tmp_buffer = [];
    foreach($b_rp_ids as $b_id => $rp_ids) {             
      $dt_ranges[$b_id] = ['all' => [], 'no_access' => [], 'no_interrupt' => [], 'no_interrupt_except_end' => [], 'duration_data' => []];
      foreach($rp_ids as $rp_id) {
        $dt_ranges[$b_id]['places'][$rp_id] = [[], []];
        $tmp_buffer[$b_id][$rp_id] = [];
      }     
    };
    
    $authed_user = (Auth::check() ? Auth::user() : false);
    
    // create date ranges     
    $curr = clone $dt_min;
    $date = $curr->format('Y-m-d');
    $end = $dt_max->format('Y-m-d');        
    while($date <= $end)
    {      
      $weekday = $curr->dayOfWeekIso;
      foreach($bike_ids as $b_id) {
        if (isset($e_map[$b_id][$date])) {
          foreach($e_map[$b_id][$date] as $e) {
            if(!$e->available) {
              continue;
            }
            
            $range = new CarbonRange($e->date_time_from, $e->date_time_to);
            
            //Rentee Limitations
            if($e->limitations !== false){
              if(($authed_user === false) ||
                (!(in_array($authed_user->id, $e->editors)) && !(in_array($authed_user->email, $e->limitations)))){
                  $dt_ranges[$b_id]['no_access'][] = $range;
                  continue;
              }
            }
            
            $dt_ranges[$b_id]['duration_data'][] = ['range' => $range, 'rental_duration' => $e->rental_duration];
            
            if (!$e->no_interrupt) {
              $dt_ranges[$b_id]['all'][] = $range;
              $dt_ranges[$b_id]['places'][$e->rental_place_id][0][] = $range;
            } else {
              $dt_ranges[$b_id]['no_interrupt'][] = $range;
              $buffer_item = ['from' => $e->date_time_from,'to' => $e->date_time_to];
              $tmp_buffer[$b_id][$e->rental_place_id][] = $buffer_item;
            }            
          }                    
        } else if (isset($rp_map[$b_id][$weekday])) {
          $entries = $rp_map[$b_id][$weekday];
          foreach($entries as $e) {
            if ($e->date_from > $date ||  $e->date_to < $date) {
              continue;
            }
            
            $from_str = $date . ' ' . $e->time_from;
            $to_str = $date . ' ' . $e->time_to;
            $from = new Carbon($from_str);
            $to = new Carbon($to_str);
            $range = new CarbonRange($from, $to);
            
            //Rentee Limitations
            if($e->limitations !== false){
              if(($authed_user === false) ||
                (!(in_array($authed_user->id, $e->editors)) && !(in_array($authed_user->email, $e->limitations)))){
                  $dt_ranges[$b_id]['no_access'][] = $range;
                  continue;
              }
            }
            
            $dt_ranges[$b_id]['duration_data'][] = ['range' => $range, 'rental_duration' => $e->rental_duration];

            if(!$e->no_interrupt) {
              $dt_ranges[$b_id]['all'][] = $range;
              $dt_ranges[$b_id]['places'][$e->rental_place_id][0][] = $range;
            } else {
              $dt_ranges[$b_id]['no_interrupt'][] = $range;
              $buffer_item = ['from' => $from, 'to' => $to];
              $tmp_buffer[$b_id][$e->rental_place_id][] = $buffer_item;
            }  
          }
        }
      }
      $curr->addDay();
      $date = $curr->format('Y-m-d'); 
    }
    
    // merge no-interrupt ranges if applicable     
    foreach($tmp_buffer as $b_id => $data) {
      foreach($data as $rp_id => $entries) {
        $sorted = collect($entries)->sortBy('from')->values();        
        for($i = 0; $i < ($sorted->count() - 1); $i++) {          
          $curr = $sorted[$i];
          $next = $sorted[$i + 1];          
                              
          if ($next['from']->equalTo($curr['to']) || 
            (
              $curr['to']->format('H:i') == "23:59" &&
              $next['from']->format('H:i') == "00:00" &&
              $curr['to']->diffInMinutes($next['from']) == 1
            )) {
            $curr['to'] = $next['to'];
            $sorted[$i + 1] = $curr;
            $sorted[$i] = null;
          }
        }        
        foreach($sorted as $item) {
          if (!$item) continue; 
          
          $range = new CarbonRange($item['from'],$item['to']);
          $dt_ranges[$b_id]['places'][$rp_id][1][] = $range;
        }         
      }
    }
    
    //create the trees    
    foreach($dt_ranges as $b_id => $data) {
      $dt_ranges[$b_id]['all'] = (empty($data['all']) ? null : new IntervalTree($data['all']));
      $dt_ranges[$b_id]['no_access'] = (empty($data['no_access']) ? null : new IntervalTree($data['no_access']));
      $dt_ranges[$b_id]['no_interrupt'] = (empty($data['no_interrupt']) ? null : new IntervalTree($data['no_interrupt']));
      if(!(empty($data['no_interrupt']))) {
        $tmp_no_interrupt = [];
        foreach($data['no_interrupt'] as $tmp_data){
          if(!(($dt_max >= $tmp_data->getStart()) && ($dt_max <= $tmp_data->getEnd()))) {
            $tmp_no_interrupt[] = $tmp_data;
          }
        }
        $dt_ranges[$b_id]['no_interrupt_except_end'] = (empty($tmp_no_interrupt) ? null : new IntervalTree($tmp_no_interrupt));
      } else {
        $dt_ranges[$b_id]['no_interrupt_except_end'] = null;
      }
      foreach($data['places'] as $rp_id => $ranges) {
        $dt_ranges[$b_id]['places'][$rp_id][0] = (empty($ranges[0]) ? null : new IntervalTree($ranges[0]));
        $dt_ranges[$b_id]['places'][$rp_id][1] = (empty($ranges[1]) ? null : new IntervalTree($ranges[1]));
      }      
    }
    
    return $dt_ranges;
  }
  
  /**
   * Create reservation trees.
   * 
   * @param unknown $bike_ids
   * @param unknown $dt_min
   * @param unknown $dt_max
   * @return \App\Util\IntervalTree\IntervalTree[]
   */
  private function searchCreateReservationsTrees($bike_ids, $dt_min, $dt_max, $ignored_reservation = null)
  {
    // get relevant reservations for given bikes
    $reservation_query = DB::table('bike_reservations')
    ->whereIn('bike_id',$bike_ids)
    ->whereNull('deleted_at')
    ->whereNested(function($query) use ($dt_min, $dt_max) {
      $query->where('reserved_to','<',$dt_min->format('Y-m-d H:i:s'));
      $query->orWhere('reserved_from','>',$dt_max->format('Y-m-d H:i:s'));
    },'and not')
    ->select([
      'id',
      'bike_id',
      'reserved_from',
      'reserved_to',
    ]);
    if ($ignored_reservation !== null){
      $reservation_query->where('id', '<>', $ignored_reservation->id);
    }
    
    $reservations = $reservation_query->get();
    
    // define ranges for reservations and group to bikes
    $ranges = [];
    foreach($reservations as $r) {
      if (!isset($ranges[$r->bike_id])) {
        $ranges[$r->bike_id] = [];
      }
      $ranges[$r->bike_id][] = new CarbonRange(new Carbon($r->reserved_from), new Carbon($r->reserved_to),null, false);
    }
    
    // create interval trees out of the defined ranges
    $trees = [];
    foreach($ranges as $bike_id => $ranges) {
      $trees[$bike_id] = new IntervalTree($ranges);
    }
    
    return $trees;
  }
  
  /**
   * Search filter no reservations.
   * 
   * @param unknown $query
   * @param unknown $dp
   */
  private function searchFilterNoReservations($query, $dp, $ignored_reservation = null)
  {
    $query->whereNotExists(function ($query) use ($dp, $ignored_reservation) {
      $query->select(DB::raw(1))
      ->from('bike_reservations', 'br')
      ->whereRaw('br.bike_id = bikes.id')
      ->where('br.deleted_at', null)
      ->whereNested(function ($query) use ($dp) {
        // reservations covering the start window
        $query->whereNested(function ($query) use ($dp) {
          $query->where('br.reserved_from', '<', $dp['from_min_dt'])
            ->where('br.reserved_to', '>', $dp['from_max_dt']);
        }, 'or');
        // reservations covering the return window
        $query->whereNested(function ($query) use ($dp) {
          $query->where('br.reserved_from', '<', $dp['to_min_dt'])
            ->where('br.reserved_to', '>', $dp['to_max_dt']);
        }, 'or');
        // reservations covering everything
        $query->whereNested(function ($query) use ($dp) {
          $query->where('br.reserved_from', '<', $dp['from_min_dt'])
            ->where('br.reserved_to', '>', $dp['to_max_dt']);
        }, 'or');
        // reservations between open window and return window
        $query->whereNested(function ($query) use ($dp) {
          $query->where('br.reserved_from','>',$dp['from_max_dt']);
          $query->where('br.reserved_to','<',$dp['to_min_dt']);
        });
      });
      if ($ignored_reservation !== null){
        $query->where('br.id', '<>', $ignored_reservation->id);
      }
    });
  }
  
}