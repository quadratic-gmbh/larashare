<?php
namespace App\Http\Controllers;


use App\Bike;
use App\BikeReservation;
use App\BoxType;
use App\Embed;
use App\PricingType;
use App\RentalMode;
use App\RentalPeriod;
use App\RentalPeriodException;
use App\RentalPeriodExceptionRenteeLimitation;
use App\RentalPeriodRenteeLimitation;
use App\RentalPlace;
use App\RentalPlaceEmail;
use App\User;
use App\Rules\RentalPeriodOverlap;
use App\Rules\RentalPeriodExceptionTimeOverlap;
use App\Rules\RequiredDateTime;
use App\Rules\RequiredTime;
use App\Http\Requests\BikeFormRequest;
use App\Jobs\GeocodeRentalPlace;
use App\Services\ImageService;
use App\Services\Notifier;
use App\Services\SearchEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

class BikeController extends Controller
{ 
  /**
   * Display a listing of the resource.
   * 
   * @param Request $request
   * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
   */
  public function index(Request $request)
  {
    $user = $request->user();
    
    $bikes = $user->bikes;
    $editable_bikes = $user->editableBikes;
    
    return view('bike.index',[
      'bikes' => $bikes,
      'editable_bikes' => $editable_bikes
    ]);
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    list(
      $wheels_options, $children_options, $electric_options,
      $box_type_options
      ) = $this->getBikeFormOptions();
      
      return view('bike.form', [
        'bike' => null,
        'wheels_options' => $wheels_options,
        'children_options' => $children_options,
        'electric_options' => $electric_options,
        'box_type_options' => $box_type_options
      ]);
  }
  
  /**
   * round given fields to 2 digits 
   *  
   * @param array $bike_data
   */
  private function fixPrices(&$bike_data) 
  {
    // round and fix decimal signs
    $field = 'pricing_deposit';
    if ($bike_data[$field]) {
      $value = $bike_data[$field];
      $value = str_replace(',','.', $value);
      $value = round(floatval($value), 2);
      $bike_data[$field] = $value;
    }
    if($bike_data['pricing_values']){
      foreach(['hourly', 'daily', 'weekly'] as $field) {
        if ($bike_data['pricing_values'][$field]) {
          $value = $bike_data['pricing_values'][$field];
          $value = str_replace(',','.', $value);
          $value = round(floatval($value), 2);
          $bike_data['pricing_values'][$field] = $value;
        }
      }
    }    
  }
  
  /**
   * Store a newly created resource in storage.
   * 
   * @param BikeFormRequest $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function store(BikeFormRequest $request)
  {
    $bike_data = [
      'user_id' => $request->user()->id,
      'name' => strip_tags($request->input('name')),
      'model' => strip_tags($request->input('model')),
      'wheels' => $request->input('wheels'),
      'children' => $request->input('children'),
      'electric' => $request->input('electric'),
      'box_type_id' => $request->input('box_type_id'),
      'cargo_weight' => $request->input('cargo_weight'),
      'cargo_length' => $request->input('cargo_length'),
      'cargo_width' => $request->input('cargo_width'),
      'misc_equipment' => ($request->input('misc_equipment') ? strip_tags($request->input('misc_equipment')) : $request->input('misc_equipment')),
      'description' => ($request->input('description') ? strip_tags($request->input('description')) : $request->input('description')),
      'buffer_time_before' => $request->input('buffer_time_before'),
      'buffer_time_after' => $request->input('buffer_time_after'),
      'pricing_values' => null,
      'pricing_deposit' => $request->input('pricing_deposit')
    ];
    
    if($request->input('pricing_free') && $request->input('pricing_donation')){
      $bike_data['pricing_type_id'] = PricingType::getFreeOrDonationId();
    }elseif($request->input('pricing_free')){
      $bike_data['pricing_type_id'] = PricingType::getFreeId();
    }elseif($request->input('pricing_donation')){
      $bike_data['pricing_type_id'] = PricingType::getDonationId();
    }else{
      $bike_data['pricing_type_id'] = PricingType::getFixedId();
      $bike_data['pricing_values'] = [
        'hourly' => $request->input('pricing_value_hourly'),
        'daily' => $request->input('pricing_value_daily'),
        'weekly' => $request->input('pricing_value_weekly'),
      ];
    } 
    
    if($terms_of_use_file = $request->file('terms_of_use_file')){
      $bike_data['terms_of_use_file'] = 1;
    }
    
    $this->fixPrices($bike_data);
    
    $bike = Bike::Create($bike_data);
    
    if($terms_of_use_file){
      Storage::putFileAs('bikes/' . $bike->id . '/', $terms_of_use_file, 'terms_of_use.pdf');
    }
    
    //rental place
    $rental_places = $request->input('rental_place');
    foreach($rental_places as $rental_place_item){
      $rental_place_data = [
        'bike_id' => $bike->id,
        'name' => strip_tags($rental_place_item['name']),
        'street_name' => strip_tags($rental_place_item['street_name']),
        'house_number' => strip_tags($rental_place_item['house_number']),
        'postal_code' => strip_tags($rental_place_item['postal_code']),
        'city' => strip_tags($rental_place_item['city']),
        'description' => ($rental_place_item['description'] ? strip_tags($rental_place_item['description']) : $rental_place_item['description'])
      ];
      
      $rental_place = RentalPlace::Create($rental_place_data);
      GeocodeRentalPlace::dispatch($rental_place);
      
      //rental place email
      foreach($rental_place_item['email'] as $email_item){
        $rental_place_email_data = [
          'rental_place_id' => $rental_place->id,
          'email' => $email_item['email'],
          'notify_on_reservation' => $email_item['notify_on_reservation'],
        ];
        $rental_place_email = RentalPlaceEmail::create($rental_place_email_data);
      }
    }
    
    return redirect()->route('bike.images',['bike_id' => $bike->id]);
  }

  /**
   * Embed show.
   * 
   * @param Request $request
   * @param Embed $embed
   * @param unknown $bike_id
   * @return \Illuminate\Http\Response
   */
  public function embedShow(Request $request, Embed $embed, $bike_id)
  {
    return $this->show($request, $bike_id, $embed);
  }
  
  /**
   * Display the specified resource.
   *
   * @param int $bike_id
   * @return \Illuminate\Http\Response
   */
  public function show(Request $request, $bike_id, Embed $embed = null)
  {
    $bike = Bike::where([
      'public' => true 
    ])
    ->with([
      'pricingType',
      'images',
      'rentalPlaces'
    ])
    ->findOrFail($bike_id);
    
    $now = now();
    $reservation_id = $request->query('reservation_id');
    $reservation = false;
    if ($reservation_id && is_numeric($reservation_id)){
      if (Auth::check()){
        $user = Auth::user();
        $reservation = BikeReservation::where('reserved_to','>=',$now->format('Y-m-d H:i:s'))
        ->where([
          ['user_id','=',$user->id],
          ['bike_id','=',$bike->id]
        ])
        ->findOrFail($reservation_id);
        BikeReservation::removeBufferTimes($reservation);
      }
    }
    
    $rental_modes = RentalMode::all();
    $rental_mode_colors = [
      RentalMode::getInstantReservationId() => 'cal-evt-instant',
      RentalMode::getInquiryId() => 'cal-evt-inquiry',
    ];
    
    $date = ($reservation ? date_create($reservation->reserved_from) : date_create($request->query('date')));
    if(!$date) {
      $date = date_create();
    }
    
    $reserve_from = $request->query('reserve_from') ? date_create($request->query('reserve_from')) :  (old('reserve_from') ? date_create(old('reserve_from')) : ($reservation ? date_create($reservation->reserved_from) : $date));
    $reserve_to = $request->query('reserve_to') ? date_create($request->query('reserve_to')) : (old('reserve_to') ? date_create(old('reserve_to')) : ($reservation ? date_create($reservation->reserved_to) : $date)); 
    $purpose = $request->query('purpose') ? $request->query('purpose') : (old('purpose') ? old('purpose') : ($reservation ? $reservation->purpose : null));
    
    $view_data = [
      'bike' => $bike,
      'rental_modes' => $rental_modes,
      'rental_mode_colors' => $rental_mode_colors,      
      'date' => $date->format('Y-m-d'),
      'reserve_from' => $reserve_from,
      'reserve_to' => $reserve_to,
      'purpose' => $purpose,
      'reservation_id' => ($reservation ? $reservation->id : false),
      'embed' => $embed
    ];
    
    return view('bike.show',$view_data);
  }

  /**
   * Calendar with reservations of a bike.
   * 
   * @param Request $request
   * @param int $bike_id
   */
  public function reservations(Request $request, int $bike_id)
  {
    $bike = Bike::findOrFail($bike_id);
    $this->authorize('modify',$bike);
    
    $rental_modes = RentalMode::all();
    $rental_mode_colors = [
      RentalMode::getInstantReservationId() => 'cal-evt-instant',
      RentalMode::getInquiryId() => 'cal-evt-inquiry',
    ];
    
    $date = date_create($request->query('date'));
    if (!$date) {
      $date = now();
    }
    
    $view_data = [
      'bike' => $bike,
      'rental_modes' => $rental_modes,
      'rental_mode_colors' => $rental_mode_colors,
      'date' => $date->format('Y-m-d')
    ];
        
    return view('bike.reservations',$view_data);
  }
  
  /**
   * Show detail information of a reservation.
   * 
   * @param Request $request
   * @param SearchEngine $search_engine
   * @param int $bike_id
   * @param int $r_id
   * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
   */
  public function reservation(Request $request, SearchEngine $search_engine, int $bike_id, int $r_id)
  {
    $bike = Bike::findOrFail($bike_id);
    $this->authorize('modify',$bike);
    
    $reservation = $bike->reservations() 
    ->with('user')
    ->findOrFail($r_id);
    
    BikeReservation::removeBufferTimes($reservation);
    
    $rental_places = $bike->rentalPlaces;
    $rental_places_map = $rental_places->mapWithKeys(function($item) {
      return [$item->id => $item];
    });
      
    $rental_place_ids = $search_engine->findRentalPlacesForReservation($reservation);
    $rental_place_from = $rental_places_map[$rental_place_ids[0]] ?? null;
    $rental_place_to = $rental_places_map[$rental_place_ids[1]] ?? null;
    
    $now = now();    
    $view_data = [
      'bike' => $bike,
      'reservation' => $reservation,
      'old' => ($reservation->reserved_to < $now),
      'old_from' => ($reservation->reserved_from < $now),
      'date' => $reservation->reserved_from->format('Y-m-d'),
      'rental_place_from' => $rental_place_from,
      'rental_place_to' => $rental_place_to,
    ];
    
    return view('bike.reservation',$view_data);
  }
  
