<?php
/**
 * Notifier.php
 */
namespace App\Services;


use App\Bike;
use App\BikeReservation;
use App\Mail\ChatNewMessage;
use App\Mail\PlaintextMail;
use App\Mail\RentalPlaceNewReservation;
use App\Mail\RentalPlaceCancelledReservation;
use App\Mail\BikeCancelledReservation;
use App\Mail\UserNewsletterConfirmation;
use App\Mail\UserNewReservation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserCancelledReservation;
use App\Mail\UserConfirmedReservation;



/**
 * Notifier: handles sending of notifications for various purposes.
 *
 * @author vadler
 *
 */
class Notifier
{  
  
  /**
   * Notify rental places about a reservation.
   * 
   * @param Bike $bike
   * @param BikeReservation $reservation
   * @param array $rental_place_ids
   * @param boolean $is_inquiry
   * @param boolean $is_update
   */
  public function notifyRentalPlacesAboutReservation(Bike $bike, BikeReservation $reservation, $rental_place_ids, $is_inquiry, $is_update)
  {
    // get unique RentalPlaceEmail instances
    $emails = $this->getUniqueRentalPlaceEmails($rental_place_ids);
    
    foreach($emails as $e) {
      Mail::to($e->email)->send(new RentalPlaceNewReservation($bike, $reservation, $is_inquiry, $is_update));
    }
    
  }
  
  /**
   * Notify rental places about a cancellation.
   * 
   * @param BikeReservation $reservation
   * @param array $rental_place_ids
   */
  public function notifyRentalPlacesAboutCancellation(BikeReservation $reservation, $rental_place_ids)
  {
    $confirmed = ($reservation->confirmed_at != null);     
    $known_ids = Arr::where($rental_place_ids, function($value, $key) {
      return $value !== null;
    });        
    
    // get emails for rental places
    $emails = $this->getUniqueRentalPlaceEmails($known_ids);        
    foreach($emails as $e) {
      Mail::to($e->email)->send(new RentalPlaceCancelledReservation($reservation, $confirmed));
    }
    
    // check if any of the rental places couldnt be identified
    $unknown_start = $rental_place_ids[0] === null;
    $unknown_end = $rental_place_ids[1] === null;
    // send mail to owner
    if($unknown_start || $unknown_end) {
      $bike_email = $reservation->bike->owner->email;
      Mail::to($bike_email)->send(New BikeCancelledReservation($reservation, $confirmed));
    }
  }
  
  /**
   * Send user email about cancelled reservation.
   * 
   * @param BikeReservation $reservation
   */
  public function notifyUserAboutCancellation(BikeReservation $reservation)
  {
    $confirmed = ($reservation->confirmed_at != null);     
    $email = $reservation->user->email;
    
    Mail::to($email)->send(new UserCancelledReservation($reservation, $confirmed));
  }
  
  /**
   * Send user email about confirmed reservation.
   * 
   * @param BikeReservation $reservation
   */
  public function notifyUserAboutConfirmation(BikeReservation $reservation)
  {    
    $email = $reservation->user->email;
    
    Mail::to($email)->send(new UserConfirmedReservation($reservation));
  }
  
  /**
   * Send user email to user about the new reservation/inquiry they just created.
   * 
   * @param Bike $bike
   * @param BikeReservation $reservation
   * @param boolean $is_inquiry
   * @param boolean $is_update
   */
  public function notifyUserNewReservation(Bike $bike, BikeReservation $reservation, $is_inquiry, $is_update)
  {
    // only notify on instant reservation 
    if ($is_inquiry) {
      return;
    }
    
    $email = $reservation->user->email;
    
    Mail::to($email)->send(new UserNewReservation($bike, $reservation, $is_update));
  }
  
  /**
   * Sends user email about new chat message.
   * 
   * @param unknown $self
   * @param unknown $mail_to
   * @param unknown $chat_message
   * @param unknown $title
   */
  public function notifyUserChatNewMessage($self, $mail_to, $chat_message, $title)
  {
    foreach($mail_to as $m_t){
      $mail = new ChatNewMessage($self, $chat_message, $title);
      Mail::to($m_t)->send($mail);
    }
  }
  
  /**
   * Sends newsletter confirmation email.
   * 
   * @param unknown $newsletter_confirmation
   */
  public function notifyEmailNewsletterConfirmation($newsletter_confirmation)
  {
    Mail::to($newsletter_confirmation->email)->send(new UserNewsletterConfirmation($newsletter_confirmation->email, $newsletter_confirmation->token));
  }
  
  /**
   * Sends email when there is a problem with rental place geocoding.
   * 
   * @param unknown $rental_place
   */
  public function notifyGeocodeRentalPlaceProblem($rental_place)
  {
    Mail::to('martin@das-lastenrad.at')->send(new PlaintextMail(
     '[LR]Verleihstandort Koordinaten Problem',
      "Die Koordinaten für den Verleihstandort \"{$rental_place->name}\" (ID: {$rental_place->id}) konnten nicht gefunden werden. Bitte von Hand in der Datenbank eintragen."
    ));
  }
  
  /**
   * Gets the unique rental place emails.
   * 
   * @param unknown $rental_place_ids
   * @param boolean $notify_flag
   * @return \Illuminate\Support\Collection
   */
  private function getUniqueRentalPlaceEmails($rental_place_ids, $notify_flag = true)
  {    
    $query = DB::table('rental_place_emails')    
    ->whereIn('rental_place_id',$rental_place_ids)
    ->select([
      'email'
    ])
    ->groupBy('email');    
    
    if ($notify_flag) {
      $query->where('notify_on_reservation',1);
    }
    
    return $query->get();
  }
}