@extends('layouts.app')
@section('content')
  <h1 class="text-primary">{{__('bike.reservation.title_cancel',['name' => $bike->name])}}</h1>
  <div class="mb-3">
    <h5 class="font-weight-bold">{{__('bike.reservation.user_name')}}</h5>
    @if($reservation->user !== null)
    <p>{{$reservation->user->full_name}}</p>
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
  <p>{{__('user.reservation_cancel.hint')}}</p>
  <form method="POST">
    @csrf    
    <button type="submit" class="btn btn-danger">{{__('general.yes')}}</button>
    <a href="{{route('bike.reservation',['bike_id' => $bike->id,'reservation_id' => $reservation->id])}}" class="btn btn-secondary">{{__('general.back')}}</a>
  </form>   
@endsection
