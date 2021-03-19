<?php

namespace App\Http\Controllers;

use App\BikeReservation;
use App\Gender;
use App\NewsletterConfirmation;
use App\Services\InputFilter;
use App\Services\Notifier;
use App\Services\SearchEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
  /**
   * Show a reservation.
   * 
   * @param Request $request
   * @param SearchEngine $search_engine
   * @param int $id
   * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
   */
  public function reservation(Request $request, SearchEngine $search_engine, int $id)
  {
    // TODO: what if bike got soft-deleted?
    $user = $request->user();
    $reservation = $user->reservations()
    ->with('bike.rentalPlaces')
    ->findOrFail($id);
    
    BikeReservation::removeBufferTimes($reservation);
    
    $bike = $reservation->bike;
    // determine rental places for both start and end
    $rental_places = $bike->rentalPlaces;
    $rental_places_map = $rental_places->mapWithKeys(function($item) {
      return [$item->id => $item];
    });
    $rental_place_ids = $search_engine->findRentalPlacesForReservation($reservation);
    $rental_place_from = $rental_places_map[$rental_place_ids[0]] ?? null;
    $rental_place_to = $rental_places_map[$rental_place_ids[1]] ?? null;

    return view('user.reservation',[
      'user' => $user,
      'reservation' => $reservation,
      'old' => ($reservation->reserved_from < now()),
      'rental_place_from' => $rental_place_from,
      'rental_place_to' => $rental_place_to
    ]);
  }
  
  /**
   * Cancel a reservation.
   * 
   * @param Request $request
   * @param int $id
   * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
   */
  public function reservationCancel(Request $request, int $id)
  {
    $user = $request->user();
    $now = now();
    $reservation = $user->reservations()
    ->with('bike')
    ->where('reserved_from','>=',$now->format('Y-m-d H:i:s'))
    ->findOrFail($id);
    
    BikeReservation::removeBufferTimes($reservation);
    
    $ref = $request->query('ref');
    
    return view('user.reservation_cancel',[
      'reservation' => $reservation,
      'back_to_list' => $ref === 'list'      
    ]);
  }
  
  /**
   * Submit reservation cancellation.
   * 
   * @param Request $request
   * @param SearchEngine $search_engine
   * @param Notifier $notifier
   * @param int $id
   * @return \Illuminate\Http\RedirectResponse
   */
  public function reservationCancelSubmit(Request $request, SearchEngine $search_engine, Notifier $notifier, int $id)
  {
    $user = $request->user();    
    $now = now();
    $reservation = $user->reservations()
    ->with(['bike','user'])
    ->where('reserved_from','>=',$now->format('Y-m-d H:i:s'))
    ->findOrFail($id);
    
    BikeReservation::removeBufferTimes($reservation);
    
    $rental_place_ids = $search_engine->findRentalPlacesForReservation($reservation);
        
    // notify rental place(s)
    $notifier->notifyRentalPlacesAboutCancellation($reservation, $rental_place_ids);
    
    $reservation->delete();
    
    return redirect()->route('user.reservations');
  }
  
  /**
   * Show reservations.
   * 
   * @param Request $request
   * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
   */
  public function reservations(Request $request)
  {           
    $now = now();
    $user = $request->user();
    $reservations = DB::table('bike_reservations')
    ->join('bikes','bikes.id','=','bike_reservations.bike_id')
    //TODO only those were bike wasnt deleted?    
    ->where('bikes.deleted_at',null)
    ->where('bike_reservations.deleted_at',null)
    ->where('bike_reservations.user_id', $user->id)
    ->orderBy('bike_reservations.reserved_from','desc')
    ->select([
      'bike_reservations.id',
      'bike_reservations.reserved_from',
      'bike_reservations.reserved_to',
      'bike_reservations.buffer_time_before',
      'bike_reservations.buffer_time_after',
      'bike_reservations.purpose',
      'bike_reservations.confirmed_on',
      'bikes.id as bike_id',
      'bikes.name as bike_name'
    ])
    ->get()
    ->mapToGroups(function($item, $key) use($now){
      $item->reserved_to = new Carbon($item->reserved_to);
      $item->reserved_from = new Carbon($item->reserved_from);
      
      BikeReservation::removeBufferTimes($item);
      
      if($item->reserved_from < $now) {
        return ['old' => $item];
      } else {
        return ['new' => $item];
      }
    });
    
    return view('user.reservations',[
      'reservations' => $reservations,
      'user' => $user
    ]);
  }
  
  /**
   * Edit user profile view.
   * 
   * @param Request $request
   * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
   */
  public function edit(Request $request)
  {
    $user = $request->user();        
    $gender_options = Gender::getSelectOptions();
    
    $form_data = [
      'firstname' => $user->firstname,
      'lastname' => $user->lastname,
      'email_old' => $user->email,
      'telephone' => $user->telephone,
      'gender_id' => $user->gender_id,
      'date_of_birth' => $user->date_of_birth,
      'street_name' => $user->street_name,
      'house_number' => $user->house_number,
      'postal_code' => $user->postal_code,
      'city' => $user->city,
      'newsletter' => $user->newsletter !== null
    ];
    
    $view_data = [
      'gender_options' => $gender_options,
      'form_data' => $form_data    
    ];
        
    return view('user.edit',$view_data);
  }
  
  /**
   * Update user profile.
   * 
   * @param Request $request
   * @param InputFilter $input_filter
   * @return \Illuminate\Http\RedirectResponse|unknown
   */
  public function update(Request $request, InputFilter $input_filter)
  {
    $user = $request->user();
    
    $input = $request->except('_token');
    $rules = [
      'firstname' => ['required', 'string', 'max:255'],
      'lastname' => ['required', 'string', 'max:255'],
      'date_of_birth' => ['required', 'date_format:"Y-m-d"', 'before:today'],
      'gender_id' => ['nullable','integer','exists:genders,id'],
      'street_name' => ['required', 'string', 'max:255'],
      'city' => ['required', 'string', 'max:255'],
      'house_number' => ['required', 'string', 'max:10'],
      'postal_code' => ['required', 'string', 'max:10'],
      'telephone' => ['required', 'phone:AUTO,AT', 'max:100'],
      'password' => ['required', function($attribute, $value, $fail) use ($user) {
        if (!Hash::check($value,$user->password)) {
          $fail(__('passwords.invalid'));
        }
      }],
      'newsletter' => ['required', 'bool'], 
      'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email', 'confirmed'],
      'password_new' => ['nullable', 'string', 'min:8', 'confirmed'],      
    ];
    
    $validator = Validator::make($input, $rules);
    
    if($validator->fails()) {
      return back()
      ->withErrors($validator)
      ->withInput($request->except(['password','password_new','password_new_confirmation']));
    }
    
    //TODO filtering
    $filter_rules = [
      'required' => [
        'firstname' => InputFilter::TYPE_STR,
        'lastname' => InputFilter::TYPE_STR,
        'street_name' => InputFilter::TYPE_STR,
        'city' => InputFilter::TYPE_STR,
        'house_number' => InputFilter::TYPE_STR,
        'postal_code' => InputFilter::TYPE_STR,
        'telephone' => InputFilter::TYPE_STR,
        'date_of_birth' => InputFilter::TYPE_DATE,
        'newsletter' => InputFilter::TYPE_BOOL
      ],
      //'nullable' => [
      //  'gender_id' => InputFilter::TYPE_INT
      //]
    ];
    $input_filter->filter($filter_rules, $input);
    
    $fields = [
      'firstname',
      'lastname',
      'street_name',
      'city',
      'house_number',
      'postal_code',
      'telephone',
      'date_of_birth',
      'gender_id'
    ];
    foreach($fields as $field) {
      $user->$field = $input[$field];
    }
    
    if(isset($input['email'])) {
      $user->email = $input['email'];
    }
    
    if(isset($input['password_new'])) {
      $user->password = Hash::make($input['password_new']);
    }
    
    if($input['newsletter'] && ($user->newsletter === null)){
      $newsletter_confirmation = NewsletterConfirmation::create(['email' => $user->email, 'token' => Str::random(100)]);
      $notifier = new Notifier();
      $notifier->notifyEmailNewsletterConfirmation($newsletter_confirmation);
    }elseif(!$input['newsletter'] && ($user->newsletter !== null)){
      $user->newsletter = null;
    }
    
    $user->save();
    
    $request->session()->flash('update_success',true);
    return redirect()->route('user.edit');
  }
  
  
  /**
   * Confirmation for removing the specified resource from storage.
   *
   * @param Request $request
   * @param int $bike_id
   * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
   */
  public function delete(Request $request)
  {
    $user = $request->user();       
    
    // check for owned bikes
    $has_bikes = $user->bikes()->public()->exists();
    
    // check for upcoming reservations
    $has_reservations = $user->reservations()
    ->where('reserved_to','>=',now())
    ->exists();    
    
    return view('user.delete', [
      'user' => $user,
      'has_reservations' => $has_reservations,
      'has_bikes' => $has_bikes
    ]);
  }
  
  /**
   * Remove the specified resource from storage.
   *
   * @param Request $request
   * @param int $bike_id
   * @return \Illuminate\Http\Response
   */
  public function deleteSubmit(Request $request)
  {
    $user = $request->user();        
    $now = now();
    // check for owned bikes
    if ($user->bikes()->public()->exists()) {
      abort(500);
    }
    
    // check for upcoming reservations
    if ($user->reservations()->where('reserved_to','>=',$now)->exists()) {
      abort(500);
    }
    
    $input = $request->input();
    
    $rules = [
      'password' => [
        'required',
        function($attribute, $value, $fail) use ($user) {
          if (!Hash::check($value,$user->password)) {
            $fail(__('passwords.invalid'));
          }
        }
      ],      
    ];
    
    $validator = Validator::make($request->input(), $rules);
    
    if($validator->fails()) {
      return back()
      ->withErrors($validator);
    }    
            
    // logout
    Auth::logout();
    
    //set email field to something else so its available again
    $str = "deleted {$user->email} {$now}";
    $user->email = $str;
    $user->save();
    
    // perform soft-delete
    $user->delete();
    
    return redirect()->route('home');
  }
  
  /**
   * Confirms Newsletter signup.
   * 
   * @param Request $request
   * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
   */
  public function confirmNewsletter(Request $request)
  {
    $inputs = $request->only(['email', 'token']);
    $validator = Validator::make($inputs, ['email' => ['required','email'], 'token' => ['required','string','size:100']]);
    if($validator->fails()){
      abort(500);
    }
    
    $newletter_confirmation = NewsletterConfirmation::where([
      'email' => $inputs['email'],
      'token' => $inputs['token']
    ])->first();
    if(!$newletter_confirmation){
      abort(500);
    }
    
    $user = $request->user();
    if($user->email !== $newletter_confirmation->email){
      abort(500);
    }
    
    $user->newsletter = $user->freshTimestamp();
    $user->save();
    $newletter_confirmation->delete();
    
    return view('user.newsletter_confirmation');
  }
  
}
