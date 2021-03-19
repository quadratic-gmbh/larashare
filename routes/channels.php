<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{chat_id}', function ($user, $chat_id) {
  $self = $user;
  $owned_bikes = $self->bikes;
  $editable_bikes = $self->editableBikes;
    
  $bikes = $owned_bikes->merge($editable_bikes);
  $chats = new Collection();
  foreach($bikes as $b){
    $chats = $chats->merge($b->chats);
  }
    
  $chats = $chats->merge($self->chats);
  foreach($chats as $c){
    if((int) $c->id === (int) $chat_id){
      return true;
    }
  }
  return false;
});

