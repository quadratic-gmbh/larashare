<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserNewsletterConfirmation extends Mailable implements ShouldQueue
{
  use Queueable, SerializesModels;
  
  protected $email;
  protected $token;
  
  /**
   * Create a new message instance.
   *
   * @return void
   */
  public function __construct($email, $token)
  {
    $this->onQueue('emails');
    $this->email = $email;
    $this->token = $token;
  }
  
  /**
   * Build the message.
   *
   * @return $this
   */
  public function build()
  {
    return $this->markdown('emails.default')
    ->subject(__('email.newsletter_confirmation.subject'))
    ->with([
      'text' => __('email.newsletter_confirmation.text'),
      'button' => __('email.newsletter_confirmation.button'),
      'url' => route('user.confirm_newsletter', ['email' => $this->email, 'token' => $this->token])
    ]);
  }
}