  /**
   * Cancel reservation.
   * 
   * @param Request $request
   * @param int $bike_id
   * @param int $r_id
   * @return \Illuminate\Http\RedirectResponse
   */
  public function reservationCancel(Request $request, SearchEngine $search_engine, int $bike_id, int $r_id)
  {
    $bike = Bike::findOrFail($bike_id);
    $this->authorize('modify',$bike);
    
    $now = now();
    
    $reservation = $bike->reservations()
      ->with('user')
      ->where('reserved_from','>=',$now->format('Y-m-d H:i:s'))
      ->whereNotNull('confirmed_on')
      ->findOrFail($r_id)
    ;
    
    BikeReservation::removeBufferTimes($reservation);
    
    $rental_places = $bike->rentalPlaces;
    $rental_places_map = $rental_places->mapWithKeys(function($item) {
      return [$item->id => $item];
    });
    
    $rental_place_ids = $search_engine->findRentalPlacesForReservation($reservation);
    $rental_place_from = $rental_places_map[$rental_place_ids[0]] ?? null;
    $rental_place_to = $rental_places_map[$rental_place_ids[1]] ?? null;
    
    return view('bike.reservation_cancel',[
      'bike' => $bike,
      'reservation' => $reservation,
      'rental_place_from' => $rental_place_from,
      'rental_place_to' => $rental_place_to,
    ]);
  }
  
  /**
   * Confirm cancel reservation.
   * 
   * @param Request $request
   * @param Notifier $notifier
   * @param Integer $bike_id
   * @param Integer $r_id
   */
  public function reservationCancelSubmit(Request $request, Notifier $notifier, int $bike_id, int $r_id)
  {
    $bike = Bike::findOrFail($bike_id);
    $this->authorize('modify',$bike);
    
    $now = now();
    
    $reservation = $bike->reservations()
      ->with('user')
      ->where('reserved_from','>=',$now->format('Y-m-d H:i:s'))
      ->whereNotNull('confirmed_on')
      ->findOrFail($r_id)
    ;
    
    BikeReservation::removeBufferTimes($reservation);
    
    $notifier->notifyUserAboutCancellation($reservation);
    
    $date = $reservation->reserved_from->format('Y-m-d');
    $reservation->delete();
    return redirect()->route('bike.reservations',['bike_id' => $bike_id,'date' => $date]);
  }
  
  /**
   * Confirm/deny reservation.
   * 
   * @param Request $request
   * @param int $bike_id
   * @param int $r_id
   * @return \Illuminate\Http\RedirectResponse
   */
  public function reservationConfirm(Request $request, Notifier $notifier, int $bike_id, int $r_id)
  {
    $bike = Bike::findOrFail($bike_id);    
    $this->authorize('modify',$bike);
    
    $reservation = $bike->reservations()
    ->findOrFail($r_id);
    
    
    $reservation_clone = clone $reservation;
    BikeReservation::removeBufferTimes($reservation_clone);
    
    $now = now();
    if ($reservation_clone->reserved_to < now()) {
      Log::error("Attempted to confirm/deny an old reservation (id:{$reservation_clone->id}");
      abort(500);
    }
    
    if (!$request->has('confirm')) {
      abort(400);
    }
    
    $confirm = $request->input('confirm') === '1';
    $date = $reservation_clone->reserved_from->format('Y-m-d');
    
    if($confirm) {
      $reservation->confirmed_on = now();
      $reservation->update();
      $notifier->notifyUserAboutConfirmation($reservation_clone);
    } else {
      $notifier->notifyUserAboutCancellation($reservation_clone);
      $reservation->delete();
      if($request->input('confirm') === '-1'){
        return redirect()->route('chat.bikeuser', ['bike_id' => $reservation->bike_id, 'user_id' => $reservation->user_id]);
      }
    }
        
    return redirect()->route('bike.reservations', ['bike_id' => $bike_id,'date' => $date]);
  }
  
  /**
   * Reserve a bike.
   * 
   * @param Request $request
   * @param SearchEngine $search_engine
   * @param Notifier $notifier
   * @param int $bike_id
   * @return \Illuminate\Http\RedirectResponse
   */
  public function reserve(Request $request, SearchEngine $search_engine, Notifier $notifier,  int $bike_id) 
  {
    $bike = Bike::where([
      'public' => true
    ])
    ->findOrFail($bike_id);
    
    $query_date = date_create($request->query('date'));    
    $date = null;
    if ($query_date != false) {
      $date = $query_date;
    }
    
    $input = $request->except('_token');
    $input_validation = $input;
    $from_tmp = Carbon::createFromFormat('Y-m-d H:i', $input_validation['reserve_from'], 'Europe/Vienna');
    $from_tmp->setTimezone('UTC');
    $to_tmp = Carbon::createFromFormat('Y-m-d H:i', $input_validation['reserve_to'], 'Europe/Vienna');
    $to_tmp->setTimezone('UTC');
    $input_validation['reserve_from'] = $from_tmp->format('Y-m-d H:i');
    $input_validation['reserve_to'] = $to_tmp->format('Y-m-d H:i');
    
    $rules = [
      'purpose' => ['nullable','string'],
      'reserve_to' =>  [new RequiredDateTime(),'date','after:reserve_from'],
      'reservation_id' => ['nullable','integer']
    ];
    
    $reservation_id = $request->query('reservation_id');
    if ($reservation_id) {
      $rules['reserve_from'] = [new RequiredDateTime(),'date','max:512'];
    } else {
      $rules['reserve_from'] = [new RequiredDateTime(),'date','max:512','after_or_equal:now'];
    }
    
    if($bike->terms_of_use_file){
      $rules['accept_bike_tos'] = ['required','accepted'];
    }

    $return_params = ['bike_id' => $bike->id];
    if ($reservation_id && is_numeric($reservation_id)) {
      $return_params['reservation_id'] = $reservation_id;
    } else {
      $return_params['date'] = $date->format('Y-m-d');
    }
    
    $validator = Validator::make($input_validation, $rules);
    if ($validator->fails()) {      
      return redirect()->route('bike.show', $return_params)
      ->withErrors($validator)
      ->withInput($input);
    }
    
    $from = new Carbon($input['reserve_from']);
    $to = new Carbon($input['reserve_to']);
    
    $duration = $from->diffInMinutes($to);
    // convert back to hours but round up
    $duration = intval(ceil($duration/60));          
    $date = $from;
    
    $now = now();
    $reservation = null;
    if ($reservation_id){
      $user = Auth::user();
      $reservation = BikeReservation::where('reserved_to','>=',$now->format('Y-m-d H:i:s'))
      ->where([
        ['user_id','=',$user->id],
        ['bike_id','=',$bike->id]
      ])
      ->findOrFail($reservation_id);
      BikeReservation::removeBufferTimes($reservation);
    }
    
    //buffer times
    $from_buffer = clone $from;
    $to_buffer = clone $to;
    $validate_buffer = false;
    
    if($bike->buffer_time_before){
      $from_buffer->subMinutes($bike->buffer_time_before);
      $validate_buffer = true;
    }
    if($bike->buffer_time_after){
      $validate_buffer = true;
      $to_buffer->addMinutes($bike->buffer_time_after);
    }
    if($validate_buffer && !$search_engine->validateReservationBuffer($from_buffer, $to_buffer, $bike, $reservation)){
      return redirect()->route('bike.show', $return_params)
      ->withErrors(['invalid_timespan' =>  __('validation.custom.reservation.reserve_buffer_overlap', ['buffer_time_before' => $bike->buffer_time_before, 'buffer_time_after' => $bike->buffer_time_after])])
      ->withInput($input);
    }
    
    $validate_reservation = $search_engine->validatePossibleReservation($from, $to, $bike, $duration, $reservation);
    if($validate_reservation !== true) {  
      return redirect()->route('bike.show', $return_params)
      ->withErrors(['invalid_timespan' =>  __('validation.custom.reservation.'.$validate_reservation)])
      ->withInput($input);
    }
    
    if ($reservation && ($reservation->reserved_from->lt($now)) && (!$reservation->reserved_from->eq($from))) {
      return redirect()->route('bike.show', $return_params)
      ->withErrors(['invalid_timespan' =>  __('validation.custom.reservation.reserve_from_on_edit_current')])
      ->withInput($input);
    }
    
    // determine rental mode
    list($is_inquiry, $rental_place_ids) = $search_engine->checkRentalPlaces($from, $to, $bike);
    
    $purpose = isset($input['purpose']) ? strip_tags($input['purpose']) : null;
    // reserve the bike
    $reservation_data = [
      'bike_id' => $bike->id,
      'user_id' => $request->user()->id,
      'reserved_from' => $from_buffer,
      'reserved_to' => $to_buffer,
      'purpose' => $purpose,
      'confirmed_on' => ($is_inquiry ? null : now())
    ];
    if($bike->buffer_time_before){
      $reservation_data['buffer_time_before'] = $bike->buffer_time_before;
    }
    if($bike->buffer_time_after){
      $reservation_data['buffer_time_after'] = $bike->buffer_time_after;
    }
    
    $is_update = false;
    if ($reservation) {
      $reservation->update($reservation_data);
      $is_update = true;
    } else {
      $reservation = BikeReservation::create($reservation_data); 
    }
    
    BikeReservation::removeBufferTimes($reservation);
      
    $notifier->notifyRentalPlacesAboutReservation($bike, $reservation, $rental_place_ids, $is_inquiry, $is_update);
    $notifier->notifyUserNewReservation($bike, $reservation, $is_inquiry, $is_update); 
    
    return redirect()->route('user.reservation', ['id' => $reservation->id]);
  }
  
  /**
   * Show the form for editing the specified resource.
   *
   * @param int $bike_id
   * @return \Illuminate\Http\Response
   */
  public function edit(Request $request, $bike_id)
  {
    $bike = Bike::with('rentalPlaces.emails')
    ->withCount('rentalPlaces')
    ->withCount('rentalPeriods')
    ->findOrFail($bike_id);    
    $this->authorize('modify',$bike);
    
    list(
      $wheels_options, $children_options, $electric_options,
      $box_type_options
      ) = $this->getBikeFormOptions();      
      
    return view('bike.form', [
      'bike' => $bike,
      'wheels_options' => $wheels_options,
      'children_options' => $children_options,
      'electric_options' => $electric_options,
      'box_type_options' => $box_type_options
    ]);
  }
  
