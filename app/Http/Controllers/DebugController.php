<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\BikeReservation;
use App\Bike;
use App\User;
use App\ChatMessage;
use App\Mail;

class DebugController extends Controller
{
    /**
     * Show cancelled reservation by bike mail.
     * 
     * @param Request $request
     * @return \App\Mail\BikeCancelledReservation
     */
    public function mailBikeCancelledReservation(Request $request)
    {
      $now = now();
      $confirmed = $request->query('confirmed') == 1;
      $reservation = new BikeReservation([
        'reserved_from' => $now,
        'reserved_to' => $now->addDay(),
      ]);
      
      $reservation->bike = new Bike(['name' => 'Sample Bike']);
      $reservation->user = new User(['firstname' => 'Max', 'lastname' => 'Mustermann']);
      $mail = new Mail\BikeCancelledReservation($reservation, $confirmed);
      
      return $mail;
    }
    
    /**
     * Show cancelled reservation by rental place mail.
     * 
     * @param Request $request
     * @return \App\Mail\RentalPlaceCancelledReservation
     */
    public function mailRentalPlaceCancelledReservation(Request $request)
    {
      $now = now();
      $confirmed = $request->query('confirmed') == 1;
      $reservation = new BikeReservation([
        'reserved_from' => $now,
        'reserved_to' => $now->addDay(),
      ]);
      
      $reservation->bike = new Bike(['name' => 'Sample Bike']);
      $reservation->user = new User(['firstname' => 'Max', 'lastname' => 'Mustermann']);
      $mail = new Mail\RentalPlaceCancelledReservation($reservation, $confirmed);
      
      return $mail;
    }
    
    /**
     * Show cancelled reservation by user mail.
     * 
     * @param Request $request
     * @return \App\Mail\UserCancelledReservation
     */
    public function mailUserCancelledReservation(Request $request)
    {
      $now = now();
      $confirmed = $request->query('confirmed') == 1;
      $reservation = new BikeReservation([
        'reserved_from' => $now,
        'reserved_to' => $now->addDay(),
      ]);
      
      $reservation->bike = new Bike(['name' => 'Sample Bike']);      
      $mail = new Mail\UserCancelledReservation($reservation, $confirmed);
      
      return $mail;
    }
    
    /**
     * Show rental period reminder mail.
     * 
     * @param Request $request
     * @return \App\Mail\RentalPeriodReminder
     */
    public function mailRentalPeriodReminder(Request $request)
    {
      $bike = new Bike(['name' => 'Sample Bike']);
      $bike->id = 1;
      $mail = new Mail\RentalPeriodReminder($bike);
      
      return $mail;
    }
    
    /**
     * Show rental place new reservation mail.
     * 
     * @param Request $request
     * @return \App\Mail\RentalPlaceNewReservation
     */
    public function mailRentalPlaceNewReservation(Request $request)
    {
      $now = now();
      $inquiry = $request->query('inquiry') == 1;
      $reservation = new BikeReservation([     
        'reserved_from' => $now,
        'reserved_to' => $now->addDay(),
      ]);      
      $reservation->id = 1;
      
      $reservation->user = new User([
        'firstname' => "Firstname",
        'lastname' => "Lastname",
        'email' => "valentin.adler@gmx.net",
        'telephone' => '+43234423'
      ]);
      
      $bike = new Bike([
        'name' => 'Sample Bike',        
      ]); 
      
      $bike->id = 1;
      $mail = new Mail\RentalPlaceNewReservation($bike, $reservation, $inquiry);
      
      return $mail;
    }
    
    /**
     * Show survey mail.
     * 
     * @param Request $request
     * @return \App\Mail\Survey
     */
    public function mailSurvey(Request $request)
    {
      $mail = new Mail\Survey();
      
      return $mail;
    }
    
    /**
     * Show user confirmed reservation mail.
     * 
     * @param Request $request
     * @return \App\Mail\UserConfirmedReservation
     */
    public function mailUserConfirmedReservation(Request $request)
    {
      $now = now();      
      $reservation = new BikeReservation([
        'reserved_from' => $now,
        'reserved_to' => $now->addDay(),
      ]);
      $reservation->id = 1;
      
      $reservation->bike = new Bike([
        'name' => 'Sample Bike'
      ]); 
      $mail = new Mail\UserConfirmedReservation($reservation);
      return $mail;
    }
    
    /**
     * Show user new reservation mail.
     * 
     * @param Request $request
     * @return \App\Mail\UserNewReservation
     */
    public function mailUserNewReservation(Request $request)
    {
      $now = now();      
      $reservation = new BikeReservation([
        'reserved_from' => $now,
        'reserved_to' => $now->addDay(),
      ]);
      $reservation->id = 2;            
      
      $bike = new Bike([
        'name' => 'Sample Bike',
      ]);
      
      $bike->id = 1;
      $mail = new Mail\UserNewReservation($bike, $reservation);
      
      return $mail;
    }
    
    /**
     * Show new chat message mail.
     * 
     * @param Request $request
     * @return \App\Mail\ChatNewMessage
     */
    public function mailChatNewMessage(Request $request)
    {
      $user = new User(['firstname' => 'Max', 'lastname' => 'Mustermann']);
      $chat_message = new ChatMessage(['chat_id' => 1, 'message' => 'MESSAGE']);
      
      $mail = new Mail\ChatNewMessage($user, $chat_message, "TITLE");
      
      return $mail;
    }
    
    /**
     * Show newsletter confirmation mail.
     * 
     * @param Request $request
     * @return \App\Mail\UserNewsletterConfirmation
     */
    public function mailNewsletterConfirmation(Request $request)
    {
      $mail = new Mail\UserNewsletterConfirmation('email@testmail.de', '1234abcd1234abcd1234abcd1234abcd1234abcd1234abcd1234abcd1234abcd1234abcd1234abcd1234abcd1234abcd1234');
      
      return $mail;
    }
}
