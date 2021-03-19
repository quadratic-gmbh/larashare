<?php

namespace App\Mail;

use App\BikeReservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserConfirmedReservation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $timeframe;
    protected $bike_name;
    protected $reservation_id;
    
    /**
     * Create a new message instance.
     *
     * @param BikeReservation $reservation
     * @return void
     */
    public function __construct($reservation)
    {
      $this->onQueue('emails');      
      
      $timeframe = $reservation->reserved_from->format('d.m.Y, H:i');
      if ($reservation->reserved_from->format('Ymd') == $reservation->reserved_to->format('Ymd')) {
        $timeframe .= ' - ' . $reservation->reserved_to->format('H:i');
      } else {
        $timeframe .= ' - ' . $reservation->reserved_to->format('d.m.Y, H:i');
      }
      
      $this->timeframe = $timeframe;     
      $this->bike_name = $reservation->bike->name; 
      $this->reservation_id = $reservation->id;      
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {         
      $text = __('email.user.confirmed_reservation.text',[
        'timeframe' => $this->timeframe,
        'b_name' => $this->bike_name,
        'url_chat' => route('chat.reservation',['id' => $this->reservation_id])
      ]);      
      $subject = __('email.user.confirmed_reservation.subject',['name' => $this->bike_name]);
      
      return $this->markdown('emails.default')
      ->subject($subject)
      ->with([
        'text' => $text,
        'button' => __('email.user.confirmed_reservation.button'),        
        'url' => route('user.reservation',['id' => $this->reservation_id])
      ]);   
    }
}