  /**
   * Update the specified resource in storage.
   * 
   * @param BikeFormRequest $request
   * @param int $bike_id
   * @return \Illuminate\Http\RedirectResponse
   */
  public function update(BikeFormRequest $request, $bike_id)
  {
    $bike = Bike::with('rentalPlaces.emails')->findOrFail($bike_id);
    $this->authorize('modify',$bike);    
    
    $bike_data = [
      'name' => strip_tags($request->input('name')),
      'model' => strip_tags($request->input('model')),
      'wheels' => $request->input('wheels'),
      'children' => $request->input('children'),
      'electric' => $request->input('electric'),
      'box_type_id' => $request->input('box_type_id'),
      'cargo_weight' => $request->input('cargo_weight'),
      'cargo_length' => $request->input('cargo_length'),
      'cargo_width' => $request->input('cargo_width'),
      'misc_equipment' => ($request->input('misc_equipment') ? strip_tags($request->input('misc_equipment')) : $request->input('misc_equipment')),
      'description' => ($request->input('description') ? strip_tags($request->input('description')) : $request->input('description')),
      'buffer_time_before' => $request->input('buffer_time_before'),
      'buffer_time_after' => $request->input('buffer_time_after'),
      'pricing_values' => null,
      'pricing_deposit' => $request->input('pricing_deposit')
    ];
    
    if($request->input('pricing_free') && $request->input('pricing_donation')){
      $bike_data['pricing_type_id'] = PricingType::getFreeOrDonationId();
    }elseif($request->input('pricing_free')){
      $bike_data['pricing_type_id'] = PricingType::getFreeId();
    }elseif($request->input('pricing_donation')){
      $bike_data['pricing_type_id'] = PricingType::getDonationId();
    }else{
      $bike_data['pricing_type_id'] = PricingType::getFixedId();
      $bike_data['pricing_values'] = [
        'hourly' => $request->input('pricing_value_hourly'),
        'daily' => $request->input('pricing_value_daily'),
        'weekly' => $request->input('pricing_value_weekly'),
      ];
    } 
    
    $this->fixPrices($bike_data);
    
    $terms_of_use_file = false;
    if(!$bike->no_tos_upload){
      if($request->input('delete_terms_of_use_file')){
        $bike_data['terms_of_use_file'] = 0;
        Storage::delete('bikes/' . $bike->id . '/terms_of_use.pdf');
      }else{
        if($terms_of_use_file = $request->file('terms_of_use_file')){
          $bike_data['terms_of_use_file'] = 1;
        }
      }
    }
    
    $bike->update($bike_data);
    
    if($terms_of_use_file){
      Storage::putFileAs('bikes/' . $bike->id . '/', $terms_of_use_file, 'terms_of_use.pdf');
    }
    
    //rental place
    $rental_places = $request->input('rental_place');
    $updated_email_ids = [];
    foreach($rental_places as $rental_place_item){
      $rental_place_data = [
        'name' => strip_tags($rental_place_item['name']),
        'street_name' => strip_tags($rental_place_item['street_name']),
        'house_number' => strip_tags($rental_place_item['house_number']),
        'postal_code' => strip_tags($rental_place_item['postal_code']),
        'city' => strip_tags($rental_place_item['city']),
        'description' => ($rental_place_item['description'] ? strip_tags($rental_place_item['description']) : $rental_place_item['description'])
      ];
      
      //update existing
      if(isset($rental_place_item['id'])){
        $rental_place = false;
        foreach($bike['rentalPlaces'] as $brp){
          if($rental_place_item['id'] == $brp->id){
            $rental_place = $brp;
            break;
          }
        }
        
        foreach(['house_number', 'street_name', 'postal_code', 'city'] as $field){
          if($rental_place->$field != $rental_place_data[$field]){
            foreach(['lon', 'lat'] as $lonlat){
              $rental_place_data[$lonlat] = null;
            }
            GeocodeRentalPlace::dispatch($rental_place);
            break;
          }
        }
        
        $rental_place->update($rental_place_data);
        
        foreach($rental_place_item['email'] as $email_item){
          $rental_place_email_data = [
            'email' => $email_item['email'],
            'notify_on_reservation' => $email_item['notify_on_reservation'],
          ];
          
          //update existing
          if(isset($email_item['id'])){
            $email = false;
            foreach($rental_place['emails'] as $rpem){
              if($email_item['id'] == $rpem->id){
                $email = $rpem;
                break;
              }
            }
            $updated_email_ids[] = $email->id;
            $email->update($rental_place_email_data);
          }else{
            $rental_place_email_data['rental_place_id'] = $rental_place->id;
            RentalPlaceEmail::create($rental_place_email_data);
          }
        }
      }else{
        $rental_place_data['bike_id'] = $bike->id;
        $rental_place = RentalPlace::Create($rental_place_data);
        GeocodeRentalPlace::dispatch($rental_place);
        
        foreach($rental_place_item['email'] as $email_item){
          $rental_place_email_data = [
            'rental_place_id' => $rental_place->id,
            'email' => $email_item['email'],
            'notify_on_reservation' => $email_item['notify_on_reservation'],
          ];
          $rental_place_email = RentalPlaceEmail::create($rental_place_email_data);
          $updated_email_ids[] = $rental_place_email->id;
        }
      }
    }
    
    //delete e-mails
    foreach($bike['rentalPlaces'] as $rental_place){
      foreach($rental_place['emails'] as $email){
        if(!in_array($email->id, $updated_email_ids)){
          $email->delete();
        }
      }
    }
    
    // flash success save to next page load
    $request->session()->flash('update_success',true);
    
    return redirect()->route('bike.edit',['bike_id' => $bike_id]);
  }
  
  /**
   * Confirmation for removing the specified resource from storage.
   * 
   * @param Request $request
   * @param int $bike_id
   * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
   */
  public function destroyAsk(Request $request, $bike_id)
  {
    $bike = Bike::findOrFail($bike_id);
    $this->authorize('delete',$bike);
    
    return view('general.destroy_ask', [
      'header' => __('bike.destroy_ask.header_bike', ['name' => $bike['name']]),
      'route' => route('bike.destroy', ['bike_id' => $bike]),
      'route_back' => route('bike.index')
    ]);
  }
  
  /**
   * Remove the specified resource from storage.
   *
   * @param Request $request
   * @param int $bike_id
   * @return \Illuminate\Http\Response
   */
  public function destroy(Request $request, $bike_id)
  {
    $bike = Bike::findOrFail($bike_id);
    $this->authorize('delete',$bike);    
    
    $bike->delete();
    
    return redirect()->route('bike.index');
  }
  
  /**
   * Download TOS.
   * 
   * @param Request $request
   * @param unknown $bike_id
   * @return unknown
   */
  public function downloadTOS(Request $request, $bike_id)
  {
    $bike = Bike::findOrFail($bike_id);
    if(!$bike->terms_of_use_file){
      abort(404);
    }
    
    if(!Storage::exists('bikes/' . $bike->id . '/'.'terms_of_use.pdf')){
     abort(404);
    }
    
    return Storage::download('bikes/' . $bike->id . '/'.'terms_of_use.pdf', $bike->name.'-terms_of_use.pdf', ['Content-Disposition' => 'inline']);
  }
  
  /**
   * Confirmation for removing the specified resource from storage.
   * 
   * @param Request $request
   * @param int $bike_id
   * @param int $rental_place_id
   * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
   */
  public function rentalPlaceDestroyAsk(Request $request, $bike_id, $rental_place_id)
  {
    $bike = Bike::withCount('rentalPlaces')->findOrFail($bike_id);
    $this->authorize('modify',$bike);
        
    if($bike->rental_places_count == 1){
      abort(404);
    }
    
    $rental_place = $bike->rentalPlaces()->findOrFail($rental_place_id);
    
    return view('general.destroy_ask', [
      'header' => __('bike.destroy_ask.header_rental_place', ['name' => $rental_place['name']]),
      'route' => route('bike.rental_place_destroy',['bike_id' => $bike, 'rental_place_id' => $rental_place]),
      'route_back' => route('bike.edit',['bike_id' => $bike])
    ]);
  }
  
  /**
   * Remove the specified resource from storage.
   * 
   * @param Request $request
   * @param int $bike_id
   * @param int $rental_place_id
   * @return \Illuminate\Http\RedirectResponse
   */
  public function rentalPlaceDestroy(Request $request, $bike_id, $rental_place_id)
  {
    $bike = Bike::withCount('rentalPlaces')->findOrFail($bike_id);
    $this->authorize('modify',$bike);
        
    if($bike->rental_places_count == 1){
      abort(404);
    }
    
    $rental_place = $bike->rentalPlaces()->findOrFail($rental_place_id);
    
    $rental_place->delete();
    
    return redirect()->route('bike.edit',['bike_id' => $bike]);
  }
  
  /**
   * Returns the options for the bike form.
   * 
   * @return string[][][]|\Illuminate\Contracts\Translation\Translator[][][]|array[][][]|NULL[][][]
   */
  protected function getBikeFormOptions()
  {
    return [[
      ['value' => 2, 'text' => __('bike.wheels.2')],
      ['value' => 3, 'text' => __('bike.wheels.3')],
      ['value' => 4, 'text' => __('bike.wheels.4')]
    ],
      [
        ['value' => 0, 'text' => trans_choice('bike.children', 0)],
        ['value' => 1, 'text' => trans_choice('bike.children', 1, ['i' => 1])],
        ['value' => 2, 'text' => trans_choice('bike.children', 2, ['i' => 2])],
        ['value' => 3, 'text' => trans_choice('bike.children', 3, ['i' => 3])],
        ['value' => 4, 'text' => trans_choice('bike.children', 4, ['i' => 4])]
      ],
      [
        ['value' => 0, 'text' => __('bike.electric.0')],
        ['value' => 1, 'text' => __('bike.electric.1')]
      ],
      BoxType::getSelectOptions()
    ];
  }
  
  /**
   * Shows bike images.
   * 
   * @param Request $request
   * @param int $bike_id
   * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
   */
  public function images(Request $request, int $bike_id)
  {
    $bike = Bike::with('images')
    ->withCount('rentalPlaces')
    ->withCount('rentalPeriods')
    ->findOrFail($bike_id);
    $this->authorize('modify',$bike);           
    
    return view('bike.images', [
      'bike' => $bike      
    ]);
  }
  
