<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PlaintextMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    
    protected $text;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $text)
    {
      $this->onQueue('emails');
      $this->subject = $subject;
      $this->text = $text;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
      return $this->text('emails.plaintext')
        ->with(['text' => $this->text])
      ;
    }
}
