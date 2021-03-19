<?php

namespace App\Mail;

use App\Bike;
use App\BikeReservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RentalPlaceNewReservation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $suffix;
    protected $suffix_update;
    protected $timeframe;
    protected $bike_name;
    protected $bike_id;
    protected $reservation_id;
    protected $user_name = '';
    protected $user_email = '';
    protected $user_phone = '';

    /**
     * Create a new message instance.
     *
     * @param Bike $bike
     * @param BikeReservation $reservation
     * @param bool $is_inquiry
     * @param boolean $is_update
     * @return void
     */
    public function __construct($bike, $reservation, $is_inquiry, $is_update)
    {
      $this->onQueue('emails');
      
      $this->suffix = $is_inquiry ? 'inquiry' : 'instant';
      $this->suffix_update = $is_update ? 'update' : 'new';
      
      $timeframe = $reservation->reserved_from->format('d.m.Y, H:i');
      if ($reservation->reserved_from->format('Ymd') == $reservation->reserved_to->format('Ymd')) {
        $timeframe .= ' - ' . $reservation->reserved_to->format('H:i');
      } else {
        $timeframe .= ' - ' . $reservation->reserved_to->format('d.m.Y, H:i');
      }
      
      $this->timeframe = $timeframe;     
      $this->bike_id = $bike->id;
      $this->bike_name = $bike->name;
      $this->reservation_id = $reservation->id;
      
      $this->user_name = $reservation->user->full_name;
      $this->user_email = $reservation->user->email;
      $this->user_phone = $reservation->user->telephone ? "\n\n{$reservation->user->telephone}" : null;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {   
      $text =  __('email.rental_place.new_reservation.' . $this->suffix . '.text.' . $this->suffix_update,[
        'b_name' => $this->bike_name,
        'timeframe' => $this->timeframe,
        'u_name' => $this->user_name,
        'u_phone' => $this->user_phone,
        'u_email' => $this->user_email
      ]);
      
      return $this->markdown('emails.default')        
      ->subject(__('email.rental_place.new_reservation.' . $this->suffix .'.subject.' . $this->suffix_update,['name' => $this->bike_name]))
      ->with([          
        'text' => $text,
        'button' => __('email.rental_place.new_reservation.' . $this->suffix . '.button'),
        'url' =>  route('bike.reservation',['bike_id' => $this->bike_id, 'reservation_id' => $this->reservation_id])
      ]);   
    }
}