  /**
   * Shows bike image upload.
   * 
   * @param Request $request
   * @param ImageService $img_service
   * @param int $bike_id
   * @return \Illuminate\Http\RedirectResponse|unknown
   */
  public function imageUpload(Request $request, ImageService $img_service, int $bike_id)
  {
    $bike = Bike::findOrFail($bike_id);
    $this->authorize('modify',$bike);
    
    $file = $request->file('file');
    $file_size = 'max:' . config('image.max_size');
    $rules = [
      'file' => ['required','file','mimes:jpeg,jpg,png',$file_size]
    ];
    $validator = Validator::make(['file' => $file], $rules);
    
    if ($validator->fails()) {
      return back()->withErrors($validator);
    }

    // save image and create thumbnails
    $img_service->saveImage($bike, $file);
    
    return redirect()->route('bike.images',['bike_id' => $bike->id]);
  }
  
  /**
   * Shows bike image delete.
   * 
   * @param Request $request
   * @param ImageService $img_service
   * @param int $bike_id
   * @param int $img_id
   * @return \Illuminate\Http\RedirectResponse
   */
  public function imageDelete(Request $request, ImageService $img_service, int $bike_id, int $img_id)
  {
    $bike = Bike::findOrFail($bike_id);
    $this->authorize('modify',$bike);
    
    $image = $bike->images()->findOrFail($img_id);
    
    // delete the image and all the modified versions
    $img_service->deleteImage($bike, $image);
    
    return redirect()->route('bike.images',['bike_id' => $bike->id]);
  }
  
  /**
   * Shows publish bike.
   * 
   * @param Request $request
   * @param int $bike_id
   * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
   */
  public function publish(Request $request, int $bike_id)
  {
    $bike = Bike::has('rentalPeriods')
    ->withCount('rentalPlaces')
    ->withCount('rentalPeriods')
    ->findOrFail($bike_id);    
    $this->authorize('modify',$bike);        
    
    $form_data = [
      'public' => $bike->public,
      'accepts_tos' => $bike->accepts_tos,
      'has_permission' => $bike->has_permission
    ];
    return view('bike.publish',[
      'bike' => $bike,
      'form_data' => $form_data
    ]);
  }
  
  /**
   * Submit publish bike.
   * 
   * @param Request $request
   * @param int $bike_id
   * @return \Illuminate\Http\RedirectResponse|unknown
   */
  public function publishSubmit(Request $request, int $bike_id)
  {
    $bike = Bike::has('rentalPeriods')->findOrFail($bike_id);
    $this->authorize('modify',$bike);    
    
    $input = $request->except('_token');
    
    $validator = Validator::make($input, [
      'public' => ['required','boolean'],
      'accepts_tos' => ['required','boolean', function($attribute, $value, $fail) {
        if (!boolval($value)) {
          $fail(__('validation.custom.bike_publish_accept_tos'));  
        }
      }],
      'has_permission' => ['required','boolean'],
    ]);
    
    if ($validator->fails()) {
      return back()->withInput($input)->withErrors($validator);
    }
    
    // filter inputs
    $filtered = [];
    foreach(['public','accepts_tos','has_permission'] as $field) {
      $filtered[$field] = boolval($input[$field]);
    }
    
    $validator->after(function($validator) use ($filtered) {
      if ($filtered['public'] && (!$filtered['accepts_tos'] || !$filtered['has_permission'])) {
        $validator->errors()->add('public',__('validation.custom.bike_publish_checkboxes'));
      }
    });
    
    if ($validator->fails()) {
      return back()->withInput($input)->withErrors($validator);
    }
    
    foreach($filtered as $field => $value) {
      $bike->$field = $value;
    }
    $bike->save();    
    
    return redirect()->route('bike.index');
  }
  
  /**
   * Checks the selected reservation.
   * 
   * @param Request $request
   * @param SearchEngine $search_engine
   * @param int $bike_id
   * @return \Illuminate\Http\JsonResponse
   */
  public function apiCheckSelection(Request $request, SearchEngine $search_engine, int $bike_id)
  {
    $bike = Bike::where([
      'public' => true
    ])->find($bike_id);
    
    if ($bike === null) {
      return response()->json(['message' => 'Bike not found'],404);
    }
    
    $input = $request->except('_token');
    $input_validation = $input;
    $from_tmp = Carbon::createFromFormat('Y-m-d H:i', $input_validation['reserve_from'], 'Europe/Vienna');
    $from_tmp->setTimezone('UTC');
    $to_tmp = Carbon::createFromFormat('Y-m-d H:i', $input_validation['reserve_to'], 'Europe/Vienna');
    $to_tmp->setTimezone('UTC');
    $input_validation['reserve_from'] = $from_tmp->format('Y-m-d H:i');
    $input_validation['reserve_to'] = $to_tmp->format('Y-m-d H:i');
    
    $rules = [
      'reserve_to' =>  [new RequiredDateTime(),'date','after:reserve_from'],
      'reservation_id' => ['nullable','integer']
    ];
    
    $reservation_id = $request->query('reservation_id');
    if ($reservation_id) {
      $rules['reserve_from'] = [new RequiredDateTime(),'date','max:512'];
    } else {
      $rules['reserve_from'] = [new RequiredDateTime(),'date','max:512','after_or_equal:now'];
    }
   
    
    $validator = Validator::make($input_validation, $rules);
    
    if ($validator->fails()) {
      return response()->json(['possible' => __('validation.custom.reservation.not_available')]);
    }
    
    $from = new Carbon($input['reserve_from']);
    $to = new Carbon($input['reserve_to']);
    
    $duration = $from->diffInMinutes($to);
    // convert back to hours but round up
    $duration = intval(ceil($duration/60));
    $date = $from;
    
    //buffer times
    $from_buffer = clone $from;
    $to_buffer = clone $to;
    $validate_buffer = false;
    
    $now = now();
    $reservation = null;
    if ($reservation_id) {
      if (Auth::check()) {
        $user = Auth::user();
        $reservation = BikeReservation::where('reserved_to','>=',$now->format('Y-m-d H:i:s'))
        ->where([
          ['user_id','=',$user->id],
          ['bike_id','=',$bike->id]
        ])
        ->find($reservation_id);
        if ($reservation) {
          BikeReservation::removeBufferTimes($reservation);
        }
      }
    }
    
    if($bike->buffer_time_before){
      $from_buffer->subMinutes($bike->buffer_time_before);
      $validate_buffer = true;
    }
    if($bike->buffer_time_after){
      $validate_buffer = true;
      $to_buffer->addMinutes($bike->buffer_time_after);
    }
    if($validate_buffer && !$search_engine->validateReservationBuffer($from_buffer, $to_buffer, $bike, $reservation)){
      return response()->json(['possible' => __('validation.custom.reservation.reserve_buffer_overlap', ['buffer_time_before' => $bike->buffer_time_before, 'buffer_time_after' => $bike->buffer_time_after])]);
    }
    
    $validate_reservation = $search_engine->validatePossibleReservation($from, $to, $bike, $duration, $reservation); 
    if($validate_reservation !== true) {
      return response()->json(['possible' => __('validation.custom.reservation.'.$validate_reservation)]);
    }
    
    if ($reservation && ($reservation->reserved_from->lt($now)) && (!$reservation->reserved_from->eq($from))) {
      return response()->json(['possible' => __('validation.custom.reservation.'.'reserve_from_on_edit_current')]);
    }
    
    return response()->json(['possible' => true]);
  }
  
  /**
   * Backend reservations.
   * 
   * @param Request $request
   * @param int $bike_id
   * @return \Illuminate\Http\JsonResponse
   */
  public function apiReservationsBackend(Request $request, int $bike_id)
  {
    $bike = Bike::find($bike_id);        
    if ($bike === null) {
      return response()->json(['message' => 'Bike not found'],404);
    }
    
    $this->authorize('modify',$bike);
    
    return $this->apiGetReservations($request, $bike, true);
  }
  
  /**
   * Reservations.
   * 
   * @param Request $request
   * @param int $bike_id
   * @return \Illuminate\Http\JsonResponse
   */
  public function apiReservations(Request $request, int $bike_id)
  {
    $bike = Bike::find($bike_id);
    
    if ($bike === null) {
      return response()->json(['message' => 'Bike not found'],404);
    }
    
    return $this->apiGetReservations($request, $bike);
  }
  
  /**
   * Get reservations.
   * 
   * @param Request $request
   * @param Bike $bike
   * @param boolean $backend
   * @return \Illuminate\Http\JsonResponse
   */
  private function apiGetReservations(Request $request, Bike $bike, $backend = false)
  {
    $inputs = $request->query();
    $validator = Validator::make($inputs, [
      'start' =>  ['required','date'],
      'end' =>  ['required','date','after:start'],
      'reservation_id' => ['nullable','integer']
    ]);
    
    if ($validator->fails()) {
      return response()->json(['message' => $validator->getMessageBag()->first()],400);
    }
    
    $start = new Carbon($inputs['start']);
    $end = new Carbon($inputs['end']);
    
    $query = DB::table('bike_reservations')
    ->where([
      'deleted_at' => null,
      'bike_id' => $bike->id
    ])
    ->whereNested(function($query) use ($start, $end) {
      $query->where(DB::raw('DATE(reserved_to)'),'<',$start->format('Y-m-d'))
      ->orWhere(DB::raw('DATE(reserved_from)'),'>',$end->format('Y-m-d'));
    },'and not')
    ->select([
      'reserved_from as start',
      'reserved_to as end',
      'buffer_time_before',
      'buffer_time_after',
      DB::raw('IFNULL(purpose,"") as title')
    ]);
    if ($backend) {
      $query->addSelect([
        DB::raw('!ISNULL(confirmed_on) as confirmed'),
        DB::raw('CONCAT("reservations/", id) as url')
      ]);
    }
    $reservation_id = $request->query('reservation_id');
    if ($reservation_id){
      $query->where('id', '<>', $reservation_id);
    }
    
    $reservations = $query->get();
    
    foreach($reservations as $res){
      $res->reserved_from = new Carbon($res->start);
      $res->reserved_to = new Carbon($res->end);
      
      BikeReservation::removeBufferTimes($res);
      
      $res->start = $res->reserved_from->format('Y-m-d H:i:s');
      $res->end = $res->reserved_to->format('Y-m-d H:i:s');
      
      foreach(['reserved_from', 'reserved_to', 'buffer_time_before', 'buffer_time_after'] as $field){
        unset($res->$field);
      }
    }
            
    return response()->json($reservations->toArray());
  }
  
