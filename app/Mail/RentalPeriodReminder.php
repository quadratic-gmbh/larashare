<?php

namespace App\Mail;

use App\Bike;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RentalPeriodReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $bike_id;
    protected $name;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Bike $bike)
    {
      $this->bike_id = $bike->id;
      $this->name = $bike->name;
      
      $this->onQueue('emails');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.default')
        ->with([
          'url' => route('bike.rental_period',['bike_id' => $this->bike_id]),
          'text' => __('email.rental_period_reminder.text',['name' => $this->name]),
          'button' => __('email.rental_period_reminder.button')
        ])->subject(__('email.rental_period_reminder.subject'));
    }
}
