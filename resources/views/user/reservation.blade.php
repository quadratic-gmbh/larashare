@extends('layouts.app')
@section('content')
  @php
    $chat_link = route('chat.reservation',['id' => $reservation->id]);
  @endphp
  @if($reservation->confirmed_on === null)
  <div class="alert alert-warning d-flex align-items-center" role="alert">   
  	<i class="fas fa-exclamation-circle mr-3"></i>
    <div>      
      {{__('user.reservation.messages.unconfirmed')}}<br>
      {{__('user.reservation.messages.unconfirmed_contact_prompt')}}
      <a href="{{$chat_link}}">{{__('user.reservations.btn_contact')}}</a>
    </div>    
	</div>
	@else
	<div class="alert alert-success d-flex align-items-center" role="alert">
  	<i class="fas fa-check mr-3"></i>{{__('user.reservation.messages.confirmed')}}
	</div>
	@endif
  <h1 class="text-primary">{{__('user.reservation.title',['name' => $reservation->bike->name])}}</h1>
  <div class="mb-3">
    <a class="btn btn-secondary" target="__blank" href="{{route('bike.show',['bike_id' => $reservation->bike_id])}}">
    <i class="fas fa-arrow-right mr-2"></i>{{__('user.reservation.btn_bike')}}
    </a>
  </div>
  <div class="d-flex justify-content-begin mb-3">
    @component('user.reservation_card',[
      'header' => __('user.reservation.header_from'),
      'date_time' => $reservation->reserved_from->format('d.m.Y, H:i'),
      'rental_place' => $rental_place_from
    ])
    @endcomponent
    <div class="m-3"></div>
    @component('user.reservation_card',[
      'header' => __('user.reservation.header_to'),
      'date_time' => $reservation->reserved_to->format('d.m.Y, H:i'),
      'rental_place' => $rental_place_to
    ])
    @endcomponent
  </div>  
  <div class="">
    <a href="{{$chat_link}}" class="btn btn-secondary">{{__('user.reservations.btn_contact')}}</a>
    @if($reservation->reserved_to > now())
    <a class="btn btn-secondary" href="{{route('bike.show',['bike_id' => $reservation->bike_id, 'reservation_id' => $reservation->id])}}">{{__('user.reservations.btn_edit')}}</a>
    @endif
    @if(!$old)
    <a href="{{route('user.reservation_cancel',['id' => $reservation->id])}}" class="btn btn-danger">{{__('user.reservations.btn_cancel')}}</a>
    @endif
    <a href="{{route('user.reservations')}}" class="btn btn-secondary">{{__('general.back')}}</a>
  </div>
@endsection