  /**
   * Show list of users that have access to backend functionality of a bike.
   * 
   * @param Request $request
   * @param int $bike_id
   * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
   */
  public function editors(Request $request, int $bike_id)
  {
    $bike = Bike::with('editors')->findOrFail($bike_id);
    $this->authorize('modify',$bike);        
    
    $view_data = [
      'bike' => $bike,      
    ];
    
    return view('bike.editors',$view_data);
  }
  
  /**
   * Add editors to bike.
   * 
   * @param Request $request
   * @param int $bike_id
   * @return \Illuminate\Http\RedirectResponse|unknown
   */
  public function editorsAdd(Request $request, int $bike_id)
  {    
    $bike = Bike::with('owner')->findOrFail($bike_id);
    $this->authorize('modify',$bike);           
    $input = $request->except('_token');
    
    $validator = Validator::make($input, [
      'email' => [
        'required',
        'bail',
        'email',
        function($attribute, $value, $fail) use ($bike) {
          if ($value == $bike->owner->email) {
            $fail(__('bike.editors.cant_add_owner'));
          }
        },
        function($attribute, $value, $fail) use ($bike) {
          $query = DB::table('bike_editors')
          ->join('users','users.id','=','bike_editors.user_id')
          ->where([
            'bike_editors.bike_id' => $bike->id,
            'users.email' => $value           
          ]);
          if ($query->exists()) {
            $fail(__('bike.editors.cant_add_again'));
          }
        },
        'exists:users,email'
      ]
    ]);
        
    if ($validator->fails()) {
      return back()
      ->withInput($input)
      ->withErrors($validator);
    }
    
    // get the user
    $user_to_add = User::where(['email' => $input['email']])->first();
    $bike->editors()->attach($user_to_add->id);
    
    return redirect()->route('bike.editors',['bike_id' => $bike->id]);
  }
  
  /**
   * Confirm removal of bike editors.
   * 
   * @param Request $request
   * @param int $bike_id
   * @param int $user_id
   * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
   */
  public function editorsRemoveAsk(Request $request, int $bike_id, int $user_id)
  {
    $bike = Bike::findOrFail($bike_id);
    $this->authorize('modify',$bike);
    
    $user = $bike->editors()->findOrFail($user_id);
    
    return view('general.destroy_ask', [
      'header' => __('bike.editors.title_editors_remove', ['name' => $user->full_name]),
      'submit_text' => __('general.yes'), 
      'route' => route('bike.editors_remove', ['bike_id' => $bike, 'user_id' => $user]),
      'route_back' => route('bike.editors',['bike_id' => $bike])
    ]);
  }
  
  /**
   * Shows bike remove editors.
   * 
   * @param Request $request
   * @param int $bike_id
   * @param int $user_id
   * @return \Illuminate\Http\RedirectResponse
   */
  public function editorsRemove(Request $request, int $bike_id, int $user_id)
  {
    $bike = Bike::findOrFail($bike_id);
    $this->authorize('modify',$bike);
    
    $user = $bike->editors()->findOrFail($user_id);
    
    $bike->editors()->detach($user->id);
    
    return redirect()->route('bike.editors',['bike_id' => $bike->id]);
  }
  
  /**
   * Edit/display rental periods.
   *
   * @param Request $request
   * @param Bike $bike
   * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
   */
  public function rentalPeriodShow(Request $request, int $bike_id)
  {    
    $bike = Bike::withCount('rentalPlaces')
    ->withCount('rentalPeriods')
    ->findOrFail($bike_id);
    $this->authorize('modify',$bike);        
    
    $rental_periods = $bike->rentalPeriods()->with(['weekdays','rentalPlace','rentalMode', 'renteeLimitations'])->get();
    $rental_place_options = $bike->rentalPlaceSelectOptions();
    
    $rental_mode_options = RentalMode::getSelectOptions();
    $rental_duration_options = [
      ['value' => 0,'text' => __('bike.rental_duration_in_days.0')],
      ['value' => 1,'text' => __('bike.rental_duration_in_days.1')],
    ];
    $form_data = [];
    /*if ($rental_periods->isNotEmpty()) {
      // form data of first rental period?
      $rp = null;     
      if(!session('submitted_form')) {
        $rp = $rental_periods->first();      
        $form_data['rp_id'] = $rp->id;
        $form_data['date_from'] = $rp->date_from->format('Y-m-d');
        $form_data['date_to'] = $rp->date_to->format('Y-m-d');
        $form_data['time_from'] = $rp->time_from->format('H:i');
        $form_data['time_to'] = $rp->time_to->format('H:i');
        $form_data['rental_place_id'] = $rp->rental_place_id;
        $form_data['rental_mode_id'] = $rp->rental_mode_id;
        $form_data['weekday'] = $rp->weekdays->mapWithKeys(function($item) {
          return [$item->id => true];
        })->toArray();
        $form_data['rental_duration'] = ($rp->rental_duration_in_days ? ($rp->rental_duration / 24) : $rp->rental_duration);
        $form_data['rental_duration_in_days'] = (int)$rp->rental_duration_in_days;
        $form_data['no_interrupt'] = $rp->no_interrupt;
      } 
    } else*/ //{
      $form_data['date_from'] = now()->format('Y-m-d');
      $form_data['date_to'] = now()->addYears(10)->format('Y-m-d');
      $form_data['weekday'] = [];
      for ($i = 1; $i <=7; $i++) {
        $form_data['weekday'][$i] = true;
      }
    //}
    
    $view_data = [
      'bike' => $bike,
      'rental_periods' => $rental_periods,
      'rental_place_options' => $rental_place_options,
      'rental_mode_options' => $rental_mode_options,
      'rental_duration_options' => $rental_duration_options,
      'form_data' => $form_data
    ];
    return view('bike.rental_period.show',$view_data);
  }
  
  /**
   * Submit rental period.
   *
   * @param Request $request
   * @param Bike $bike
   */
  public function rentalPeriodSubmit(Request $request, int $bike_id)
  {
    $bike = Bike::findOrFail($bike_id);
    $this->authorize('modify',$bike);
    
    $input = $request->except('_token');

    $rentee_limitation_emails = ($input['rentee_limitation'] ? ['emails' => preg_split("/\r\n|\n|\r/", $input['rentee_limitation'])] : false);

    $rules = [
      'rp_id' => ['nullable','bail','integer','exists:rental_periods,id'],
      'rental_place_id' => ['required','integer','exists:rental_places,id'],
      'rental_mode_id' => ['required','integer','exists:rental_modes,id'],
      'time_from' => [new RequiredTime(),'date_format:"H:i"'],
      'time_to' => [new RequiredTime(),'date_format:"H:i"','after:time_from'],
      'date_from' =>  ['required','date_format:"Y-m-d"'],
      'date_to' =>  ['required','date_format:"Y-m-d"','after:date_from'],
      'rental_duration' => ['required','integer', 'min:1'],
      'rental_duration_in_days' => ['required','boolean'],
      //'no_interrupt' => ['required','boolean'],
      'weekday' => [
        'required',
        function ($attribute, $value, $fail) {
          // value is array of weekdays mapped to true/false - atleast one has to be true
          if(!collect($value)->contains(1)) {
            $fail(__('validation.custom.weekday_not_checked'));
          }
        },
      ],
      'rentee_limitation' => [
        'nullable',
        function ($attribute, $value, $fail) use ($rentee_limitation_emails) {
          if($rentee_limitation_emails){
            $email_validator = Validator::make($rentee_limitation_emails, ['emails.*' => ['required', 'string', 'email', 'max:255', 'distinct']]);
            if($email_validator->fails()){
              $fail(__('validation.custom.rentee_limitation_invalid_emails'));
            }
          }
        }
        ]
    ];
    
    $validator = Validator::make($input, $rules);    
    $request->session()->flash('submitted_form',true);
    $request->session()->flash('submitted_rp_id',$input['rp_id']);
    if ($validator->fails()) {      
      return back()
      ->withErrors($validator)
      ->withInput();
    }
    
    // check for conflicts with other rental periods
    $validator->after(function($validator) use($bike) {
      $data = $validator->getData();
      $rule = new RentalPeriodOverlap($data, $bike);
      if(!$rule->passes(null, null)) {
        $validator->errors()->add('rental_period_overlap',$rule->message());
      }
    });
      
    if ($validator->fails()) {
      return back()
      ->withErrors($validator)
      ->withInput();
    }
    $id = $input['rp_id'];
    // filter data      
    $input['rental_place_id'] = intval($input['rental_place_id']);
    $input['rental_mode_id'] = intval($input['rental_mode_id']);
    $input['rental_duration'] = (int)$input['rental_duration'];
    $input['rental_duration_in_days'] = (bool)$input['rental_duration_in_days'];
    if ($input['rental_duration_in_days']) {
      $input['rental_duration'] = $input['rental_duration'] * 24;
    }
    
    //$input['no_interrupt'] = (bool)$input['no_interrupt'];
    
    $rp = null;      
    $params = [
      'rental_mode_id' => $input['rental_mode_id'],
      'rental_place_id' => $input['rental_place_id'],
      'time_from' => $input['time_from'],
      'time_to' => $input['time_to'],
      'date_from' => $input['date_from'],
      'date_to' => $input['date_to'],
      'no_interrupt' => false, //$input['no_interrupt'],
      'rental_duration' => $input['rental_duration'],
      'rental_duration_in_days' => $input['rental_duration_in_days']
    ];
    
    if ($id === null) { // edit period
      $rp = RentalPeriod::create($params);
    } else { // new period
      $rp = RentalPeriod::find($id);
      $rp->update($params);
    }
    
    // handle weekdays
    $weekdays = [];
    for($i = 1; $i <= 7; $i++) {
      if (boolval($input['weekday'][$i] ?? false)) {
        $weekdays[] = $i;
      }
    }
    $rp->weekdays()->sync($weekdays);
    
    //Rentee Limitations
    $rp->renteeLimitations()->delete();
    if($rentee_limitation_emails){
      foreach($rentee_limitation_emails['emails'] as $email){ 
        RentalPeriodRenteeLimitation::create(['rental_period_id' => $rp->id, 'email' => $email]);
      }
    }
    
    return redirect()->route('bike.rental_period',['bike_id' => $bike->id]);
  }
  
  /**
   * Delete rental period.
   * 
   * @param Request $request
   * @param int $bike_id
   * @param int $rp_id
   * @return \Illuminate\Http\RedirectResponse
   */
  public function rentalPeriodDelete(Request $request, int $bike_id, int $rp_id)
  {
    $bike = Bike::findOrFail($bike_id);
    $this->authorize('modify',$bike);
    
    $rp = $bike->rentalPeriods()->findOrFail($rp_id);
    
    $rp->weekdays()->detach();
    $rp->renteeLimitations()->delete();
    $rp->delete();
    
    return redirect()->route('bike.rental_period',['bike_id' => $bike_id]);
  }
  
