@extends('layouts.app')
@push('scripts')
<script src="/js/bike.reservation.js"></script>        
@endpush 
@section('content')
  @php
    $confirmed_suffix = ($reservation->confirmed_on === null ? 'unconfirmed' : 'confirmed');
    $title = __('bike.reservation.title_' . $confirmed_suffix,['name' => $reservation->bike->name])
  @endphp
  <h1 class="text-primary">{{$title}}</h1>
  <div class="mb-3">
    <h5 class="font-weight-bold">{{__('bike.reservation.user_name')}}</h5>
    @if($reservation->user !== null)
    <p>
      {{$reservation->user->full_name}}
      @if($reservation->user->telephone)
        <br><i class="fas fa-phone mr-2"></i>{{ $reservation->user->telephone }}
      @endif
      <br><a href="mailto:{{ $reservation->user->email }}"><i class="fas fa-envelope mr-2"></i>{{$reservation->user->email}}</a>
    </p>
    @else
      <span class="alert alert-danger mb-2 d-inline-block" role="alert">
        <i class="fas fa-lg fa-exclamation-circle"></i> {{__('bike.reservation.user_deleted')}}
      </span>
    @endif
    <h5 class="font-weight-bold">{{__('bike.reservation.purpose')}}</h5>
    <p>{{$reservation->purpose ?? __('bike.reservation.purpose_null')}}</p>
  </div>
  <div class="d-flex justify-content-begin mb-3">
    @component('bike.reservation_card',[
      'header' => __('bike.reservation.header_from'),
      'date_time' => $reservation->reserved_from->format('d.m.Y, H:i'),
      'rental_place' => $rental_place_from
    ])
    @endcomponent
    <div class="m-3"></div>
    @component('bike.reservation_card',[
      'header' => __('bike.reservation.header_to'),
      'date_time' => $reservation->reserved_to->format('d.m.Y, H:i'),
      'rental_place' => $rental_place_to
    ])
    @endcomponent
  </div>   
  <p>
  @if($old_from)  
  {{__('bike.reservation.hint_old')}}
  @else
  {{__('bike.reservation.hint_' . $confirmed_suffix)}}  
  @endif
  </p>
  <div class="" id="reservation-buttons">
    <a href="{{route('chat.reservation',['id' => $reservation->id])}}" class="btn btn-secondary">{{__('bike.reservation.btn_contact')}}</a>    
    @if(!$old)
      @isset($reservation->confirmed_on)
      @if(!$old_from)
      <a href="{{route('bike.reservation_cancel',['bike_id' => $bike->id,'reservation_id' => $reservation->id])}}" class="btn btn-danger">{{__('bike.reservation.btn_cancel')}}</a>
      @endif
      @else
      <form class="d-inline-block" method="POST" action="{{route('bike.reservation_confirm',['bike_id' => $bike->id,'reservation_id' => $reservation->id])}}">
        @csrf
        <button name="confirm" value="1" type="submit" class="btn btn-success">{{__('bike.reservation.btn_confirm')}}</button>
      </form>
      <button id="show-reservation-cancel" class="btn btn-danger">{{__('bike.reservation.btn_deny')}}</button>
      @endisset
    @endif    
    <a href="{{route('bike.reservations',['bike_id' => $bike->id,'date' => $date])}}" class="btn btn-secondary">{{__('general.back')}}</a>
  </div>
  <div id="reservation-cancel" hidden>
  	{{__('bike.reservation.confirm_inquiry_cancellation')}}
    <form class="d-inline-block" method="POST" action="{{route('bike.reservation_confirm',['bike_id' => $bike->id,'reservation_id' => $reservation->id])}}">
      @csrf
      <button name="confirm" value="-1" type="submit" class="btn btn-danger">{{__('bike.reservation.btn_deny_message')}}</button>
      <button name="confirm" value="0" type="submit" class="btn btn-danger">{{__('bike.reservation.btn_deny_no_message')}}</button>
    </form>
    <button id="reservation-cancel-no" class="btn btn-secondary">{{__('general.back')}}</button>
  </div>
@endsection
