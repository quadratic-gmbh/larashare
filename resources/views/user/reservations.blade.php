@extends('layouts.app')
@section('content')
  <h1 class="text-primary">{{__('user.reservations.title')}}</h1>
  <h3>{{__('user.reservations.category_new')}}</h3>
  @if(!isset($reservations['new']) || $reservations['new']->isEmpty())
  <p>{{__('user.reservations.category_new_empty')}}</p>
  @else
    @foreach($reservations['new'] as $r)
      @component('user.reservations_list_item',[
        'reservation' => $r,
        'new' => true
      ])
      @endcomponent
    @endforeach
  @endif
  <h3>{{__('user.reservations.category_old')}}</h3>
  @if(!isset($reservations['old']) || $reservations['old']->isEmpty())
  <p>{{__('user.reservations.category_old_empty')}}</p>
  @else
    @foreach($reservations['old'] as $r)
      @component('user.reservations_list_item',[
        'reservation' => $r,
        'new' => false
      ])
      @endcomponent    
    @endforeach
  @endif
@endsection