  /**
   * Delete all rental periods.
   * 
   * @param Request $request
   * @param int $bike_id
   * @return \Illuminate\Http\RedirectResponse
   */
  public function rentalPeriodDeleteAll(Request $request, int $bike_id)
  {
    $bike = Bike::with('rentalPeriods')->findOrFail($bike_id);
    $this->authorize('modify',$bike);    
    
    // delete the weekday mappings for all the bike's rental periods
    $rp_ids = $bike->rentalPeriods->pluck('id')->toArray();
    DB::table('rental_period_weekday')
    ->whereIn('rental_period_id',$rp_ids)
    ->delete();
    
    //delete limitations
    DB::table('rental_period_rentee_limitations')->whereIn('rental_period_id', $rp_ids)->delete(); 
    
    // delete the rental periods
    $bike->rentalPeriods()->delete();
    
    // delete the exceptions
    $bike->rentalPeriodExceptions()->delete();
    
    return redirect()->route('bike.rental_period',['bike_id' => $bike_id]);
  }
  
  /**
   * Review bike.
   * 
   * @param Request $request
   * @param int $bike_id
   * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
   */
  public function review(Request $request, int $bike_id)
  {
    $bike = Bike::has('rentalPeriods')
    ->withCount('rentalPlaces')
    ->withCount('rentalPeriods')
    ->findOrFail($bike_id);
    
    $this->authorize('modify',$bike);
    
    $date = null;
    if ($request->has('date')) {
      $date = date_create($request->query('date'));
    }
    
    $rental_modes = RentalMode::all();
    $rental_mode_colors = [
      RentalMode::getInstantReservationId() => 'cal-evt-instant',
      RentalMode::getInquiryId() => 'cal-evt-inquiry',
    ];
    
    $view_data = [
      'bike' => $bike,
      'date' => $date,
      'rental_modes' => $rental_modes,
      'rental_mode_colors' => $rental_mode_colors
    ];
    
    return view('bike.rental_period.review',$view_data);
  }
  
  /**
   * Rental periods.
   * 
   * @param Request $request
   * @param int $bike_id
   * @return \Illuminate\Http\JsonResponse
   */
  public function apiRentalPeriods(Request $request, int $bike_id)
  {
    $bike = Bike::find($bike_id);
    
    if ($bike === null) {
      return response()->json(['message' => 'Bike not found'],404);
    }
    
    $inputs = $request->query();
    
    $validator = Validator::make($inputs, [
      'start' =>  ['required','date'],
      'end' =>  ['required','date','after:start'],
    ]);
    
    if ($validator->fails()) {
      return response()->json(['message' => $validator->getMessageBag()->first()],400);
    }
    
    $start = new Carbon($inputs['start']);
    $end = new Carbon($inputs['end']);
    
    // get rental places
    $rental_places = $bike->rentalPlaces->mapWithKeys(function($item) {
      return [$item->id => $item->name];
    });
    $add_rental_place = $rental_places->count() > 1;
    
    $authed_user = (Auth::check() ? Auth::user() : false);
    if($authed_user !== false){
      $authed_user->is_bike_admin = $authed_user->can('modify', $bike) ;
    }
    
    // query all fitting rental periods
    $rental_periods = $bike->rentalPeriods()
    ->with(['weekdays', 'renteeLimitations'])
    // ignore all periods that end before the start or start after the end
    ->where(function($query) use ($start, $end) {
      $query->whereDate('date_to','<',$start)
      ->orWhereDate('date_from','>',$end);
    } ,null, null, 'and not')
    ->get();
    
    // get all exceptions within timeframe
    $exceptions = $bike->rentalPeriodExceptions()
    ->whereBetween('date_time_from',[$start, $end])
    ->with('renteeLimitations')
    ->get()
    ->mapToGroups(function($item, $key) {
      return [$item['date_time_from']->format('Y-m-d') => $item];
    });
        
    // organize rental periods
    $map = [];
    foreach($rental_periods as $rp) {
      foreach($rp->weekdays as $wd) {
        $day = $wd->id;
        if (!isset($map[$day])) {
          $map[$day] = [];
        }
        
        $map[$day][] = [
        //           'id' => $rp->id,
          'time_from' => $rp->time_from,
          'time_to' => $rp->time_to,
          'date_from' => $rp->date_from,
          'date_to' => $rp->date_to,
          'rental_place' => $rental_places[$rp->rental_place_id],
          'rental_place_id' => $rp->rental_place_id,
          'rental_mode_id' => $rp->rental_mode_id,
          'rental_duration' => $rp->rental_duration,
          'rental_duration_in_days' => $rp->rental_duration_in_days,
          'no_interrupt' => $rp->no_interrupt,
          'rentee_limitations' => $rp->renteeLimitations
        ];
      }
    }
    
    $curr = $start->clone();
    $events = [];
    $no_interrupts = [];
    
    $class_names = [
      RentalMode::getInstantReservationId() => ['border-success', 'bg-success'],
      RentalMode::getInquiryId() => ['border-info','bg-info'],
    ];
            
    while($curr <= $end) {
      $day = $curr->dayOfWeekIso;
      $date_string = $curr->format('Y-m-d');
      if (isset($exceptions[$date_string])) {
        foreach($exceptions[$date_string] as $e) {
          if (!$e->available) {
            continue;
          }
          
          //Rentee Limitations
          $restricted = false;
          if($e->renteeLimitations->isNotEmpty()){
            if(($authed_user === false) || 
               (!($authed_user->is_bike_admin) && (!in_array($authed_user->email, $e->renteeLimitations->pluck('email')->toArray())))){
             $restricted = true;
            }
          }
          
          $event = [
            'start' => $e->date_time_from->format('Y-m-d\TH:i:s'),
            'end' => $e->date_time_to->format('Y-m-d\TH:i:s'),
            //             'classNames' => $class_names[$e->rental_mode_id],
            'rental_mode' => $e->rental_mode_id,
            'rental_place_id' => $e->rental_place_id,
            'no_interrupt' => $e->no_interrupt,
            'restricted' => $restricted
          ];
          
          $event['duration_calc_data'] = [
            'rental_place' => ($add_rental_place ? $rental_places[$e->rental_place_id] : null),
            'rental_duration' => $e->rental_duration,
            'rental_duration_in_days' => $e->rental_duration_in_days
          ];
          
          if($event['no_interrupt']) {
            $no_interrupts[] = $event;
          }

          $events[] = $event;
        }
      } else if(isset($map[$day])) {
        $entries = $map[$day];
        foreach($entries as $entry) {
          if ($entry['date_from']->gt($curr) || $entry['date_to']->lt($curr)) {
            continue;
          }
          
          //Rentee Limitations
          $restricted = false;
          if($entry['rentee_limitations']->isNotEmpty()){
            if(($authed_user === false) ||
              (!($authed_user->is_bike_admin) && (!in_array($authed_user->email, $entry['rentee_limitations']->pluck('email')->toArray())))){
                $restricted = true;
            }
          }
          
          $event = [
            'start' => $date_string . 'T' . $entry['time_from']->format('H:i:s'),
            'end' => $date_string . 'T' . $entry['time_to']->format('H:i:s'),
            'rental_mode' => $entry['rental_mode_id'],
            'rental_place_id' => $entry['rental_place_id'],
            'no_interrupt' => $entry['no_interrupt'],
            'restricted' => $restricted
          ];
          
          $event['duration_calc_data'] = [
            'rental_place' => ($add_rental_place ? $entry['rental_place'] : null),
            'rental_duration' => $entry['rental_duration'],
            'rental_duration_in_days' => $entry['rental_duration_in_days']
          ];
          
          if($event['no_interrupt']) {
            $no_interrupts[] = $event;
          }
                    
          $events[] = $event;
        }
      }
      $curr->addDay(1);
    }
    
    foreach($events as &$ev){
      $ev_start = new Carbon($ev['start']);
      $ev_end = new Carbon($ev['end']);
      $ev_rental_duration = $ev['duration_calc_data']['rental_duration'] * 60;
      if($ev['no_interrupt']){
        $temp_duration = $ev_start->diffInMinutes($ev_end);
        if($temp_duration < $ev_rental_duration){
          $ev_rental_duration = $temp_duration;
        }
        $ev['title'] = $this->apiRentalPeriodsMakeTitle(
          $ev['duration_calc_data']['rental_place'],
          $ev_rental_duration / 60,
          $ev['no_interrupt'],
          $ev_end->format('H:i')
        );
        unset($ev['duration_calc_data']);
        continue;
      }else{
        foreach($no_interrupts as $ni){
          $ni_end = new Carbon($ni['end']);
          if($ni_end <= $ev_start){
            continue;
          }
          $temp_duration = $ev_start->diffInMinutes($ni_end);
          if ($temp_duration < $ev_rental_duration) {
            $ev_rental_duration = $temp_duration;
          }
        }
        $ev['title'] = $this->apiRentalPeriodsMakeTitle(
          $ev['duration_calc_data']['rental_place'],
          $ev_rental_duration / 60,
          $ev['no_interrupt'],
          $ev_end->format('H:i')
        );
        unset($ev['duration_calc_data']);
      }
    }
    
    return response()->json($events);
  }
  
  /**
   * Get rental periods titles.
   * 
   * @param unknown $rental_place
   * @param unknown $duration
   * @param unknown $no_interrupt
   * @param unknown $end_time
   * @return string
   */
  private function apiRentalPeriodsMakeTitle($rental_place, $duration, $no_interrupt, $end_time)
  {    
    $title = '';
   
    $days = 0;
    $hours = 0;
    while($duration > 0){
      if($duration < 24) {
        $hours = $duration;
        $duration = 0;
      }else{
        $days++;
        $duration -= 24;
      }
    }
    
    $duration_str = (' ' . $days > 0 ? ($days . ' ' . trans_choice('general.day', $days)) : '') . ($hours > 0 ? (' '. $hours . ' ' . trans_choice('general.hour', $hours)) : '');
    
    $title .=  __('bike.rental_duration_api') . ': ' . str_replace('.', ',', $duration_str);
    if ($no_interrupt) {
      $title .= "\n" . __('bike.no_interrupt_api', ['time' => $end_time]);
    }
    
    if ($rental_place) {
      $title = $rental_place . "\n" . $title;
    }
    
    return $title;
  }
  
