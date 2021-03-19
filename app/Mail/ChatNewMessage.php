<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ChatNewMessage extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    
    protected $sender_name;
    protected $chat_message;
    protected $chat_id;
    protected $chat_title;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($sender, $chat_message, $chat_title)
    {
      $this->onQueue('emails');
      $this->sender_name = $sender->getFullNameAttribute();
      $this->chat_message = $chat_message->message;
      $this->chat_id = $chat_message->chat_id;
      $this->chat_title = $chat_title;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
      $text = __('email.chat.text',[
        'name' => $this->sender_name,
        'message' => $this->chat_message
      ]);
      $subject =  __('email.chat.subject', ['chatTitle' => $this->chat_title]);
      
      return $this->markdown('emails.default')
      ->subject($subject)
      ->with([
        'text' => $text,
        'button' => __('email.chat.button'),
        'url' => route('chat.show',['chat_id' => $this->chat_id])
      ]);   
    }
}
