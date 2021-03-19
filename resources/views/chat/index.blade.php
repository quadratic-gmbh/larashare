@extends('layouts.app')
@section('content')
	<h1 class="text-primary">
    {{__('chat.index.title')}}
  </h1>
  
  @if(!$chats->count())
  <p>{{__('chat.index.empty_text')}}</p>
  @else
  @foreach($chats as $c)
  <div class="card border-primary mb-3">
  <div class="card-header bg-light">
    <h4>
    <a href="{{route('chat.show',['chat_id' => $c->id])}}">{{__('chat.title.bike', ['username' => (isset($c->users[0]) ? $c->users[0]->getFullNameAttribute() : __('user.deleted')), 'bikename' => (isset($c->bikes[0]) ? $c->bikes[0]->name : __('bike.deleted'))])}}</a>
     @if($c->unread_count > 0)
     <span class="text-danger" title="{{__('chat.messages.unread', ['count' => $c->unread_count])}}"><i class="fas fa-envelope-open-text fa-1x"></i> {{$c->unread_count}}</span>
     @else
     <span class="text-success" title="{{__('chat.messages.read')}}"><i class="fas fa-envelope fa-1x"></i> 0</span>
     @endif
    </h4>
  </div>
  <div class="card-body">
   @if(!$c->messages->count())
    <p>{{__('chat.index.empty_chat_text')}}</p>
   @else
   <div>
   @php
   $cm = $c->messages[0];
   $current_sender = $cm->sender;
   $user = $c->users[0];
   $last_read = ($c->lastReads->count() ? $c->lastReads[0] : false);
   @endphp
   
    {{$current_sender->getFullNameAttribute()}}
  	 @if($cm->user_id == $self->id)
  	 <em>({{__('chat.show.me')}})</em>
  	 @endif
  	 - {{$cm->date_time->format('d.m.Y, H:i:s')}}
  	 @if($cm->user_id == $user->id)
  	 <em>{{__('chat.show.rentee')}}</em>
  	 @else
  	 <em>{{__('chat.show.renter')}}</em>
  	 @endif
  	 @if(($cm->user_id != $self->id) &&(!$last_read || ($last_read <= $cm->created_at)))
  	 <em><strong>{{__('chat.show.new')}}</strong></em>
  	 @endif
   </div>
   {{Str::limit($cm->message, 50)}}
   @endif
   </div>
  </div>
  @endforeach
  @endif
@endsection
