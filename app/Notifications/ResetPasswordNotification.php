<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;
use Illuminate\Auth\Notifications\ResetPassword;


class ResetPasswordNotification extends ResetPassword implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token)
    {
      parent::__construct($token);
      
      $this->onQueue('password_resets');      
    }    
    
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
      if (static::$toMailCallback) {
        return call_user_func(static::$toMailCallback, $notifiable, $this->token);
      }
      
      return (new MailMessage)
      ->subject(Lang::get('email.password_reset.subject'))
      ->line(Lang::get('email.password_reset.line_1'))
      ->action(Lang::get('email.password_reset.button'), url(config('app.url').route('password.reset', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()], false)))
      ->line(Lang::get('email.password_reset.line_2', ['count' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire')]))
      ->line(Lang::get('email.password_reset.line_3'));
    }
    
}
