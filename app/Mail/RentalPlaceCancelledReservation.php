<?php

namespace App\Mail;

use App\BikeReservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RentalPlaceCancelledReservation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $suffix;
    protected $timeframe;
    protected $bike_name;
    protected $user_name;
    
    /**
     * Create a new message instance.
     *
     * @param BikeReservation $reservation
     * @param bool $confirmed
     * @return void
     */
    public function __construct($reservation, $confirmed)
    {
      $this->onQueue('emails');
      
      $this->suffix = $confirmed ? 'confirmed' : 'unconfirmed';
      
      $timeframe = $reservation->reserved_from->format('d.m.Y, H:i');
      if ($reservation->reserved_from->format('Ymd') == $reservation->reserved_to->format('Ymd')) {
        $timeframe .= ' - ' . $reservation->reserved_to->format('H:i');
      } else {
        $timeframe .= ' - ' . $reservation->reserved_to->format('d.m.Y, H:i');
      }
      
      $this->timeframe = $timeframe;     
      $this->bike_name = $reservation->bike->name;
      $this->user_name = $reservation->user->full_name;  
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {         
      $text = __('email.rental_place.cancelled_reservation.' . $this->suffix . '.text',[
        'timeframe' => $this->timeframe,
        'u_name' => $this->user_name,
        'b_name' => $this->bike_name
      ]);      
      $subject = __('email.rental_place.cancelled_reservation.' . $this->suffix .'.subject',['name' => $this->bike_name]);
      
      return $this->markdown('emails.default')        
      ->subject($subject)
      ->with(['text' => $text]);   
    }
}