  /**
   * Rental period exception.
   * 
   * @param Request $request
   * @param int $bike_id
   * @return \Illuminate\Http\RedirectResponse|unknown
   */
  public function exception(Request $request, int $bike_id)
  {
    $bike = Bike::has('rentalPeriods')
    ->withCount('rentalPlaces')
    ->withCount('rentalPeriods')
    ->findOrFail($bike_id);    
    $this->authorize('modify',$bike);        
    
    $input = $request->query();
    $validator = Validator::make($input, [
      'date' =>  ['required','date_format:"Y-m-d"'],
    ]);
    
    // just return if there are any errors
    if ($validator->fails()) {
      return back();
    }
    
    $date = new Carbon($input['date']);
    
    //get available rental mode
    $rental_modes = RentalMode::getSelectOptions();
    //get available places
    $rental_places = $bike->rentalPlaceSelectOptions();
    
    // check for existing rental periods
    $rpe = RentalPeriodException::byBikeAndDate($bike, $date)
    ->get();
    
    // create a list of form rows
    $rows = collect([]);
    $old = old();
    if (empty($old)) {
      // fill based on existing exceptions
      if($rpe->isNotEmpty()) {
        foreach($rpe as $e) {
          if (!$e->available) {
            continue;
          }
          
          $duration = $e->rental_duration;
          $duration_in_days = $e->rental_duration_in_days;
          if ($duration_in_days) {
            $duration = $duration / 24;
          }
          
          $rentee_limitation = $e->renteeLimitations->pluck('email')->toArray();
          if(empty($rentee_limitation)){
            $rentee_limitation = '';
          }else{
            $rentee_limitation = implode("\n", $rentee_limitation);
          }
          
          $rows->push([
            'time_from' => $e->date_time_from->format('H:i'),
            'time_to' => $e->date_time_to->format('H:i'),
            'rental_place_id' => $e->rental_place_id,
            'rental_mode_id' => $e->rental_mode_id,
            'rental_duration' => $duration,
            'rental_duration_in_days' => $duration_in_days,
            //'no_interrupt' => $e->no_interrupt,
            'rentee_limitation' => $rentee_limitation
          ]);
        }
        // fill base on rental periods with
      } else {
        $rows = collect($this->prepareTimeListForExceptions($bike, $date))->transform(function($item, $key) {
          $item['time_from'] = $item['time_from']->format('H:i');
          $item['time_to'] = $item['time_to']->format('H:i');
          if ($item['rental_duration_in_days']) {
            $item['rental_duration'] = $item['rental_duration'] / 24;
          }
          return $item;
        });
      }
      // fill based on submitted data
    } else {
      foreach($old['time_from'] as $index => $time_from) {
        $duration = $old['rental_duration'][$index];
        $duration_in_days = $old['rental_duration_in_days'][$index];
        
        $rows->push([
          'index' => $index,
          'time_from' => $time_from,
          'time_to' => $old['time_to'][$index],
          'rental_place_id' => $old['rental_place_id'][$index],
          'rental_mode_id' => $old['rental_mode_id'][$index],
          'rental_duration' => $duration,
          'rental_duration_in_days' => $duration_in_days,
          //'no_interrupt' => $old['no_interrupt'][$index],
          'rentee_limitation' => $old['rentee_limitation'][$index]
        ]);
      }
      
      // sort by time_from
      $rows = $rows->sortBy('time_from')->values();
    }
    
    $view_data = [
      'rows' => $rows,
      'date' => $date->format('Y-m-d'),
      'rental_modes' => $rental_modes,
      'rental_places' => $rental_places,
      'bike' => $bike
    ];
    return view('bike.rental_period.exception',$view_data);
  }
  
  /**
   * Submit rental period exception.
   * 
   * @param Request $request
   * @param int $bike_id
   * @return \Illuminate\Http\RedirectResponse|unknown
   */
  public function exceptionSubmit(Request $request, int $bike_id)
  {
    $bike = Bike::has('rentalPeriods')->findOrFail($bike_id);
    $this->authorize('modify',$bike);    
    
    $input = $request->except('_token');
    
    $rentee_limitation_emails = [];
    if(isset($input['rentee_limitation'])){
      foreach($input['rentee_limitation'] as $index => $rl_input){
        $rentee_limitation_emails[$index] = ($rl_input ? ['emails' => preg_split("/\r\n|\n|\r/", $rl_input)] : false);
      }
    }
    
    if($request->has('reset')) {
      // make sure valid date is given
      $validator = Validator::make($input, [
        'date' =>  ['required','date_format:"Y-m-d"'],
      ]);
      
      if ($validator->fails()) {
        return back();
      }
      
      // delete all exceptions on given date to effectively reset
      $date = Carbon::createFromFormat('Y-m-d', $input['date']);
      $bike->rentalPeriodExceptions()
      ->where(DB::raw('DATE(date_time_from)'),$date->format('Y-m-d'))
      ->delete();
      return redirect()->route('bike.rental_period_exception',['bike_id' => $bike_id, 'date' => $date->format('Y-m-d')]);
    }
    
    // prepare timespans for overlap validation
    $timespans = [];
    if(array_key_exists('time_from',$input) && array_key_exists('time_to',$input)) {
      foreach($input['time_from'] as $i => $time_from) {
        $timespans[] = [
          'time_from' => $time_from,
          'time_to' => $input['time_to'][$i],
          'index' => $i,
        ];
      }
    }
    $validator = Validator::make($input, [
      'date' =>  ['required','date_format:"Y-m-d"'],
      'time_from.*' => ['bail',new RequiredTime(),'date_format:"H:i"', new RentalPeriodExceptionTimeOverlap($timespans)],
      'time_to.*' => ['bail',new RequiredTime(),'date_format:"H:i"', new RentalPeriodExceptionTimeOverlap($timespans)],
      'rental_place_id.*' => ['required','integer','exists:rental_places,id'],
      'rental_mode_id.*' => ['required','integer','exists:rental_modes,id'],
      'rental_duration.*' => ['required','integer','min:1'],
      'rental_duration_in_days.*' => ['required','boolean'],
      //'no_interrupt.*' => ['required','boolean'],
      'rentee_limitation.*' => [
        'nullable',
        function ($attribute, $value, $fail) use ($rentee_limitation_emails) {
          if(empty($rentee_limitation_emails)){
            return;
          }
          $index = explode('.', $attribute)[1];
          $current_rentee_limitation_emails = $rentee_limitation_emails[$index];
          if($current_rentee_limitation_emails){
            $email_validator = Validator::make($current_rentee_limitation_emails, ['emails.*' => ['required', 'string', 'email', 'max:255', 'distinct']]);
            if($email_validator->fails()){
              $fail(__('validation.custom.rentee_limitation_invalid_emails'));
            }
          }
        }
        ]
    ]);
    
   
    // just return if there are any errors
    if ($validator->fails()) {
      return back()
      ->withInput($input)
      ->withErrors($validator);
    }
    // delete existing periods on given date
    $date = Carbon::createFromFormat('Y-m-d', $input['date']);
    
    $existing_rentee_limitations = $bike->rentalPeriodExceptions()
    ->where(DB::raw('DATE(date_time_from)'),$date->format('Y-m-d'))->get();
    foreach($existing_rentee_limitations as $item){
      $item->renteeLimitations()->delete();
    }
    
    $bike->rentalPeriodExceptions()
    ->where(DB::raw('DATE(date_time_from)'),$date->format('Y-m-d'))
    ->delete();
    
    // create list for new exceptions
    $list = [];
    if (array_key_exists('time_from',$input)) {
      foreach($input['time_from'] as $i => $time_from_str) {
        $time_from = Carbon::createFromFormat('H:i',$time_from_str);
        $time_to = Carbon::createFromFormat('H:i',$input['time_to'][$i]);
        $duration = (int)$input['rental_duration'][$i];
        $duration_in_days = (bool)$input['rental_duration_in_days'][$i];
        if ($duration_in_days) {
          $duration = $duration * 24;
        }
        $list[] = [
          'time_from' => $time_from,
          'time_from_str' => $time_from->format('H:i:s'),
          'time_to' => $time_to,
          'time_to_str' => $time_to->format('H:i:s'),
          'rental_mode_id' => $input['rental_mode_id'][$i],
          'rental_place_id' => $input['rental_place_id'][$i],
          'rental_duration' => $duration,
          'rental_duration_in_days' => $duration_in_days,
          'no_interrupt' => false, //$input['no_interrupt'][$i],
          'rentee_limitation' => $rentee_limitation_emails[$i]
        ];
      }
    }
    
    $this->createExceptionsFromTimeList($list, $date, $bike_id);
    
    return redirect()->route('bike.rental_period_exception',['bike_id' => $bike_id,'date' => $date->format('Y-m-d')]);
  }
  
  /**
   * Instant rental period exception.
   * 
   * @param Request $request
   * @param int $bike_id
   * @return \Illuminate\Http\RedirectResponse|unknown
   */
  public function exceptionInstant(Request $request, int $bike_id)
  {
    $bike = Bike::has('rentalPeriods')->findOrFail($bike_id);
    $this->authorize('modify',$bike);    
    
    $input = $request->except('_token');
    
    $validator = Validator::make($input, [
      'delete' => ['required','boolean'],
      'time_from' => ['required','date_format:"H:i"'],
      'time_to' => ['required','date_format:"H:i"','after:time_from'],
      'date' =>  ['required','date_format:"Y-m-d"'],
    ]);
    
    // just return if there are any errors
    if ($validator->fails()) {
      return back();
    }
    
    $start = new Carbon($input['date'] . ' ' . $input['time_from']);
    $end = new Carbon($input['date'] . ' ' . $input['time_to']);
    $delete = boolval($input['delete']);

    // assign default values in instant mode  
    list (
      $rental_place_id,
      $rental_mode_id,
      $rental_duration,
      $rental_duration_in_days,
      $no_interrupt,
      $rentee_limitation
    ) = $this->determineExceptionDefaults($bike, $start, $delete);
     
    // query existing exceptions for given date
    $rpe = RentalPeriodException::byBikeAndDate($bike, $start)->get();
    if ($rpe->isEmpty()) {
      $list = $this->prepareTimeListForExceptions($bike, $start);
      $rpe = $this->createExceptionsFromTimeList($list, $start, $bike_id);
    }        
    
    $this->adaptExceptions(
      $rpe,
      $start,
      $end,
      $rental_place_id,
      $rental_mode_id,
      $rental_duration,
      $rental_duration_in_days,
      $no_interrupt,
      $rentee_limitation,
      $delete
    );
    
    $route = ($request->has('redirect_to_form') ? 'bike.rental_period_exception' : 'bike.rental_period_review');
    
    return redirect()->route($route,['bike_id' => $bike->id, 'date' => $start->format('Y-m-d')]);
  }
  
