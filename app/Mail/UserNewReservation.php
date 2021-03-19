<?php

namespace App\Mail;

use App\Bike;
use App\BikeReservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserNewReservation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    
    protected $suffix_update;
    protected $timeframe;
    protected $bike_name;
    protected $reservation_id;

    /**
     * Create a new message instance.
     *
     * @param Bike $bike
     * @param BikeReservation $reservation    
     * @param boolean $is_update
     * @return void
     */
    public function __construct($bike, $reservation, $is_update)
    {
      $this->onQueue('emails');
      
      $this->suffix_update = $is_update ? 'update' : 'new';
                  
      $timeframe = $reservation->reserved_from->format('d.m.Y, H:i');
      if ($reservation->reserved_from->format('Ymd') == $reservation->reserved_to->format('Ymd')) {
        $timeframe .= ' - ' . $reservation->reserved_to->format('H:i');
      } else {
        $timeframe .= ' - ' . $reservation->reserved_to->format('d.m.Y, H:i');
      }
      
      $this->timeframe = $timeframe;     
      $this->bike_name = $bike->name;
      $this->reservation_id = $reservation->id;            
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {   
      $text =  __('email.user.new_reservation.text.' . $this->suffix_update,[
        'b_name' => $this->bike_name,
        'timeframe' => $this->timeframe,
        'url_chat' => route('chat.reservation',['id' => $this->reservation_id])
      ]);
      
      return $this->markdown('emails.default')        
      ->subject(__('email.user.new_reservation.subject.' . $this->suffix_update,['name' => $this->bike_name]))
      ->with([          
        'text' => $text,
        'button' => __('email.user.new_reservation.button'),
        'url' =>  route('user.reservation',['id' => $this->reservation_id])
      ]);   
    }
}
