@extends('layouts.app')
@push('scripts')
<script src="/js/chat.show.js"></script>        
@endpush 
@section('content')
<div id="chat-main-container">
	<h1 class="text-primary">
    {{$title}}
  </h1>
  @foreach($chat->messages as $cm)
  @php
  $current_sender_name = (isset($cm->sender) ? $cm->sender->getFullNameAttribute() : __('user.deleted'));
  $chat_color = 'danger';
  $chat_offset = '';
  if($cm->user_id == $self->id){
  	 $chat_color = 'primary';
  	 $chat_offset = "offset-4";
  }
  $new_message = false;
   @endphp
  	 <div class="row mt-5" id="cm-{{$cm->id}}">
  	 <div class="col-6 {{$chat_offset}}">
  	 <div class="card border-{{$chat_color}}">
  	 <div class="card-header bg-light border-{{$chat_color}}">
  	 {{$current_sender_name}}
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
  	 @php
  	 $new_message = true;
  	 @endphp
  	 <em><strong>{{__('chat.show.new')}}</strong></em>
  	 @endif
  	 </div>
  	 <div class="card-body">
  	 <p class="card-text chat-message-{{($new_message ? 'new' : 'old')}}">{{$cm->message}}</p>
  	 </div>
  	 </div>
  	 </div>
  	 </div>
@endforeach

  <div class="row">
  	<div class="col">
  		<form id="chat-form" method="post" action="{{route('chat.show',['chat_id' => $chat])}}" enctype="multipart/form-data" data-chat-id="{{$chat->id}}">
  		@csrf
  		@method('POST')
  		  <div class="form-group mb-0">
  				<label for="chat_message">{{__('chat.show.message')}} <span id="chat-message-length">500</span></label>
  			</div>
				<div class="form-group">
      		@component('components.form.label_input_error',[
        		'name' => 'chat_message',
        		'label' => false,
        		'type' => 'textarea'
      		])
      		@endcomponent
    		</div>
    		<div class="form-group">
        	<button type="submit" id="save-button" class="btn btn-primary" disabled="">
        		{{__('general.send') }}
        	</button>
        	<a href="{{route('chat.index')}}" class="btn btn-secondary">{{__('general.back')}}</a>
    		</div>
  		</form>
  	</div>
  </div>
</div>
@endsection
