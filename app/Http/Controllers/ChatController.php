<?php

namespace App\Http\Controllers;

use App\Bike;
use App\BikeReservation;
use App\Chat;
use App\ChatLastRead;
use App\ChatMessage;
use App\ChatType;
use App\User;
use App\Events\ChatNewMessageEvent;
use App\Services\Notifier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
  /**
   * Display a listing of the resource.
   * 
   * @param Request $request
   * @return \Illuminate\View\View|\Illuminate\Contracts\View\Factory
   */
  public function index(Request $request)
  {
    $self = $request->user();
    $owned_bikes = $self->bikes;
    $editable_bikes = $self->editableBikes;
    
    $bikes = $owned_bikes->merge($editable_bikes);
    $chats = new Collection();
    foreach($bikes as $b){
      $chats = $chats->merge($b->chats);
    }
    
    $chats = $chats->merge($self->chats);
    
    //filter out chats with deleted bikes
    list($chats_bike_deleted, $chats_bike_not_deleted) = $chats->partition(function ($i){
      return (!isset($i->bikes[0]));
    });
    $chats = $chats_bike_not_deleted;
    
    $chats->load(['lastReads' => function ($query) use ($self) {
      $query->where('user_id', '=', $self->id);
    }, 'users', 'bikes']);
    
    foreach($chats as $c){
      $unread_query = $c->messages()->where('user_id', '<>', $self->id);
      if($c->lastReads->count()){
        $unread_query->where('created_at', '>=', $c->lastReads[0]->updated_at);
      }
      $c->unread_count = $unread_query->count();
      $c->load(['messages' => function ($query) {
        $query->latest()->first();
      }]);
      $c->messages->load('sender');
    }
  
    $chats = $chats->sort(function($a, $b){
      $a_cm = ($a->messages->count() ? $a->messages[0] : false);
      $b_cm = ($b->messages->count() ? $b->messages[0] : false);
      if(!$a_cm && !$b_cm){
        return 0;
      }elseif($a_cm != false && !$b_cm){
        return -1;
      }elseif($b_cm != false && !$a_cm){
        return 1;
      }else{
        if($a_cm->created_at > $b_cm->created_at){
          return -1;
        }else{
          return 1;
        }
      }
    });
    
    return view('chat.index',[
      'self' => $self,
      'chats' => $chats
    ]);
  }
  
  /**
   * Creates a new chat between bike and user based on reservation ID and redirects to it.
   * 
   * @param Request $request
   * @param Reservation ID $id
   * @return \Illuminate\Http\RedirectResponse
   */
  public function reservation(Request $request, $id)
  {
    $reservation = BikeReservation::with([
      'user',
      'bike'
    ])->findOrFail($id);

    $bike = $reservation->bike;
    $user = $reservation->user;
   
   $chat = DB::table('chats')
     ->join('bike_chat', 'chats.id', '=', 'bike_chat.chat_id')
     ->join('chat_user', 'chats.id', '=', 'chat_user.chat_id')
     ->where([
       ['bike_chat.bike_id', '=', $bike->id],
       ['chat_user.user_id', '=', $user->id],
     ])->get();
      
   if(count($chat)){
     return redirect()->route('chat.show', ['chat_id' => $chat[0]->id]);
   }
   
   $chat = Chat::create(['chat_type_id' => ChatType::getBikeId()]);
   $bike->chats()->attach($chat->id);
   $user->chats()->attach($chat->id);
   
   return redirect()->route('chat.show', ['chat_id' => $chat->id]);
  }
  
  /**
   * 
   * Creates a new chat between bike and user based on their IDs and redirects to it.
   * 
   * @param Request $request
   * @param integer $bike_id
   * @param integer $user_id
   * @return \Illuminate\Http\RedirectResponse
   */
  public function bikeuser(Request $request, $bike_id, $user_id)
  {
    $bike = Bike::findOrFail($bike_id);
    $user = User::findOrFail($user_id);
    
    $chat = DB::table('chats')
    ->join('bike_chat', 'chats.id', '=', 'bike_chat.chat_id')
    ->join('chat_user', 'chats.id', '=', 'chat_user.chat_id')
    ->where([
      ['bike_chat.bike_id', '=', $bike->id],
      ['chat_user.user_id', '=', $user->id],
    ])->get();
    
    if(count($chat)){
      return redirect()->route('chat.show', ['chat_id' => $chat[0]->id]);
    }
    
    $chat = Chat::create(['chat_type_id' => ChatType::getBikeId()]);
    $bike->chats()->attach($chat->id);
    $user->chats()->attach($chat->id);
    
    return redirect()->route('chat.show', ['chat_id' => $chat->id]);
  }
  
  /**
   * Show Chat and submit messages to it.
   * 
   * @param Request $request
   * @param Notifier $notifier
   * @param Chat ID $chat_id
   * @return \Illuminate\Http\RedirectResponse|unknown
   */
  public function show(Request $request, Notifier $notifier, $chat_id){
    $chat = Chat::findOrFail($chat_id);
    $user = $chat->users[0];
    $self = $request->user();
    
    if(!isset($chat->bikes[0])){
      abort(404);
    }
    
    $bike = $chat->bikes[0];
    
    (($self->is($user)) || $this->authorize('modify',$bike));
    
    $title = __('chat.title.bike', ['username' => $user->getFullNameAttribute(), 'bikename' => $bike->name]);
    
    if($request->isMethod('post')){
      $inputs = $request->except('_token');
      $inputs['chat_message'] = strip_tags($inputs['chat_message']);
      
      $validator = Validator::make($inputs, ['chat_message' => ['required','string','max:500']]);
      
      if($validator->fails()){
        return back()
        ->withErrors($validator)
        ->withInput();
      }
      
      $chat_message_data = [
        'chat_id' => $chat->id,
        'user_id' => $self->id,
        'message' => $inputs['chat_message']
      ];
      $chat_message = ChatMessage::create($chat_message_data);
      
      $bike->load(['owner', 'editors']);
      $mail_to = [];
      if(!$self->is($bike->owner)){
        $mail_to[] = $bike->owner->email;
      }
      if(!$self->is($user)){
        $mail_to[] = $user->email;
      }
      foreach($bike->editors as $editor){
        if(!$self->is($editor)){
          $mail_to[] = $editor->email;
        }
      }
      
      $notifier->notifyUserChatNewMessage($self, $mail_to, $chat_message, $title);
      broadcast(new ChatNewMessageEvent($chat->id));
    }
    
    $chat_last_read = $chat->lastReads()->where('user_id', '=', $self->id)->first();
    
    $last_read = false;
    if(!$chat_last_read){
      $chat_last_read_data = [
        'chat_id' => $chat->id,
        'user_id' => $self->id
      ];
      ChatLastRead::create($chat_last_read_data);
    }else{
      $last_read = $chat_last_read->updated_at;
      $chat_last_read->touch();
    }
    
    $chat->load('messages.sender');
    
    return view('chat.show',[
      'title' => $title,
      'chat' => $chat,
      'user' => $user,
      'self' => $self,
      'last_read' => $last_read
    ]);
  }
}
