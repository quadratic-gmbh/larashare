@extends('layouts.app')
@section('content')
  <h1 class="text-primary">{{__('user.reservation_cancel.title')}}</h1>  
  <ul class="list-unstyled">
    <li><b>{{$reservation->bike->name}}</b></li>            
    <li><b>
    @if($reservation->reserved_from->format('Ymd') == $reservation->reserved_to->format('Ymd'))
    {{$reservation->reserved_from->format('d.m.Y, H:i') . ' - ' . $reservation->reserved_to->format('H:i')}}
    @else
    {{$reservation->reserved_from->format('d.m.Y, H:i') . ' - ' . $reservation->reserved_to->format('d.m.Y, H:i')}}
    @endif
    </b></li>
  </ul>
  <p>{{__('user.reservation_cancel.hint')}}</p>
  @php
   $href_back = ($back_to_list ? route('user.reservations') : route('user.reservation',['id' => $reservation->id])); 
  @endphp  
  <form method="POST">
    @csrf    
    <button type="submit" class="btn btn-danger">{{__('general.yes')}}</button>
    <a href="{{$href_back}}" class="btn btn-secondary">{{__('general.back')}}</a>
  </form>    
  </div>
@endsection