  /**
   * Determine rental period exception default values.
   * 
   * @param Bike $bike
   * @param Carbon $date
   * @param bool $delete
   * @return NULL[]|boolean[]|array[]|NULL[]
   */
  private function determineExceptionDefaults(Bike $bike, Carbon $date, bool $delete = false) 
  {
    if ($delete) {
      return [
        null, // rental_place_id
        null, // rental_mode_id
        null, // rental_duration
        false, // rental_duration_in_days
        false,  // no_interrupt
        [] // rentee_limitation
      ];
    }
    
//     $rental_place_id = $bike->rentalPlaces->first()->id;
//     $rental_mode_id = RentalMode::getInstantReservationId();
    $rp = $bike->rentalPeriods()
    ->where([
      ['date_from', '<=',$date->format('Y-m-d')],
      ['date_to','>=',$date->format('Y-m-d')]
    ])
    ->first();
    
    if (!$rp) {
      $rp = $bike->rentalPeriods()->first();
    }
        
    return [
      $rp->rental_place_id,
      $rp->rental_mode_id,
      $rp->rental_duration,
      $rp->rental_duration_in_days,
      false, //$rp->no_interrupt,
      $rp->renteeLimitations->pluck('email')->toArray()
    ];    
  }
  
  /**
   * Prepare time list for rental period exception.
   * 
   * @param Bike $bike
   * @param Carbon $date
   * @return string[][]|NULL[][]
   */
  private function prepareTimeListForExceptions(Bike $bike, Carbon $date)
  {
    $weekday_id = $date->format('N');
    $rental_periods = $bike->rentalPeriods()
    ->join('rental_period_weekday','rental_period_weekday.rental_period_id','=','rental_periods.id')
    ->where([
      ['rental_period_weekday.weekday_id','=',$weekday_id],
    ])
    ->where(function($query) use ($date) {
      $query->whereDate('rental_periods.date_to','<',$date->format('Y-m-d'))
      ->orWhereDate('rental_periods.date_from','>',$date->format('Y-m-d'));
    } ,null, null, 'and not')
    ->select(['rental_periods.*'])
    ->orderBy('rental_periods.time_from','ASC')
    ->get();
    
    // create list of time slots
    $list = [];
    foreach($rental_periods as $rp) {
      
      $rentee_limitation = $rp->renteeLimitations->pluck('email')->toArray();
      if(empty($rentee_limitation)){
        $rentee_limitation = '';
      }else{
        $rentee_limitation = implode("\n", $rentee_limitation);
      }
      
      $list[] = [
        'time_from' => $rp->time_from,
        'time_from_str' => $rp->time_from->format('H:i:s'),
        'time_to' => $rp->time_to,
        'time_to_str' => $rp->time_to->format('H:i:s'),
        'rental_mode_id' => $rp->rental_mode_id,
        'rental_place_id' => $rp->rental_place_id,
        'rental_duration' => $rp->rental_duration,
        'rental_duration_in_days' => $rp->rental_duration_in_days,
        'no_interrupt' => $rp->no_interrupt,
        'rentee_limitation' => $rentee_limitation
      ];
    }
    
    return $list;
  }
  
  /**
   * Adapts rental period exception.
   * 
   * @param unknown $rpe
   * @param unknown $start
   * @param unknown $end
   * @param unknown $rental_place_id
   * @param unknown $rental_mode_id
   * @param unknown $rental_duration
   * @param unknown $rental_duration_in_days
   * @param unknown $no_interrupt
   * @param unknown $rentee_limitation
   * @param unknown $delete
   */
  private function adaptExceptions(
    $rpe, 
    $start, 
    $end, 
    $rental_place_id,
    $rental_mode_id,
    $rental_duration,
    $rental_duration_in_days,
    $no_interrupt,
    $rentee_limitation,
    $delete)
  {
    // exceptions exist. find fitting exception, flip flag and possibly merge
    $length = $rpe->count();
    $found_index = 0;
    $e = null;
    for($i = 0; $i < $length; $i++) {
      $e = $rpe[$i];
      if ($e->date_time_from == $start && $e->date_time_to == $end) {
        $found_index = $i;
        break;
      }
    }
    if ($e) {
      $e->available = !$delete;
      $e->rental_place_id = $rental_place_id;
      $e->rental_mode_id = $rental_mode_id;
      $e->rental_duration = $rental_duration;
      $e->rental_duration_in_days = $rental_duration_in_days;
      $e->no_interrupt = $no_interrupt;
      // merge with previou rpe
      if($found_index >  0 &&
        $rpe[$found_index - 1]->available === !$delete &&
        $rpe[$found_index - 1]->rental_mode_id === $rental_mode_id &&
        $rpe[$found_index - 1]->rental_place_id === $rental_place_id &&
        $rpe[$found_index - 1]->rental_duration === $rental_duration &&
        $rpe[$found_index - 1]->rental_duration_in_days === $rental_duration_in_days &&
        $rpe[$found_index - 1]->no_interrupt === $no_interrupt
        )
      {
        $prev = $rpe[$found_index - 1];
        $e->date_time_from = $prev->date_time_from;
        $e->rental_place_id = $prev->rental_place_id;
        $e->rental_mode_id = $prev->rental_mode_id;
        $e->rental_duration = $prev->rental_duration;
        $e->rental_duration_in_days = $prev->rental_duration_in_days;
        $e->no_interrupt = $prev->no_interrupt;
        $rentee_limitation = $prev->renteeLimitations->pluck('email')->toArray();
        $prev->renteeLimitations()->delete();
        $prev->delete();
      }
      //merge with next rpe
      if ($found_index < ($length - 1) &&
        $rpe[$found_index + 1]->available === !$delete &&
        $rpe[$found_index + 1]->rental_mode_id === $rental_mode_id &&
        $rpe[$found_index + 1]->rental_place_id === $rental_place_id &&
        $rpe[$found_index + 1]->rental_duration === $rental_duration &&
        $rpe[$found_index + 1]->rental_duration_in_days === $rental_duration_in_days &&
        $rpe[$found_index + 1]->no_interrupt === $no_interrupt
        )
      {
        $next = $rpe[$found_index+1];
        $e->date_time_to = $next->date_time_to;
        $e->rental_place_id = $next->rental_place_id;
        $e->rental_mode_id = $next->rental_mode_id;
        $e->rental_duration = $next->rental_duration;
        $e->rental_duration_in_days = $next->rental_duration_in_days;
        $e->no_interrupt = $next->no_interrupt;
        $rentee_limitation = $next->renteeLimitations->pluck('email')->toArray();
        $next->renteeLimitations()->delete();
        $next->delete();
      }
      $e->save();
      
      $e->renteeLimitations()->delete();
      if($rentee_limitation){
        foreach($rentee_limitation as  $email){
          RentalPeriodExceptionRenteeLimitation::create(['rental_period_exception_id' => $e->id, 'email' => $email]);
        }
      }
    }
  }
  
  /**
   * Creates exceptions from time list.
   * 
   * @param unknown $list
   * @param unknown $date
   * @param unknown $bike_id
   * @return \Illuminate\Support\Collection
   */
  private function createExceptionsFromTimeList($list, $date, $bike_id)
  {
    usort($list, function ($a,$b) {
      return ($a['time_from'] < $b['time_from']) ? -1 : 1;
    });
      
    $start = $date;
    $weekday = $date->format('N');
    $date_string = $date->format('Y-m-d');
    $time_count = count($list);
    $exceptions = collect([]);
    // empty list - create one non-available slot
    if ($time_count === 0) {
      $exceptions->push(RentalPeriodException::create([
        'bike_id' => $bike_id,
        'weekday_id' => $weekday,
        'date_time_from' => $date_string . ' 00:00:00',
        'date_time_to' => $date_string . ' 23:59:00',
      ]));
    } else {
      $last = $time_count - 1;
      if ($list[0]['time_from_str'] !== '00:00:00') {
        $exceptions->push(RentalPeriodException::create([
          'bike_id' => $bike_id,
          'weekday_id' => $weekday,
          'date_time_from' => $date_string . ' 00:00:00',
          'date_time_to' => $date_string . ' ' . $list[0]['time_from_str'],
        ]));
      }
      if ($list[$last]['time_to_str'] !== '23:59:00') {
        $exceptions->push(RentalPeriodException::create([
          'bike_id' => $bike_id,
          'weekday_id' => $weekday,
          'date_time_from' => $date_string . ' ' . $list[$last]['time_to_str'],
          'date_time_to' => $date_string . ' 23:59:00',
        ]));
      }
      
      for ($i = 0; $i < $time_count; $i++) {
        $exceptions->push($rpe = RentalPeriodException::create([
          'bike_id' => $bike_id,
          'available' => true,
          'rental_place_id' => $list[$i]['rental_place_id'],
          'rental_mode_id' => $list[$i]['rental_mode_id'],
          'rental_duration' => $list[$i]['rental_duration'],
          'rental_duration_in_days' => $list[$i]['rental_duration_in_days'],
          'no_interrupt' => false, //$list[$i]['no_interrupt'],
          'weekday_id' => $weekday,
          'date_time_from' => $date_string . ' ' . $list[$i]['time_from_str'],
          'date_time_to' => $date_string . ' ' . $list[$i]['time_to_str'],
        ]));
        
        if($list[$i]['rentee_limitation']){
          if(isset($list[$i]['rentee_limitation']['emails'])){
            foreach($list[$i]['rentee_limitation']['emails'] as $email){
              RentalPeriodExceptionRenteeLimitation::create(['rental_period_exception_id' => $rpe->id, 'email' => $email]);
            }
          }
        }
        
        if ($i != $last && $list[$i]['time_to_str'] != $list[$i+1]['time_from_str']) {
          $exceptions->push(RentalPeriodException::create([
            'bike_id' => $bike_id,
            'weekday_id' => $weekday,
            'date_time_from' => $date_string . ' ' . $list[$i]['time_to_str'],
            'date_time_to' => $date_string . ' ' . $list[$i + 1]['time_from_str'],
          ]));
        }
      }
    }
    
    $sorted = $exceptions->sortBy('date_time_from')->values();
    return $sorted;
  }
}
